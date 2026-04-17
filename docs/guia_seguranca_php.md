# Guia de segurança — painel PHP com planos Free/Pro/Admin

> **Aviso:** este repositório contém código **intencionalmente vulnerável** para fins didáticos. Não exponha o laboratório na internet; rode apenas em `localhost`.

Este guia acompanha o código em `backend/public/` e a suíte de testes em `tests/`. Para cada classe de falha, mostramos a versão vulnerável, o fix, e o teste automatizado que valida o comportamento.

## 1. Modelo de ameaça

Painel SaaS com três papéis:

| tier  | pode                                   |
|-------|-----------------------------------------|
| free  | ver métricas próprias com cota baixa    |
| pro   | métricas próprias com cota alta         |
| admin | gerenciar todos os usuários             |

Ameaças consideradas:

- **T1 — Escalação vertical por client-side trust**: usuário `free` envia `tier=pro` no corpo da requisição e o backend obedece.
- **T2 — IDOR**: usuário envia `userId` arbitrário e lê dados de outro.
- **T3 — Escalação por rota oculta**: endpoint administrativo só está "escondido" no frontend; qualquer usuário autenticado que conheça a URL acessa.
- **T4 — Denial of Wallet / exaustão**: sem rate-limit, um cliente consegue esgotar recursos (DB, API de terceiros paga por request).
- **T5 — Roubo de sessão**: cookie sem `HttpOnly`/`SameSite`, ausência de regeneração no login.

## 2. Client-Side Trust — nunca confie no body para autorização

**Vulnerável** (`backend/public/api/monitor.php`):

```php
$tier   = (string)($body['tier']   ?? 'free');   // cliente dita
$userId = (int)   ($body['userId'] ?? 0);        // cliente dita
```

Qualquer um que abra o DevTools e edite a requisição vira `pro` ou lê dados de outro usuário.

**Fix** (`backend/public/api/monitor_secure.php`):

```php
require_login();
$uid  = (int) current_user_id();     // vem de $_SESSION
$tier = (string) current_user_tier(); // vem de $_SESSION
```

**Regra:** dados de identidade e autorização (`user_id`, `tier`, `role`, `plan_id`, `org_id`) só podem sair do servidor — sessão, JWT assinado no backend, ou lookup no DB a cada request. Nunca leia esses campos do corpo/query/header vindos do cliente.

**Teste que valida:** `tests/test_tier_bypass.py::test_tier_upgrade_via_body` roda o mesmo PoC contra os dois endpoints e confirma que só a versão segura mantém `free`.

## 3. RBAC server-side em toda rota privilegiada

**Vulnerável** (`backend/public/api/admin.php`):

```php
require_login(); // só exige login, não admin
```

O link só aparece para admin no HTML, mas quem souber a URL acessa.

**Fix** (`backend/public/api/admin_secure.php`):

```php
require_role('admin'); // 403 para free/pro
```

Implementação em `lib/session.php`:

```php
function require_role(string $role): void {
    require_login();
    $order = ['free' => 0, 'pro' => 1, 'admin' => 2];
    $tier  = current_user_tier();
    if (($order[$tier] ?? -1) < $order[$role]) {
        http_response_code(403);
        exit(json_encode(['error' => 'forbidden']));
    }
}
```

**Princípio:** cada endpoint declara explicitamente seu requisito (`require_role('admin')`), como primeira linha. Esconder link no frontend não é controle de acesso.

**Teste que valida:** `tests/test_privilege_escalation.py` — free/pro recebem 403 em `admin_secure.php`, e só admin passa.

## 4. Rate-limit com Redis

**Vulnerável:** sem limite, 80 requisições concorrentes passam todas.

**Fix** (`backend/public/lib/ratelimit.php`):

```php
function rate_limit_check(string $key, int $limit, int $window): array {
    $r = redis_conn();
    $k = "rl:{$key}";
    $count = $r->incr($k);
    if ($count === 1) $r->expire($k, $window);
    return ['allowed' => $count <= $limit, 'count' => $count,
            'limit' => $limit, 'reset' => $r->ttl($k)];
}
```

Uso por endpoint:

```php
rate_limit_enforce("monitor:user:{$uid}", 60, 60); // 60 req / 60s por user
```

Pontos importantes:

