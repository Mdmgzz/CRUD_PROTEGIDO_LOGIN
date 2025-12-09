<?php
require 'functions.php';

// obtener el ID del usuario desde la URL y buscar sus datos
$id = $_GET['id'] ?? null;
$user = $id ? find_user($id) : null;
if (!$user) { echo "Usuario no encontrado"; exit; }
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Usuario <?= h($user['id']) ?></title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <header class="header">
      <div class="brand"><span class="logo">U</span><div>Usuario</div></div>
      <div class="actions">
        <a href="user_edit.php?id=<?= urlencode($user['id']) ?>">Editar</a>
        <a href="user_index.php">Volver</a>
      </div>
    </header>

    <div class="card">
      <h3 style="margin-top:0"><?= h($user['nombre']) ?> <span class="small">#<?= h($user['id']) ?></span></h3>
      <p><strong>Email:</strong> <?= h($user['email']) ?></p>
      <p><strong>Rol:</strong> <?= h($user['rol']) ?></p>
      <p><strong>Fecha alta:</strong> <?= h($user['fecha_alta']) ?></p>
    </div>
  </div>
</body>
</html>
