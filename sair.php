<?php
require __DIR__ . '/lib.php';
iniciar_sessao();
$_SESSION = [];
session_destroy();
header('Location: index.php');
exit;
