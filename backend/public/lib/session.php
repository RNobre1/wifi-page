<?php
declare(strict_types=1);

function session_boot(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function current_user_id(): ?int {
    session_boot();
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function current_user_tier(): ?string {
    session_boot();
    return $_SESSION['tier'] ?? null;
}

function require_login(): void {
    if (current_user_id() === null) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'not_authenticated']);
        exit;
    }
}

function require_role(string $role): void {
    require_login();
    $tier = current_user_tier();
    $order = ['free' => 0, 'pro' => 1, 'admin' => 2];
    if (!isset($order[$tier]) || $order[$tier] < $order[$role]) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'forbidden', 'required' => $role, 'actual' => $tier]);
        exit;
    }
}

function login_user(int $id, string $tier): void {
    session_boot();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $id;
    $_SESSION['tier']    = $tier;
}

function logout_user(): void {
    session_boot();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
