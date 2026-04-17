<?php
declare(strict_types=1);

/**
 * VULNERÁVEL — não checa role no servidor.
 *
 * O dashboard só mostra o link deste arquivo se tier === 'admin', mas o servidor
 * não valida o role. Qualquer usuário autenticado (free/pro) que adivinhe ou
 * descubra a URL acessa o endpoint. Segurança via "obscurity" + client-side.
 *
 * Veja admin_secure.php para a versão corrigida.
 */

require_once __DIR__ . '/../lib/session.php';

header('Content-Type: application/json');
require_login(); // <-- só exige estar logado, não admin

echo json_encode([
    'ok'        => true,
    'action'    => 'list_all_users',
    'users'     => [
        ['id' => 1, 'email' => 'free@teste.local',  'tier' => 'free'],
        ['id' => 2, 'email' => 'pro@teste.local',   'tier' => 'pro'],
        ['id' => 3, 'email' => 'admin@teste.local', 'tier' => 'admin'],
    ],
    'note'      => 'endpoint vulneravel: sem require_role admin',
]);
