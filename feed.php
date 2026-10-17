<?php
// ============================================================
//  Agenda para o Google Agenda (lida pelo Google Apps Script).
//  Protegida por uma chave secreta gerada em Configurações.
//  Entrega SÓ data, horário, nome e link do Meet. Nunca as
//  anotações clínicas.
//
//  GET feed.php?token=XXXX → { config, from, to, sessions:[...] }
// ============================================================
require __DIR__ . '/lib.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');

function sair(int $code, array $body): void {
    http_response_code($code);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$token = ler_token_agenda();
if ($token === '' || !hash_equals($token, (string)($_GET['token'] ?? ''))) {
    sleep(1);
    sair(403, ['erro' => 'chave']);
}

$d    = file_exists(DATA_FILE) ? json_decode((string)file_get_contents(DATA_FILE), true) : null;
$data = $d['data'] ?? ['patients' => [], 'settings' => []];
$cfg  = $data['settings'] ?? [];

// Janela sincronizada: 7 dias para trás até 120 dias à frente
$tz   = new DateTimeZone('America/Sao_Paulo');
$from = (new DateTime('today', $tz))->modify('-7 days');
$to   = (new DateTime('today', $tz))->modify('+121 days');

$lembretes = array_values(array_filter(array_map('intval',
    preg_split('/[^0-9]+/', (string)($cfg['reminders'] ?? '60, 10'))), fn($m) => $m > 0 && $m <= 40320));
$lembretes = array_slice($lembretes ?: [60, 10], 0, 5);   // Google aceita até 5 lembretes

$base = (https_ativo() ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '') . rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';

$sessoes = [];
foreach ($data['patients'] ?? [] as $p) {
    foreach ($p['sessions'] ?? [] as $s) {
        $st = $s['status'] ?? 'agendada';
        if ($st === 'cancelada' || empty($s['date'])) continue;
        $ini = DateTime::createFromFormat('Y-m-d H:i', $s['date'] . ' ' . ($s['time'] ?: '10:00'), $tz);
        if (!$ini || $ini < $from || $ini >= $to) continue;
        $extra = ['faltou' => ' (faltou)', 'falta_cobrada' => ' (falta)'][$st] ?? '';
        $meet  = trim((string)($p['meetLink'] ?? ''));
        $sessoes[] = [
            'id'          => (string)$s['id'],
            'title'       => 'Sessão – ' . ($p['name'] ?? 'Paciente') . $extra,
            'start'       => $ini->format(DateTime::ATOM),
            'location'    => $meet,
            'description' => ($meet ? "Link da sessão: $meet\n\n" : '') . "Abrir no app: $base",
        ];
    }
}

sair(200, [
    'config' => [
        'calendarName' => 'Clínica – Atendimentos',
        'duration'     => max(10, min(240, (int)($cfg['sessionMinutes'] ?? 50))),
        'reminders'    => $lembretes,
    ],
    'from'     => $from->format(DateTime::ATOM),
    'to'       => $to->format(DateTime::ATOM),
    'sessions' => $sessoes,
]);
