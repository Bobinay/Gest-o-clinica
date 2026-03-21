<?php
// ============================================================
//  CONFIGURAÇÃO — altere a senha antes de fazer upload
// ============================================================
$SENHA = 'thomaz2025'; // <- TROQUE AQUI pela sua senha

// ============================================================
session_start();

// Já logado → vai para o app
if (!empty($_SESSION['logado'])) {
    header('Location: app.html');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha = $_POST['senha'] ?? '';
    if ($senha === $SENHA) {
        session_regenerate_id(true);
        $_SESSION['logado'] = true;
        $_SESSION['hora']   = time();
        header('Location: app.html');
        exit;
    } else {
        sleep(1); // Atrasa tentativas erradas
        $erro = 'Senha incorreta.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Acesso · Thomaz Almeida</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400&family=DM+Sans:wght@300;400&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{min-height:100vh;background:#0a0a0a;display:flex;align-items:center;justify-content:center;font-family:'DM Sans',sans-serif;color:#e8e2d8;padding:20px}
.wrap{width:100%;max-width:360px}
.logo{text-align:center;margin-bottom:36px}
.logo h1{font-family:'Cormorant Garamond',serif;font-weight:300;font-size:26px;color:#c8a96e;letter-spacing:.03em}
.logo p{font-size:10px;color:#444;margin-top:5px;letter-spacing:.1em;text-transform:uppercase}
.card{background:#111213;border:1px solid #1e1e1e;border-radius:12px;padding:32px}
.lock{text-align:center;font-size:32px;margin-bottom:20px;opacity:.35}
label{display:block;font-size:9px;letter-spacing:.12em;text-transform:uppercase;color:#555;margin-bottom:5px}
input[type=password]{width:100%;background:#0d0d0d;border:1px solid #252525;border-radius:6px;padding:12px 14px;color:#e8e2d8;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;transition:border-color .2s}
input[type=password]:focus{border-color:#c8a96e55}
.btn{width:100%;padding:13px;background:#c8a96e;color:#0a0a0a;border:none;border-radius:6px;font-family:'DM Sans',sans-serif;font-size:11px;font-weight:400;letter-spacing:.1em;text-transform:uppercase;cursor:pointer;margin-top:16px;transition:opacity .15s}
.btn:hover{opacity:.88}
.erro{background:#1e0c0c;border:1px solid #3a1515;color:#c07070;padding:10px 14px;border-radius:6px;font-size:12px;margin-bottom:16px}
.field{margin-bottom:4px}
</style>
</head>
<body>
<div class="wrap">
  <div class="logo">
    <h1>Thomaz Almeida</h1>
    <p>Sistema Clínico · Acesso Restrito</p>
  </div>
  <div class="card">
    <div class="lock">🔒</div>
    <?php if ($erro): ?>
      <div class="erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    <form method="POST" autocomplete="off">
      <div class="field">
        <label>Senha de Acesso</label>
        <input type="password" name="senha" autofocus required placeholder="••••••••••">
      </div>
      <button type="submit" class="btn">Entrar</button>
    </form>
  </div>
</div>
</body>
</html>
