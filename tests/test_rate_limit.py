"""PoC de rate-limit.

Dispara 80 requests concorrentes (ThreadPoolExecutor, max_workers=20) contra
o mesmo endpoint, reusando uma única sessão autenticada — tudo em localhost.

- monitor.php (vulnerável): nenhum 429.
- monitor_secure.php (fix): ao menos um 429 acima do limite configurado (60/60s).
"""
from __future__ import annotations

from concurrent.futures import ThreadPoolExecutor
from typing import List

import requests


TOTAL_REQUESTS = 80
MAX_WORKERS    = 20


def _burst(session: requests.Session, url: str) -> List[int]:
    def one(_: int) -> int:
        r = session.post(url, json={"metric": "pings"}, timeout=5)
        return r.status_code

    with ThreadPoolExecutor(max_workers=MAX_WORKERS) as pool:
        return list(pool.map(one, range(TOTAL_REQUESTS)))


def test_monitor_vulneravel_sem_rate_limit(base_url: str, session_pro) -> None:
    codes = _burst(session_pro, f"{base_url}/api/monitor.php")
    assert all(c == 200 for c in codes), (
        f"endpoint vulneravel deveria aceitar tudo; recebidos: {set(codes)}"
    )
    assert 429 not in codes


def test_monitor_secure_aplica_rate_limit(base_url: str, session_pro) -> None:
    codes = _burst(session_pro, f"{base_url}/api/monitor_secure.php")
    assert 429 in codes, (
        f"endpoint seguro deveria retornar 429 acima do limite; recebidos: {set(codes)}"
    )
    # sanity: alguns sucessos abaixo do limite
    assert 200 in codes
