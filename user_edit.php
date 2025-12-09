<?php
require 'auth_check.php';
require 'functions.php';
require 'csrf.php';

// obtener el ID del usuario desde la URL y buscar sus datos para poder editarlo
$id = $_GET['id'] ?? null;
$user = $id ? find_user($id) : null;
if (!$user) { echo "Usuario no encontrado"; exit; }

$values = $user;
$errors = [];

// Procesar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf'] ?? '')) { http_response_code(403); echo "Token inválido"; exit; }

    $values['nombre'] = $_POST['nombre'] ?? '';
    $values['email']  = $_POST['email'] ?? '';
    $values['rol']    = $_POST['rol'] ?? '';
    $password = $_POST['password'] ?? '';

    // Si el usuario actual NO es administrador no le permitimos cambiar el rol
    if (($_SESSION['role'] ?? '') !== 'administrador') {
        $values['rol'] = $user['rol'];
    }

    // validamos los datos
    $errors = validate_user_input($values);

    // si cambian email, comprobar duplicados (excluyendo al propio)
    $other = find_user_by_email(trim($values['email']));
    if ($other && (int)$other['id'] !== (int)$id) {
        $errors['email'] = 'Email ya registrado';
    }

    if (empty($errors)) {
        $all = read_users();
        foreach ($all as &$u) {
            if ((int)$u['id'] === (int)$id) {
                $u['nombre'] = $values['nombre'];
                $u['email']  = $values['email'];
                $u['rol']    = $values['rol'];
                // actualizar contraseña solo si se ha rellenado
                if ($password !== '') {
                    $u['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
                }
                break;
            }
        }
        write_users($all);
        header('Location: user_index.php');
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Editar usuario</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <header class="header">
      <div class="brand"><span class="logo">U</span><div>Editar</div></div>
      <div class="actions">
        <span class="small">Hola <?= h($_SESSION['username'] ?? '') ?></span>
        <a href="logout.php">Salir</a>
        <a href="user_index.php">Volver</a>
      </div>
    </header>

    <form method="post" class="card">
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
          <select name="rol" <?= (($_SESSION['role'] ?? '') !== 'administrador') ? 'disabled' : '' ?>>
            <option value="">— seleccionar —</option>
            <option value="administrador" <?= $values['rol']=='administrador'?'selected':'' ?>>Administrador</option>
            <option value="editor" <?= $values['rol']=='editor'?'selected':'' ?>>Editor</option>
            <option value="visitante" <?= $values['rol']=='visitante'?'selected':'' ?>>Visitante</option>
          </select>
          <div class="small" style="color:#c42"><?= $errors['rol'] ?? '' ?></div>
          <?php if (($_SESSION['role'] ?? '') !== 'administrador'): ?>
            <div class="small">Solo administradores pueden cambiar el rol.</div>
            <!-- Si el select está deshabilitado, enviamos el valor original con un input oculto -->
            <input type="hidden" name="rol" value="<?= h($values['rol']) ?>">
          <?php endif; ?>
        </div>

        <div class="full">
          <label>Nueva contraseña (dejar en blanco para no cambiarla)</label>
          <input name="password" type="password" value="">
        </div>
      </div>

      <div style="margin-top:14px; display:flex; gap:8px;">
        <button class="btn positive" type="submit">Guardar</button>
        <a class="btn ghost" href="user_index.php">Cancelar</a>
      </div>
    </form>
  </div>
</body>
</html>
