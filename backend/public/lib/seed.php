<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function seed_users_once(): void {
    $pdo = db();
    $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count > 0) {
        return;
    }
    $users = [
        ['free@teste.local',  'free'],
        ['pro@teste.local',   'pro'],
        ['admin@teste.local', 'admin'],
    ];
    $password = getenv('LAB_SEED_PASSWORD');
    if (!is_string($password) || $password === '') {
        throw new RuntimeException('LAB_SEED_PASSWORD nao configurada — defina em .env');
    }
    $stmt = $pdo->prepare('INSERT INTO users (email, pass_hash, tier) VALUES (?, ?, ?)');
    foreach ($users as [$email, $tier]) {
        $stmt->execute([$email, password_hash($password, PASSWORD_BCRYPT), $tier]);
    }
}
