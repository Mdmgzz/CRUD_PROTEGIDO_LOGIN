<!-- login.php -->
<?php
session_start();
// si ya está autenticado, redirige
if (!empty($_SESSION['user_id'])) {
    header('Location: user_index.php');
    exit;
}
$error = $_GET['error'] ?? '';
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Login</title><link rel="stylesheet" href="styles.css"></head>
<body>
  <div class="container" style="max-width:420px; margin-top:60px;">
    <form method="post" action="login_process.php" class="card" novalidate>
      <h3>Acceso</h3>
      <?php if ($error): ?><div class="small" style="color:#c42;margin-bottom:8px">Credenciales inválidas</div><?php endif; ?>
      <label>Email</label>
      <input name="email" type="email" required>
      <label>Contraseña</label>
      <input name="password" type="password" required>
      <div style="margin-top:12px; display:flex; gap:8px;">
        <button class="btn positive" type="submit">Entrar</button>
        <a class="btn ghost" href="user_index.php">Volver</a>
      </div>
    </form>
  </div>
</body>
</html>
