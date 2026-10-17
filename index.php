<?php
// ============================================================
//  Tela de acesso.
//  No PRIMEIRO acesso pede para você criar a senha.
//  A senha não fica escrita em nenhum arquivo — só um "hash"
//  (impressão digital irreversível) em dados/senha.php.
// ============================================================
require __DIR__ . '/lib.php';
iniciar_sessao();
cabecalhos_privados();

if (logado()) { header('Location: app.php'); exit; }

$primeiro = !senha_definida();
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok($_POST['csrf'] ?? null)) {
        $erro = 'Sessão expirada. Tente novamente.';
    } elseif ($primeiro) {
        $s1 = (string)($_POST['senha'] ?? '');
        $s2 = (string)($_POST['senha2'] ?? '');
        if (mb_strlen($s1) < 8)      $erro = 'Use pelo menos 8 caracteres.';
        elseif ($s1 !== $s2)         $erro = 'As senhas não conferem.';
        else {
            gravar_hash($s1);
            entrar();
            header('Location: app.php');
            exit;
        }
    } elseif ($espera = bloqueado_por()) {
        $erro = 'Muitas tentativas. Aguarde ' . ceil($espera / 60) . ' min.';
    } elseif (password_verify((string)($_POST['senha'] ?? ''), ler_hash())) {
        limpar_erros();
        entrar();
        header('Location: app.php');
        exit;
    } else {
        registrar_erro();
        sleep(1);
        $erro = 'Senha incorreta.';
    }
}
$csrf = csrf_token();

// Nome do profissional (de Configurações) para o cabeçalho; só o nome, nada de pacientes
$nome = 'Sistema Clínico';
if (file_exists(DATA_FILE)) {
    $d = json_decode((string)file_get_contents(DATA_FILE), true);
    $n = trim((string)($d['data']['settings']['name'] ?? ''));
    if ($n !== '') $nome = $n;
}

