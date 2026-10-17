<?php
// ============================================================
//  Núcleo compartilhado: sessão, caminhos e segurança.
//  Não precisa editar nada aqui.
// ============================================================

define('DADOS_DIR',   __DIR__ . '/dados');
define('SENHA_FILE',  DADOS_DIR . '/senha.php');     // hash da senha (nunca a senha em si)
define('DATA_FILE',   DADOS_DIR . '/clinica.json');  // pacientes, sessões, financeiro
define('BACKUP_DIR',  DADOS_DIR . '/backups');       // 1 cópia por dia, últimos 60 dias
define('TENTATIVAS',  DADOS_DIR . '/tentativas.json');
define('INATIVIDADE', 8 * 3600);                     // logout após 8h sem uso
define('AGENDA_TOKEN', DADOS_DIR . '/agenda_token.php'); // chave do Google Agenda (feed.php)
define('DIGITAIS',    DADOS_DIR . '/digitais.json');  // aparelhos com entrada por digital (só chaves públicas)

date_default_timezone_set('America/Sao_Paulo');

function https_ativo(): bool {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function iniciar_sessao(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_name('clinica_sess');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => dirname($_SERVER['SCRIPT_NAME']) ?: '/',
        'secure'   => https_ativo(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.gc_maxlifetime', (string)INATIVIDADE);
    session_start();
}

function garantir_pasta_dados(): void {
    if (!is_dir(DADOS_DIR))  mkdir(DADOS_DIR, 0700, true);
    if (!is_dir(BACKUP_DIR)) mkdir(BACKUP_DIR, 0700, true);
    // Bloqueio extra caso o .htaccess da pasta não tenha sido enviado
    $ht = DADOS_DIR . '/.htaccess';
    if (!file_exists($ht)) {
        file_put_contents($ht, "Require all denied\n<IfModule !mod_authz_core.c>\nOrder Allow,Deny\nDeny from all\n</IfModule>\n");
    }
    $idx = DADOS_DIR . '/index.html';
    if (!file_exists($idx)) file_put_contents($idx, '');
}

function senha_definida(): bool {
    return file_exists(SENHA_FILE);
}

function ler_hash(): string {
    if (!senha_definida()) return '';
    $h = include SENHA_FILE;   // arquivo .php retorna o hash; acessá-lo pela web não mostra nada
    return is_string($h) ? $h : '';
}

function gravar_hash(string $senha): void {
    garantir_pasta_dados();
    $hash = password_hash($senha, PASSWORD_DEFAULT);
    file_put_contents(SENHA_FILE, "<?php\nreturn " . var_export($hash, true) . ";\n", LOCK_EX);
    @chmod(SENHA_FILE, 0600);
}

function ler_token_agenda(): string {
    if (!file_exists(AGENDA_TOKEN)) return '';
    $t = include AGENDA_TOKEN;
    return is_string($t) ? $t : '';
}

function gerar_token_agenda(): string {
    garantir_pasta_dados();
    $t = bin2hex(random_bytes(24));
    file_put_contents(AGENDA_TOKEN, "<?php\nreturn " . var_export($t, true) . ";\n", LOCK_EX);
    @chmod(AGENDA_TOKEN, 0600);
    return $t;
}

function logado(): bool {
    if (empty($_SESSION['logado'])) return false;
    if (time() - ($_SESSION['hora'] ?? 0) > INATIVIDADE) {
        $_SESSION = [];
        session_destroy();
        return false;
    }
    $_SESSION['hora'] = time();
    return true;
}

function entrar(): void {
    session_regenerate_id(true);
    $_SESSION['logado'] = true;
    $_SESSION['hora']   = time();
    $_SESSION['csrf']   = bin2hex(random_bytes(32));
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_ok(?string $t): bool {
    return is_string($t) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t);
}

// ── Limite de tentativas de login (5 erros → espera 15 min) ──
function _tentativas(): array {
    if (!file_exists(TENTATIVAS)) return [];
    $d = json_decode((string)file_get_contents(TENTATIVAS), true);
    return is_array($d) ? $d : [];
}

function bloqueado_por(): int {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '?';
    $t  = _tentativas()[$ip] ?? null;
    if (!$t || $t['n'] < 5) return 0;
    $resta = $t['ate'] - time();
    return $resta > 0 ? $resta : 0;
}

function registrar_erro(): void {
    garantir_pasta_dados();
    $ip  = $_SERVER['REMOTE_ADDR'] ?? '?';
    $all = array_filter(_tentativas(), fn($t) => $t['ate'] > time());
    $n   = ($all[$ip]['n'] ?? 0) + 1;
    $all[$ip] = ['n' => $n, 'ate' => time() + 15 * 60];
    file_put_contents(TENTATIVAS, json_encode($all), LOCK_EX);
}

function limpar_erros(): void {
    $ip  = $_SERVER['REMOTE_ADDR'] ?? '?';
    $all = _tentativas();
    unset($all[$ip]);
    if (file_exists(TENTATIVAS)) file_put_contents(TENTATIVAS, json_encode($all), LOCK_EX);
}

function cabecalhos_privados(): void {
    header('Cache-Control: no-store, no-cache, must-revalidate, private');
    header('Pragma: no-cache');
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer');
    header('X-Robots-Tag: noindex, nofollow');
}

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
