<?php
// crea una sesión protegida y verifica que el usuario esté logueado
if (session_status() === PHP_SESSION_NONE) session_start();
// Timeout de inactividad: 30 minutos
$timeout = 30 * 60;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
