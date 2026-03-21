<?php
// Este arquivo protege o app.html
// É chamado pelo .htaccess antes de servir app.html
session_start();

if (empty($_SESSION['logado'])) {
    header('Location: index.php');
    exit;
}

// Auto-logout após 8 horas de inatividade
if (!empty($_SESSION['hora']) && (time() - $_SESSION['hora']) > 28800) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$_SESSION['hora'] = time();

// Serve o app.html
readfile(__DIR__ . '/app.html');
?>
