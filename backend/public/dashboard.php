<?php
require_once __DIR__ . '/lib/session.php';
session_boot();
if (!current_user_id()) {
    header('Location: login.php');
    exit;
}
$tier = current_user_tier();
?><!doctype html>
<html lang="pt-br">
<head><meta charset="utf-8"><title>Dashboard</title>
<style>body{font-family:system-ui;max-width:640px;margin:3rem auto;padding:0 1rem}
.box{border:1px solid #ccc;padding:1rem;margin:1rem 0;border-radius:6px}</style>
</head>
<body>
<h1>Dashboard</h1>
<p>Usuário <?= htmlspecialchars((string)current_user_id()) ?> · tier <b><?= htmlspecialchars($tier) ?></b></p>

<div class="box">
  <h3>API /api/monitor.php (vulnerável)</h3>
  <p>Aceita <code>{"tier":"pro","userId":N}</code> do cliente — PoC de client-side trust.</p>
</div>
<div class="box">
  <h3>API /api/monitor_secure.php (fix)</h3>
  <p>Ignora qualquer tier/userId vindo do body; usa só a sessão.</p>
</div>
<?php if ($tier === 'admin'): ?>
<div class="box">
  <h3>Admin</h3>
  <p><a href="api/admin.php">admin.php</a> · <a href="api/admin_secure.php">admin_secure.php</a></p>
</div>
<?php endif; ?>
<p><a href="logout.php">Sair</a></p>
</body>
</html>
