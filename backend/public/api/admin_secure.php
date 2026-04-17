<?php
declare(strict_types=1);

/**
 * Versão SEGURA de admin.php.
 *
 * Correção: require_role('admin') checa o tier armazenado na sessão server-side.
 * Usuários free/pro recebem 403 mesmo com sessão válida.
 */

require_once __DIR__ . '/../lib/session.php';

header('Content-Type: application/json');
require_role('admin');

echo json_encode([
    'ok'     => true,
    'action' => 'list_all_users',
    'users'  => [
        ['id' => 1, 'email' => 'free@teste.local',  'tier' => 'free'],
        ['id' => 2, 'email' => 'pro@teste.local',   'tier' => 'pro'],
        ['id' => 3, 'email' => 'admin@teste.local', 'tier' => 'admin'],
    ],
]);
