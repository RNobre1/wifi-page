<?php
declare(strict_types=1);

/**
 * Versão SEGURA de monitor.php.
 *
 * Correções:
 *   1. tier e user_id vêm de $_SESSION. Body do cliente é ignorado para autorização.
 *   2. Rate-limit por user via Redis (60 req / 60s).
 *   3. Valida tipo do input restante (metric).
 */

require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../lib/ratelimit.php';

header('Content-Type: application/json');
require_login();

$uid  = (int) current_user_id();
$tier = (string) current_user_tier();

rate_limit_enforce("monitor:user:{$uid}", 60, 60);

$raw    = file_get_contents('php://input');
$body   = json_decode($raw, true) ?: [];
$metric = (string)($body['metric'] ?? 'pings');
if (!in_array($metric, ['pings', 'latency', 'errors'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_metric']);
    exit;
}

$quotas = ['free' => 10, 'pro' => 10000, 'admin' => -1];
$quota  = $quotas[$tier] ?? 10;

echo json_encode([
    'ok'        => true,
    'user_id'   => $uid,
    'tier_used' => $tier,
    'metric'    => $metric,
    'quota'     => $quota,
]);
