<?php
declare(strict_types=1);

function redis_conn(): Redis {
    static $r = null;
    if ($r !== null) {
        return $r;
    }
    $r = new Redis();
    $r->connect(getenv('REDIS_HOST') ?: 'redis', (int)(getenv('REDIS_PORT') ?: 6379));
    return $r;
}

/**
 * Fixed-window rate limit keyed by $key.
 * Returns ['allowed' => bool, 'count' => int, 'limit' => int, 'reset' => int].
 */
function rate_limit_check(string $key, int $limit, int $window_seconds): array {
    $r = redis_conn();
    $redis_key = "rl:{$key}";
    $count = $r->incr($redis_key);
    if ($count === 1) {
        $r->expire($redis_key, $window_seconds);
    }
    $ttl = $r->ttl($redis_key);
    return [
        'allowed' => $count <= $limit,
        'count'   => $count,
        'limit'   => $limit,
        'reset'   => $ttl > 0 ? $ttl : $window_seconds,
    ];
}

function rate_limit_enforce(string $key, int $limit, int $window_seconds): void {
    $res = rate_limit_check($key, $limit, $window_seconds);
    header('X-RateLimit-Limit: ' . $res['limit']);
    header('X-RateLimit-Remaining: ' . max(0, $res['limit'] - $res['count']));
    header('X-RateLimit-Reset: ' . $res['reset']);
    if (!$res['allowed']) {
        http_response_code(429);
        header('Content-Type: application/json');
        header('Retry-After: ' . $res['reset']);
        echo json_encode(['error' => 'rate_limited', 'retry_after' => $res['reset']]);
        exit;
    }
}
