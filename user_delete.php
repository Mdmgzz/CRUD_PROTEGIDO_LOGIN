<?php
require 'auth_check.php';
require 'functions.php';
require 'csrf.php';

// Sólo aceptamos POST para eliminar
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Si alguien intenta acceder por GET, redirigimos a la lista
    header('Location: user_index.php');
    exit;
}

// Validar token CSRF
if (!csrf_validate($_POST['csrf'] ?? '')) {
    http_response_code(403);
    echo "Token inválido";
    exit;
}

// Comprobar permiso: solo administradores pueden borrar
if (($_SESSION['role'] ?? '') !== 'administrador') {
    http_response_code(403);
    echo "No autorizado";
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo "ID inválido";
    exit;
}

$all = read_users();
$found = false;
foreach ($all as $k => $u) {
    if ((int)$u['id'] === $id) {
        array_splice($all, $k, 1);
        $found = true;
        break;
    }
}

if ($found) {
    write_users(array_values($all));
}

// Siempre redirigimos de vuelta al índice (no exponemos si existía o no)
header('Location: user_index.php');
exit;
