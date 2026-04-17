<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/seed.php';
seed_users_once();
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/session.php';

session_boot();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');
    $stmt  = db()->prepare('SELECT id, pass_hash, tier FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    if ($row && password_verify($pass, $row['pass_hash'])) {
        login_user((int)$row['id'], (string)$row['tier']);
        // content negotiation: JSON clients get JSON, browsers get redirect
        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'user_id' => (int)$row['id'], 'tier' => $row['tier']]);
            exit;
        }
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Credenciais inválidas';
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => $error]);
        exit;
    }
}
?><!doctype html>
<html lang="pt-br">
<head><meta charset="utf-8"><title>Login</title>
<style>body{font-family:system-ui;max-width:420px;margin:3rem auto;padding:0 1rem}
label{display:block;margin:.75rem 0 .25rem}input{width:100%;padding:.5rem;font:inherit}
button{margin-top:1rem;padding:.5rem 1rem;font:inherit}.err{color:#b00}</style>
</head>
<body>
<h1>Login</h1>
<?php if ($error): ?><p class="err"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
  <label for="email">Email</label>
  <input id="email" name="email" type="email" required autofocus>
  <label for="password">Senha</label>
  <input id="password" name="password" type="password" required>
  <button type="submit">Entrar</button>
</form>
<p><a href="index.php">Voltar</a></p>
</body>
</html>
