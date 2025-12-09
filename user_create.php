<?php
require 'auth_check.php';
require 'functions.php';
require 'csrf.php';

$errors = [];
$values = ['nombre'=>'','email'=>'','rol'=>''];

// si el formulario se ha enviado por POST entonces procesamos los datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf'] ?? '')) { http_response_code(403); echo "Token inválido"; exit; }

    $values['nombre'] = $_POST['nombre'] ?? '';
    $values['email']  = $_POST['email'] ?? '';
    $values['rol']    = $_POST['rol'] ?? '';
    $password = $_POST['password'] ?? '';

    // validamos los datos de entrada
    $errors = validate_user_input($values);

    // contraseña obligatoria al crear
    if ($password === '') $errors['password'] = 'Contraseña obligatoria';

    // comprobar email duplicado
    $existing = find_user_by_email(trim($values['email']));
    if ($existing) $errors['email'] = 'Email ya registrado';

    // si no hay errores, añadimos el nuevo usuario
    if (empty($errors)) {
        $users = read_users();
        $users[] = [
            'id' => next_id(),
            'nombre' => $values['nombre'],
            'email' => $values['email'],
            'rol' => $values['rol'],
            'fecha_alta' => date('Y-m-d'),
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ];
        write_users($users);
        header('Location: user_index.php');
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Crear usuario</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <header class="header">
      <div class="brand"><span class="logo">U</span><div>Nuevo usuario</div></div>
      <div class="actions">
        <span class="small">Hola <?= h($_SESSION['username'] ?? '') ?></span>
        <a href="logout.php">Salir</a>
        <a href="user_index.php">Volver</a>
      </div>
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
          <input name="password" type="password" value="">
          <div class="small" style="color:#c42"><?= $errors['password'] ?? '' ?></div>
        </div>
      </div>

      <div style="margin-top:14px; display:flex; gap:8px;">
        <button class="btn positive" type="submit">Crear</button>
        <a class="btn ghost" href="user_index.php">Cancelar</a>
      </div>
    </form>

  </div>
</body>
</html>
