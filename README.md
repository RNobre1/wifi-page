# SuperMonitor Lab — laboratório local de segurança PHP

> ⚠️ **Este repositório contém código intencionalmente vulnerável para fins didáticos.** Não exponha na internet. Rode apenas em `localhost`, atrás do Docker. Não reuse as senhas seed em nenhum outro lugar.

Painel PHP simulando um SaaS de monitoramento com planos **Free / Pro / Admin**. Cada vulnerabilidade comum (client-side trust, IDOR, escalação por rota oculta, ausência de rate-limit) está implementada em um arquivo **e** sua versão corrigida está lado a lado. Uma suíte pytest roda PoCs contra ambas e valida que o fix realmente bloqueia o ataque.

## Stack

- **Web**: PHP 8.2 + Apache
- **DB**: MySQL 8
- **Rate-limit**: Redis 7
- **Testes**: Python 3 + pytest + requests

## Pré-requisitos

- Docker + Docker Compose
- Python 3.11+ (só para rodar os testes)

## Subir o ambiente

```bash
cp .env.example .env
docker compose up -d --build
```

Abra `http://localhost:8080/` no navegador. Três contas seed são criadas no primeiro request (senha `senha123` em todas):

| email                 | tier  |
|-----------------------|-------|
| `free@teste.local`    | free  |
| `pro@teste.local`     | pro   |
| `admin@teste.local`   | admin |

## Rodar os testes

```bash
python -m venv .venv && source .venv/bin/activate
pip install -r tests/requirements.txt
pytest
```

O que cada teste valida:

- `tests/test_auth.py` — login ok/ruim, cookie `HttpOnly`, endpoint protegido retorna 401 sem sessão.
- `tests/test_tier_bypass.py` — PoC de `{"tier":"pro"}` e IDOR: passa no endpoint vulnerável, falha no seguro.
- `tests/test_privilege_escalation.py` — free/pro tentam acessar `admin.php` e `admin_secure.php`.
- `tests/test_rate_limit.py` — 80 requests concorrentes em threads; só a versão segura retorna 429.

## Estrutura

```
backend/
  Dockerfile                # php:8.2-apache + pdo_mysql + ext redis
  php.ini                   # cookie hardening
  db/init.sql               # schema users(id,email,pass_hash,tier)
  public/
    index.php               # landing
    login.php  logout.php  dashboard.php
    lib/                    # db, session, ratelimit, seed
    api/
      monitor.php           # VULNERÁVEL — confia em tier/userId do body
      monitor_secure.php    # FIX — tier/user vêm da sessão + rate-limit
      admin.php             # VULNERÁVEL — só exige login
      admin_secure.php      # FIX — require_role('admin')
tests/                      # pytest suite parametrizada
docs/guia_seguranca_php.md  # explicação técnica + checklist
```

## Próximas leituras

- `docs/guia_seguranca_php.md` — explicação de cada vulnerabilidade, fix e teste correspondente, com checklist de code review.

## Desligar

```bash
docker compose down -v   # remove containers e volume do DB
```
