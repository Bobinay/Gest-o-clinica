<?php
// Só entrega o aplicativo (app.html) para quem fez login.
require __DIR__ . '/lib.php';
iniciar_sessao();
cabecalhos_privados();

if (!logado()) { header('Location: index.php'); exit; }

$html = file_get_contents(__DIR__ . '/app.html');
// Token anti-falsificação usado pelo app ao salvar no servidor
$meta = '<meta name="csrf-token" content="' . e(csrf_token()) . '">';
echo str_replace('<!--CSRF-->', $meta, $html);
