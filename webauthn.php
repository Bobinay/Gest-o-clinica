<?php
// ============================================================
//  Entrada com digital / rosto (WebAuthn, "chave de acesso").
//  A digital nunca sai do celular: o aparelho guarda uma chave
//  secreta e só prova ao servidor que é ele. Aqui ficam apenas
//  as chaves PÚBLICAS de cada aparelho (dados/digitais.json).
//
//  POST ?acao=registrar_opcoes  (logado)  → desafio para cadastrar o aparelho
//  POST ?acao=registrar         (logado)  → salva o aparelho
//  POST ?acao=entrar_opcoes               → desafio para entrar
//  POST ?acao=entrar                      → confere a digital e faz login
//  GET  ?acao=listar            (logado)  → aparelhos cadastrados
//  POST ?acao=remover           (logado)  → remove um aparelho
// ============================================================
require __DIR__ . '/lib.php';
iniciar_sessao();
cabecalhos_privados();
header('Content-Type: application/json; charset=utf-8');

function resp(int $code, array $body): void {
    http_response_code($code);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function b64u_enc(string $b): string { return rtrim(strtr(base64_encode($b), '+/', '-_'), '='); }
function b64u_dec(string $s): string { return (string)base64_decode(strtr($s, '-_', '+/') . str_repeat('=', (4 - strlen($s) % 4) % 4)); }

// ── Leitor CBOR mínimo (formato binário usado pelos aparelhos) ──
function cbor_decode(string $d, int &$p) {
    if ($p >= strlen($d)) throw new Exception('cbor');
    $ib = ord($d[$p++]); $mt = $ib >> 5; $ai = $ib & 31;
    if ($ai < 24)       $v = $ai;
    elseif ($ai === 24) { $v = ord($d[$p]); $p += 1; }
    elseif ($ai === 25) { $v = unpack('n', substr($d, $p, 2))[1]; $p += 2; }
    elseif ($ai === 26) { $v = unpack('N', substr($d, $p, 4))[1]; $p += 4; }
    elseif ($ai === 27) { $v = unpack('J', substr($d, $p, 8))[1]; $p += 8; }
    else throw new Exception('cbor');
    switch ($mt) {
        case 0: return $v;
        case 1: return -1 - $v;
        case 2: case 3: $s = substr($d, $p, $v); $p += $v; return $s;
        case 4: $a = []; for ($i = 0; $i < $v; $i++) $a[] = cbor_decode($d, $p); return $a;
        case 5: $m = []; for ($i = 0; $i < $v; $i++) { $k = cbor_decode($d, $p); $m[$k] = cbor_decode($d, $p); } return $m;
        case 7: return [20 => false, 21 => true, 22 => null][$ai] ?? null;
    }
    throw new Exception('cbor');
}

// ── Chave do aparelho (formato COSE) → formato PEM que o PHP entende ──
function der_len(int $n): string {
    if ($n < 128) return chr($n);
    $b = ltrim(pack('N', $n), "\0");
    return chr(0x80 | strlen($b)) . $b;
}
function der(int $tag, string $v): string { return chr($tag) . der_len(strlen($v)) . $v; }
function der_int(string $b): string { $b = ltrim($b, "\0"); if ($b === '' || ord($b[0]) & 0x80) $b = "\0" . $b; return der(0x02, $b); }

function cose_para_pem(array $k): array {
    $alg = $k[3] ?? null;
    if (($k[1] ?? null) === 2 && $alg === -7 && ($k[-1] ?? null) === 1) {          // EC P-256 (celulares)
        $x = $k[-2]; $y = $k[-3];
        if (strlen($x) !== 32 || strlen($y) !== 32) throw new Exception('chave');
        $der = hex2bin('3059301306072a8648ce3d020106082a8648ce3d030107034200') . "\x04" . $x . $y;
    } elseif (($k[1] ?? null) === 3 && $alg === -257) {                              // RSA (Windows Hello)
        $rsa = der(0x30, der_int($k[-1]) . der_int($k[-2]));
        $der = der(0x30, der(0x30, hex2bin('06092a864886f70d0101010500')) . der(0x03, "\0" . $rsa));
    } else {
        throw new Exception('tipo de chave não suportado');
    }
    return [$alg, "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END PUBLIC KEY-----\n"];
}

// ── Aparelhos cadastrados ──
function ler_digitais(): array {
    $d = file_exists(DIGITAIS) ? json_decode((string)file_get_contents(DIGITAIS), true) : null;
    if (!is_array($d)) $d = [];
    if (empty($d['userId'])) $d['userId'] = b64u_enc(random_bytes(16));
    $d['creds'] = $d['creds'] ?? [];
    return $d;
}
function gravar_digitais(array $d): void {
    garantir_pasta_dados();
    file_put_contents(DIGITAIS, json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    @chmod(DIGITAIS, 0600);
}

function rp_id(): string { return strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? '')); }
function origem(): string { return (https_ativo() ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? ''); }

