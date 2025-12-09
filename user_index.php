<?php
require 'functions.php';
// ejecutar la función para leer los usuarios desde el archivo CSV
$users = read_users();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Usuarios — lista</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <header class="header">
      <div class="brand"><span class="logo">U</span><div>Gestión de usuarios</div></div>
      <div class="actions">
        <a href="user_create.php" class="primary">Crear usuario</a>
      </div>
    </header>

    <div class="card">
      <table class="table" aria-label="Lista de usuarios">
        <thead>
          <tr>
            <th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Alta</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($users)): ?>
            <tr><td colspan="6" class="small">No hay usuarios todavía</td></tr>
          <?php else: foreach ($users as $u): ?>
            <tr>
              <td><?= h($u['id']) ?></td>
              <td><?= h($u['nombre']) ?></td>
              <td class="small"><?= h($u['email']) ?></td>
              <td><?= h($u['rol']) ?></td>
              <td class="small"><?= h($u['fecha_alta']) ?></td>
              <td class="row-actions">
                <a href="user_info.php?id=<?= urlencode($u['id']) ?>">Ver</a>
                <a href="user_edit.php?id=<?= urlencode($u['id']) ?>">Editar</a>
                <a href="user_delete.php?id=<?= urlencode($u['id']) ?>" onclick="return confirm('Confirmar eliminación')"
                   style="color:var(--danger)">Eliminar</a>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

</div>
</body>
</html>