- **Chave por identidade estável**: user_id quando logado, IP para rotas públicas (ex: `/login.php`, com limite bem menor — 10/min — contra brute force).
- **Fixed vs sliding window**: a versão acima é fixed window (simples e suficiente). Para sliding window use `ZADD` com timestamp e `ZREMRANGEBYSCORE`.
- **Headers informativos**: `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `X-RateLimit-Reset` e `Retry-After` em 429 são padrão esperado.
- **Não use APCu/arquivo**: não funciona entre workers/containers.
- **Combine com quotas de plano**: rate-limit (req/s) é técnico; quotas mensais (`free`: 10k/mês) são de negócio — ambas server-side.

**Teste que valida:** `tests/test_rate_limit.py` dispara 80 reqs concorrentes; só a versão segura produz 429.

## 5. Sessão segura

Config em `backend/php.ini`:

```ini
session.cookie_httponly = 1   ; JS não lê o cookie
session.cookie_samesite = Lax ; bloqueia CSRF em navegação cross-site
session.use_strict_mode = 1   ; rejeita session-id não emitido pelo servidor
```

Em produção: `session.cookie_secure = 1` (só HTTPS).

Regeneração no login (`lib/session.php`):

```php
function login_user(int $id, string $tier): void {
    session_regenerate_id(true); // invalida o id anterior
    $_SESSION['user_id'] = $id;
    $_SESSION['tier']    = $tier;
}
```

Isso previne **session fixation**: um atacante que plantou um `PHPSESSID` antes do login perde o acesso assim que a vítima autentica.

## 6. CSRF (para formulários com sessão via cookie)

`SameSite=Lax` cobre a maioria dos casos em navegadores modernos, mas adicione token para POSTs que causam side-effects:

```php
// ao renderizar o form
$_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(32));
echo '<input type="hidden" name="csrf" value="'. htmlspecialchars($_SESSION['csrf']) .'">';

// ao processar
if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    http_response_code(403);
    exit('csrf');
}
```

Para APIs JSON consumidas por SPA própria, prefira o padrão **double-submit cookie** ou header customizado (`X-CSRF-Token`) — o `SameSite=Lax` já impede o navegador de enviar o cookie em requisições cross-origin iniciadas por outro site.

## 7. Hardening adicional

- **SQL**: sempre `PDO` com prepared statements e `ATTR_EMULATE_PREPARES=false` (ver `lib/db.php`). Nunca interpole strings em queries.
- **Senhas**: `password_hash($pw, PASSWORD_BCRYPT)` para armazenar, `password_verify()` para checar. Nunca SHA-1/MD5.
- **Mensagens de erro no login**: idênticas para "email não existe" e "senha errada" — evita user enumeration.
- **Headers**:
  ```php
  header('X-Content-Type-Options: nosniff');
  header('X-Frame-Options: DENY');
  header("Content-Security-Policy: default-src 'self'");
  header('Referrer-Policy: same-origin');
  ```
- **Logs**: registre tentativas de login falhas, 403s e 429s; alerte em picos. **Não logue** senhas nem tokens.
- **Secrets**: `.env` fora do repositório. Em produção, vault/secret manager.

## 8. Como executar e reproduzir

```bash
cp .env.example .env
docker compose up -d --build
pip install -r tests/requirements.txt
pytest
```

Resultado esperado: todos os testes passam. Os testes "vulnerável" passam porque confirmam a falha; os "seguro" passam porque confirmam a mitigação. Se algum `*_secure*` falhar, o fix regrediu.

Para encerrar e limpar volumes: `docker compose down -v`.

## 9. Checklist de code review

- [ ] Toda rota privilegiada começa com `require_role(...)` ou equivalente
- [ ] Identidade/role nunca são lidas do body/query/header do cliente
- [ ] Rate-limit aplicado em login, reset de senha, endpoints caros
- [ ] Prepared statements em 100% das queries
- [ ] Senha com `password_hash`/`password_verify`
- [ ] `session_regenerate_id(true)` no login e em mudança de privilégio
- [ ] Cookie de sessão: `HttpOnly`, `SameSite=Lax`, `Secure` em produção
- [ ] CSRF token em POSTs de side-effect (ou padrão equivalente)
- [ ] Headers de segurança (CSP, X-Frame-Options, X-Content-Type-Options)
- [ ] Logs de 401/403/429 com alertas em picos
- [ ] Secrets fora do repositório