// Confere clientDataJSON e authenticatorData comuns ao cadastro e à entrada
function conferir(string $tipo, string $clientJson, string $authData): array {
    $c = json_decode($clientJson, true);
    $desafio = $_SESSION['wa_desafio'] ?? '';
    unset($_SESSION['wa_desafio']);
    if (!is_array($c) || ($c['type'] ?? '') !== $tipo)                 throw new Exception('tipo');
    if ($desafio === '' || !hash_equals($desafio, (string)($c['challenge'] ?? ''))) throw new Exception('desafio');
    if (($c['origin'] ?? '') !== origem())                             throw new Exception('origem');
    if (strlen($authData) < 37)                                        throw new Exception('dados');
    if (!hash_equals(hash('sha256', rp_id(), true), substr($authData, 0, 32))) throw new Exception('site');
    $flags = ord($authData[32]);
    if (!($flags & 0x01)) throw new Exception('presença');            // tocou no aparelho
    if (!($flags & 0x04)) throw new Exception('digital');             // digital/rosto/PIN confirmados
    return ['flags' => $flags, 'count' => unpack('N', substr($authData, 33, 4))[1]];
}

function corpo(): array { $j = json_decode((string)file_get_contents('php://input'), true); return is_array($j) ? $j : []; }

$acao = $_GET['acao'] ?? '';
$post = $_SERVER['REQUEST_METHOD'] === 'POST';

