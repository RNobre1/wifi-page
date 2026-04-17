<?php
require_once __DIR__ . '/lib/seed.php';
seed_users_once();
require_once __DIR__ . '/lib/session.php';
session_boot();
$uid = current_user_id();
?><!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>SuperMonitor Lab</title>
<style>body{font-family:system-ui;max-width:640px;margin:3rem auto;padding:0 1rem}</style>
</head>
<body>
<h1>SuperMonitor Lab</h1>
<p>Laboratório local de segurança. Somente para localhost.</p>
<?php if ($uid): ?>
  <p>Logado como user <?= htmlspecialchars((string)$uid) ?> (tier: <?= htmlspecialchars(current_user_tier() ?? '') ?>).</p>
  <p><a href="dashboard.php">Dashboard</a> · <a href="logout.php">Sair</a></p>
<?php else: ?>
  <p><a href="login.php">Entrar</a></p>
<?php endif; ?>
<hr>
<p>Contas seed (senha <code>senha123</code>):</p>
<ul>
  <li><code>free@teste.local</code></li>
  <li><code>pro@teste.local</code></li>
  <li><code>admin@teste.local</code></li>
</ul>
</body>
</html>