// Há aparelho com entrada por digital cadastrado?
$temDigital = false;
if (!$primeiro && file_exists(DIGITAIS)) {
    $dg = json_decode((string)file_get_contents(DIGITAIS), true);
    $temDigital = !empty($dg['creds']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#0a0a0a">
<link rel="manifest" href="manifest.webmanifest">
<link rel="apple-touch-icon" href="apple-touch-icon.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Clínica">
<title>Acesso · Clínica</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400&family=DM+Sans:wght@300;400&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{min-height:100vh;background:#0a0a0a;display:flex;align-items:center;justify-content:center;font-family:'DM Sans',sans-serif;color:#e8e2d8;padding:20px}
.wrap{width:100%;max-width:360px}
.logo{text-align:center;margin-bottom:36px}
.logo h1{font-family:'Cormorant Garamond',serif;font-weight:300;font-size:26px;color:#c8a96e;letter-spacing:.03em}
.logo p{font-size:10px;color:#666;margin-top:5px;letter-spacing:.1em;text-transform:uppercase}
.card{background:#111213;border:1px solid #1e1e1e;border-radius:12px;padding:32px}
.lock{text-align:center;font-size:32px;margin-bottom:20px;opacity:.35}
label{display:block;font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:#777;margin-bottom:5px}
input[type=password]{width:100%;background:#0d0d0d;border:1px solid #2a2a2a;border-radius:6px;padding:12px 14px;color:#e8e2d8;font-family:'DM Sans',sans-serif;font-size:16px;outline:none;transition:border-color .2s}
input[type=password]:focus{border-color:#c8a96e88}
.btn{width:100%;padding:13px;background:#c8a96e;color:#0a0a0a;border:none;border-radius:6px;font-family:'DM Sans',sans-serif;font-size:12px;letter-spacing:.1em;text-transform:uppercase;cursor:pointer;margin-top:16px;transition:opacity .15s}
.btn:hover{opacity:.88}
.erro{background:#1e0c0c;border:1px solid #3a1515;color:#d08080;padding:10px 14px;border-radius:6px;font-size:13px;margin-bottom:16px}
.info{font-size:12px;color:#888;line-height:1.6;margin-bottom:18px}
.field{margin-bottom:12px}
.bio{display:none;width:100%;padding:14px;background:none;color:#c8a96e;border:1px solid #c8a96e66;border-radius:6px;font-family:'DM Sans',sans-serif;font-size:13px;letter-spacing:.06em;cursor:pointer;margin-bottom:18px}
.bio .ic{font-size:22px;display:block;margin-bottom:4px}
.ou{display:none;text-align:center;font-size:10px;color:#555;letter-spacing:.12em;text-transform:uppercase;margin:-6px 0 14px}
</style>
</head>
<body>
<div class="wrap">
  <div class="logo">
    <h1><?= e($nome) ?></h1>
    <p>Sistema Clínico · Acesso Restrito</p>
  </div>
  <div class="card">
    <div class="lock">🔒</div>
    <?php if ($erro): ?><div class="erro"><?= e($erro) ?></div><?php endif; ?>
    <form method="POST">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <?php if ($primeiro): ?>
        <p class="info">Primeiro acesso: crie a sua senha. Ela protege as anotações dos seus pacientes, então use uma senha forte (frase, números e símbolos).</p>
        <div class="field"><label for="s1">Nova senha</label>
          <input id="s1" type="password" name="senha" autocomplete="new-password" minlength="8" required autofocus></div>
        <div class="field"><label for="s2">Repita a senha</label>
          <input id="s2" type="password" name="senha2" autocomplete="new-password" minlength="8" required></div>
        <button type="submit" class="btn">Criar senha e entrar</button>
      <?php else: ?>
        <?php if ($temDigital): ?>
          <button type="button" class="bio" id="bio"><span class="ic">☝</span>Entrar com digital</button>
          <div class="ou" id="ou">ou use a senha</div>
        <?php endif; ?>
        <div class="field"><label for="s">Senha de acesso</label>
          <input id="s" type="password" name="senha" autocomplete="current-password" required autofocus></div>
        <button type="submit" class="btn">Entrar</button>
      <?php endif; ?>
    </form>
  </div>
</div>
<?php if ($temDigital): ?>
<script>
// Entrada com digital/rosto (WebAuthn). A digital é conferida pelo próprio celular.
(function(){
  const btn=document.getElementById('bio');
  if(!window.PublicKeyCredential||!navigator.credentials)return;
  btn.style.display='block';document.getElementById('ou').style.display='block';
  const csrf=document.querySelector('input[name=csrf]').value;
  const b2u=b=>btoa(String.fromCharCode(...new Uint8Array(b))).replace(/\+/g,'-').replace(/\//g,'_').replace(/=+$/,'');
  const u2b=s=>Uint8Array.from(atob(s.replace(/-/g,'+').replace(/_/g,'/')+'==='.slice((s.length+3)%4)),c=>c.charCodeAt(0));
  const erro=m=>{let e=document.querySelector('.erro');if(!e){e=document.createElement('div');e.className='erro';document.querySelector('form').prepend(e);}e.textContent=m;};
  const post=(acao,body)=>fetch('webauthn.php?acao='+acao,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-Token':csrf},body:JSON.stringify(body||{})}).then(async r=>({ok:r.ok,j:await r.json().catch(()=>({}))}));
  btn.addEventListener('click',async()=>{
    btn.disabled=true;
    try{
      const o=await post('entrar_opcoes');if(!o.ok)throw new Error(o.j.erro||'Erro');
      const c=await navigator.credentials.get({publicKey:{challenge:u2b(o.j.challenge),rpId:o.j.rpId,timeout:o.j.timeout,userVerification:'required',allowCredentials:o.j.allow.map(id=>({type:'public-key',id:u2b(id)}))}});
      const r=await post('entrar',{id:b2u(c.rawId),clientDataJSON:b2u(c.response.clientDataJSON),authenticatorData:b2u(c.response.authenticatorData),signature:b2u(c.response.signature)});
      if(!r.ok)throw new Error(r.j.erro||'Digital não confirmada.');
      location.href='app.php';
    }catch(e){
      erro(e.name==='NotAllowedError'?'Digital cancelada ou não reconhecida. Tente de novo ou use a senha.':(e.message||'Não foi possível usar a digital.'));
    }finally{btn.disabled=false;}
  });
})();
</script>
<?php endif; ?>
</body>
</html>