if ($post && !csrf_ok($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) resp(403, ['erro' => 'Sessão expirada. Recarregue a página.']);

try {
    switch ($acao) {

    case 'registrar_opcoes':
        if (!$post || !logado()) resp(401, ['erro' => 'login']);
        $d = ler_digitais(); gravar_digitais($d);
        $_SESSION['wa_desafio'] = b64u_enc(random_bytes(32));
        resp(200, [
            'challenge' => $_SESSION['wa_desafio'],
            'rp'        => ['name' => 'Clínica', 'id' => rp_id()],
            'user'      => ['id' => $d['userId'], 'name' => 'clinica@' . rp_id(), 'displayName' => 'Clínica'],
            'pubKeyCredParams' => [['type' => 'public-key', 'alg' => -7], ['type' => 'public-key', 'alg' => -257]],
            'authenticatorSelection' => ['authenticatorAttachment' => 'platform', 'userVerification' => 'required', 'residentKey' => 'preferred'],
            'attestation' => 'none',
            'timeout'   => 60000,
            'exclude'   => array_column($d['creds'], 'id'),
        ]);

    case 'registrar':
        if (!$post || !logado()) resp(401, ['erro' => 'login']);
        $in  = corpo();
        $cj  = b64u_dec((string)($in['clientDataJSON'] ?? ''));
        $p = 0; $att = cbor_decode(b64u_dec((string)($in['attestationObject'] ?? '')), $p);
        $ad  = (string)($att['authData'] ?? '');
        $chk = conferir('webauthn.create', $cj, $ad);
        if (!($chk['flags'] & 0x40)) throw new Exception('sem chave');
        $len    = unpack('n', substr($ad, 53, 2))[1];
        $credId = substr($ad, 55, $len);
        $q = 55 + $len; $cose = cbor_decode($ad, $q);
        [$alg, $pem] = cose_para_pem($cose);
        $d = ler_digitais();
        $id = b64u_enc($credId);
        $d['creds'] = array_values(array_filter($d['creds'], fn($c) => $c['id'] !== $id));
        $nome = trim(mb_substr((string)($in['nome'] ?? ''), 0, 40)) ?: 'Aparelho';
        $d['creds'][] = ['id' => $id, 'pem' => $pem, 'alg' => $alg, 'count' => $chk['count'], 'nome' => $nome, 'criado' => date('Y-m-d H:i'), 'usado' => null];
        gravar_digitais($d);
        resp(200, ['ok' => true]);

    case 'entrar_opcoes':
        if (!$post) resp(405, ['erro' => 'metodo']);
        if ($espera = bloqueado_por()) resp(429, ['erro' => 'Muitas tentativas. Aguarde ' . ceil($espera / 60) . ' min.']);
        $d = ler_digitais();
        if (!$d['creds']) resp(404, ['erro' => 'Nenhum aparelho com digital cadastrado.']);
        $_SESSION['wa_desafio'] = b64u_enc(random_bytes(32));
        resp(200, ['challenge' => $_SESSION['wa_desafio'], 'rpId' => rp_id(), 'allow' => array_column($d['creds'], 'id'), 'timeout' => 60000]);

    case 'entrar':
        if (!$post) resp(405, ['erro' => 'metodo']);
        if ($espera = bloqueado_por()) resp(429, ['erro' => 'Muitas tentativas. Aguarde ' . ceil($espera / 60) . ' min.']);
        $in = corpo();
        $d  = ler_digitais();
        $i  = array_search((string)($in['id'] ?? ''), array_column($d['creds'], 'id'), true);
        if ($i === false) { registrar_erro(); resp(403, ['erro' => 'Este aparelho não está cadastrado. Entre com a senha.']); }
        $cred = $d['creds'][$i];
        $cj = b64u_dec((string)($in['clientDataJSON'] ?? ''));
        $ad = b64u_dec((string)($in['authenticatorData'] ?? ''));
        $chk = conferir('webauthn.get', $cj, $ad);
        $ok = openssl_verify($ad . hash('sha256', $cj, true), b64u_dec((string)($in['signature'] ?? '')), $cred['pem'], OPENSSL_ALGO_SHA256);
        if ($ok !== 1) { registrar_erro(); resp(403, ['erro' => 'Digital não confirmada.']); }
        // Contador anti-clonagem: se o aparelho usa contador, ele só pode aumentar
        if ($chk['count'] > 0 || $cred['count'] > 0) {
            if ($chk['count'] <= $cred['count']) { registrar_erro(); resp(403, ['erro' => 'Chave inválida. Entre com a senha.']); }
        }
        $d['creds'][$i]['count'] = $chk['count'];
        $d['creds'][$i]['usado'] = date('Y-m-d H:i');
        gravar_digitais($d);
        limpar_erros();
        entrar();
        resp(200, ['ok' => true]);

    case 'listar':
        if (!logado()) resp(401, ['erro' => 'login']);
        $d = ler_digitais();
        resp(200, ['aparelhos' => array_map(fn($c) => ['id' => $c['id'], 'nome' => $c['nome'], 'criado' => $c['criado'], 'usado' => $c['usado']], $d['creds'])]);

    case 'remover':
        if (!$post || !logado()) resp(401, ['erro' => 'login']);
        $id = (string)(corpo()['id'] ?? '');
        $d = ler_digitais();
        $d['creds'] = array_values(array_filter($d['creds'], fn($c) => $c['id'] !== $id));
        gravar_digitais($d);
        resp(200, ['ok' => true]);
    }
    resp(400, ['erro' => 'ação']);
} catch (Throwable $e) {
    resp(400, ['erro' => 'Não foi possível confirmar (' . $e->getMessage() . ').']);
}
