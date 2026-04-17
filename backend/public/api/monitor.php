<?php
declare(strict_types=1);

/**
 * VULNERÁVEL — propositalmente inseguro para fins didáticos.
 *
 * Falhas plantadas:
 *   1. Confia em $body['tier'] vindo do cliente (client-side trust / privilege escalation).
 *   2. Aceita $body['userId'] arbitrário sem verificar se bate com a sessão (IDOR).
 *   3. Sem rate-limit (denial-of-wallet / scraping).
 *
 * Veja monitor_secure.php para a versão corrigida.
 */

require_once __DIR__ . '/../lib/session.php';

header('Content-Type: application/json');
require_login();

$raw  = file_get_contents('php://input');
$body = json_decode($raw, true) ?: [];

$tier   = (string)($body['tier']   ?? 'free');   // (1) cliente dita o plano
$userId = (int)   ($body['userId'] ?? 0);        // (2) cliente dita o id
$metric = (string)($body['metric'] ?? 'pings');

$quotas = ['free' => 10, 'pro' => 10000, 'admin' => -1];
$quota  = $quotas[$tier] ?? 10;

echo json_encode([
    'ok'         => true,
    'user_id'    => $userId,
    'tier_used'  => $tier,
    'metric'     => $metric,
    'quota'      => $quota,
    'note'       => 'endpoint vulneravel: tier/userId confiados do cliente',
]);
