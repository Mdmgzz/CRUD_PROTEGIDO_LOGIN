<?php
require 'functions.php';
// obtener el ID del usuario desde la URL y buscar sus datos para poder editarlo
$id = $_GET['id'] ?? null;
$user = $id ? find_user($id) : null;
if (!$user) { echo "Usuario no encontrado"; exit; }

$values = $user;
$errors = [];

// si el formulario se ha enviado por POST entonces procesamos los datos 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['nombre'] = $_POST['nombre'] ?? '';
    $values['email']  = $_POST['email'] ?? '';
    $values['rol']    = $_POST['rol'] ?? '';

    // validamos los datos
    $errors = validate_user_input($values);
    if (empty($errors)) {
        $all = read_users();
        foreach ($all as &$u) {
            if ((int)$u['id'] === (int)$id) {
                $u['nombre'] = $values['nombre'];
                $u['email']  = $values['email'];
                $u['rol']    = $values['rol'];
                break;
            }
        }
        // guardamos los cambios
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
      <div class="actions"><a href="user_index.php">Volver</a></div>
    </header>

    <form method="post" class="card">
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
      </div>

      <div style="margin-top:14px; display:flex; gap:8px;">
        <button class="btn positive" type="submit">Guardar</button>
        <a class="btn ghost" href="user_index.php">Cancelar</a>
      </div>
    </form>
  </div>
</body>
</html>
