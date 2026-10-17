<?php
// Trocar a senha (precisa estar logado e saber a senha atual).
require __DIR__ . '/lib.php';
iniciar_sessao();
cabecalhos_privados();
if (!logado()) { header('Location: index.php'); exit; }

$msg = ''; $ok = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $atual = (string)($_POST['atual'] ?? '');
    $s1 = (string)($_POST['senha'] ?? '');
    $s2 = (string)($_POST['senha2'] ?? '');
    if (!csrf_ok($_POST['csrf'] ?? null))          $msg = 'Sessão expirada. Tente novamente.';
    elseif (!password_verify($atual, ler_hash()))   $msg = 'Senha atual incorreta.';
    elseif (mb_strlen($s1) < 8)                     $msg = 'A nova senha precisa ter pelo menos 8 caracteres.';
    elseif ($s1 !== $s2)                            $msg = 'As novas senhas não conferem.';
    else { gravar_hash($s1); $ok = true; $msg = 'Senha alterada com sucesso.'; }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Trocar senha · Clínica</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{min-height:100vh;background:#0a0a0a;display:flex;align-items:center;justify-content:center;font-family:system-ui,sans-serif;color:#e8e2d8;padding:20px}
.card{width:100%;max-width:380px;background:#111213;border:1px solid #1e1e1e;border-radius:12px;padding:28px}
h1{font-weight:400;font-size:20px;color:#c8a96e;margin-bottom:18px}
label{display:block;font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:#777;margin:12px 0 5px}
input{width:100%;background:#0d0d0d;border:1px solid #2a2a2a;border-radius:6px;padding:11px 13px;color:#e8e2d8;font-size:16px}
.btn{display:block;width:100%;text-align:center;padding:12px;background:#c8a96e;color:#0a0a0a;border:none;border-radius:6px;font-size:12px;letter-spacing:.1em;text-transform:uppercase;cursor:pointer;margin-top:18px;text-decoration:none}
.sec{background:none;border:1px solid #2a2a2a;color:#aaa;margin-top:10px}
.msg{padding:10px 13px;border-radius:6px;font-size:13px;margin-bottom:8px}
.err{background:#1e0c0c;border:1px solid #3a1515;color:#d08080}
.ok{background:#0e2018;border:1px solid #1a3525;color:#7fc096}
</style>
</head>
<body>
<div class="card">
  <h1>Trocar senha</h1>
  <?php if ($msg): ?><div class="msg <?= $ok ? 'ok' : 'err' ?>"><?= e($msg) ?></div><?php endif; ?>
  <form method="POST">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label for="a">Senha atual</label><input id="a" type="password" name="atual" autocomplete="current-password" required>
    <label for="n1">Nova senha</label><input id="n1" type="password" name="senha" autocomplete="new-password" minlength="8" required>
    <label for="n2">Repita a nova senha</label><input id="n2" type="password" name="senha2" autocomplete="new-password" minlength="8" required>
    <button class="btn" type="submit">Salvar nova senha</button>
    <a class="btn sec" href="app.php">Voltar ao app</a>
  </form>
</div>
</body>
</html>
