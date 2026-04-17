"""Shared pytest fixtures for the local PHP security lab."""
from __future__ import annotations

import os
import time

import pytest
import requests


BASE_URL = os.environ.get("LAB_BASE_URL", "http://localhost:8080")
SEED_PASSWORD = "senha123"


@pytest.fixture(scope="session")
def base_url() -> str:
    return BASE_URL


@pytest.fixture(scope="session", autouse=True)
def wait_for_server(base_url: str) -> None:
    """Block until the PHP container is responsive (docker compose warmup)."""
    deadline = time.time() + 30
    while time.time() < deadline:
        try:
            r = requests.get(f"{base_url}/index.php", timeout=2)
            if r.status_code == 200:
                return
        except requests.RequestException:
            pass
        time.sleep(1)
    pytest.fail(f"server at {base_url} did not come up in 30s")


def _login(base: str, email: str) -> requests.Session:
    s = requests.Session()
    r = s.post(
        f"{base}/login.php",
        data={"email": email, "password": SEED_PASSWORD},
        headers={"Accept": "application/json"},
        allow_redirects=False,
        timeout=5,
    )
    assert r.status_code == 200, f"login falhou para {email}: {r.status_code} {r.text}"
    body = r.json()
    assert body.get("ok") is True, f"login não retornou ok para {email}: {body}"
    return s


@pytest.fixture
def session_free(base_url: str) -> requests.Session:
    return _login(base_url, "free@teste.local")


@pytest.fixture
def session_pro(base_url: str) -> requests.Session:
    return _login(base_url, "pro@teste.local")


@pytest.fixture
def session_admin(base_url: str) -> requests.Session:
    return _login(base_url, "admin@teste.local")
