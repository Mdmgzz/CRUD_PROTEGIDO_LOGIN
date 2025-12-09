<?php
require 'functions.php';
require 'csrf.php';
session_start();

// Si ya hay usuarios creados, bloquear registro público
$existing_users = read_users();
if (!empty($existing_users)) {
    header("Location: login.php");
    exit;
}

$errors = [];
$values = ['nombre'=>'','email'=>'','rol'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf'] ?? '')) {
        http_response_code(403);
        echo "CSRF token inválido";
        exit;
    }

    $values['nombre'] = trim($_POST['nombre'] ?? '');
    $values['email']  = trim($_POST['email'] ?? '');
    $values['rol']    = $_POST['rol'] ?? '';
    $password         = $_POST['password'] ?? '';

    // Validaciones
    $errors = validate_user_input($values);
    if ($password === '') $errors['password'] = 'Contraseña obligatoria';
    if (!empty(find_user_by_email($values['email']))) {
        $errors['email'] = 'Este email ya está en uso';
    }

    // Si todo está correcto → crear usuario
    if (empty($errors)) {
        $users = [];

        $users[] = [
            'id' => 1,
            'nombre' => $values['nombre'],
            'email' => $values['email'],
            'rol' => $values['rol'],
            'fecha_alta' => date('Y-m-d'),
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ];

        write_users($users);

        // Auto-login
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = $values['nombre'];
        $_SESSION['role'] = $values['rol'];
        $_SESSION['last_activity'] = time();

        header("Location: user_index.php");
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Registrar primer usuario</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">

    <header class="header">
        <div class="brand"><span class="logo">U</span><div>Primer usuario</div></div>
        <div class="actions"></div>
    </header>

    <form method="post" class="card" novalidate>
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">

        <div class="form-row">
            <div>
                <label>Nombre</label>
                <input name="nombre" type="text" value="<?= h($values['nombre']) ?>">
                <div class="small" style="color:#c42"><?= $errors['nombre'] ?? '' ?></div>
            </div>

            <div>
                <label>Email</label>
                <input name="email" type="email" value="<?= h($values['email']) ?>">
                <div class="small" style="color:#c42"><?= $errors['email'] ?? '' ?></div>
            </div>

            <div class="full">
                <label>Rol</label>
                <select name="rol">
                    <option value="">— seleccionar —</option>
                    <option value="administrador" <?= $values['rol']=='administrador'?'selected':'' ?>>Administrador</option>
                    <option value="editor" <?= $values['rol']=='editor'?'selected':'' ?>>Editor</option>
                    <option value="visitante" <?= $values['rol']=='visitante'?'selected':'' ?>>Visitante</option>
                </select>
                <div class="small" style="color:#c42"><?= $errors['rol'] ?? '' ?></div>
            </div>

            <div class="full">
                <label>Contraseña</label>
                <input name="password" type="password">
                <div class="small" style="color:#c42"><?= $errors['password'] ?? '' ?></div>
            </div>
        </div>

        <div style="margin-top:14px; display:flex; gap:8px;">
            <button class="btn positive" type="submit">Crear usuario</button>
        </div>
    </form>

</div>
</body>
</html>