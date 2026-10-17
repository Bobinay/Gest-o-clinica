<?php
// ============================================================
//  API de dados: o app lê e grava aqui (no servidor),
//  para que celular e computador vejam as mesmas informações.
//
//  GET  api.php           → { rev, data }
//  POST api.php {rev,data} → grava se ninguém salvou antes (rev igual)
// ============================================================
require __DIR__ . '/lib.php';
iniciar_sessao();
cabecalhos_privados();
header('Content-Type: application/json; charset=utf-8');

function responder(int $code, array $body): void {
    http_response_code($code);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!logado()) responder(401, ['erro' => 'login']);

garantir_pasta_dados();

function ler_dados(): array {
    if (!file_exists(DATA_FILE)) return ['rev' => 0, 'data' => null];
    $d = json_decode((string)file_get_contents(DATA_FILE), true);
    return is_array($d) ? $d : ['rev' => 0, 'data' => null];
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $d = ler_dados();
    responder(200, ['rev' => (int)($d['rev'] ?? 0), 'data' => $d['data'] ?? null]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') responder(405, ['erro' => 'metodo']);
if (!csrf_ok($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) responder(403, ['erro' => 'csrf']);

$in = json_decode((string)file_get_contents('php://input'), true);
if (!is_array($in) || !isset($in['data']) || !is_array($in['data'])
    || !isset($in['data']['patients']) || !is_array($in['data']['patients'])) {
    responder(400, ['erro' => 'formato']);
}

// Trava o arquivo durante a gravação (evita corromper se dois aparelhos salvarem juntos)
$fp = fopen(DATA_FILE . '.lock', 'c');
flock($fp, LOCK_EX);

$atual = ler_dados();
$rev   = (int)($atual['rev'] ?? 0);

// Outro aparelho salvou depois que este carregou → não sobrescreve
if ((int)($in['rev'] ?? -1) !== $rev) {
    flock($fp, LOCK_UN);
    responder(409, ['erro' => 'conflito', 'rev' => $rev, 'data' => $atual['data'] ?? null]);
}

$novo = ['rev' => $rev + 1, 'salvo_em' => date('c'), 'data' => $in['data']];
$json = json_encode($novo, JSON_UNESCAPED_UNICODE);

// Grava num arquivo temporário e troca de uma vez (nunca deixa o arquivo pela metade)
$tmp = DATA_FILE . '.tmp';
file_put_contents($tmp, $json, LOCK_EX);
rename($tmp, DATA_FILE);
@chmod(DATA_FILE, 0600);

// Backup diário automático (mantém os últimos 60 dias)
$bk = BACKUP_DIR . '/clinica-' . date('Y-m-d') . '.json';
copy(DATA_FILE, $bk);
$todos = glob(BACKUP_DIR . '/clinica-*.json') ?: [];
sort($todos);
foreach (array_slice($todos, 0, max(0, count($todos) - 60)) as $velho) @unlink($velho);

flock($fp, LOCK_UN);
responder(200, ['rev' => $rev + 1]);
