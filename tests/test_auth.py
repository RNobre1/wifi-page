"""Smoke tests for the login flow and session cookie hardening."""
import requests


def test_login_ok(base_url: str) -> None:
    s = requests.Session()
    r = s.post(
        f"{base_url}/login.php",
        data={"email": "free@teste.local", "password": "senha123"},
        headers={"Accept": "application/json"},
        timeout=5,
    )
    assert r.status_code == 200
    body = r.json()
    assert body["ok"] is True
    assert body["tier"] == "free"


def test_login_bad_credentials(base_url: str) -> None:
    r = requests.post(
        f"{base_url}/login.php",
        data={"email": "free@teste.local", "password": "wrong"},
        headers={"Accept": "application/json"},
        timeout=5,
    )
    assert r.status_code == 401
    assert r.json()["ok"] is False


def test_session_cookie_is_httponly(base_url: str) -> None:
    s = requests.Session()
    s.post(
        f"{base_url}/login.php",
        data={"email": "free@teste.local", "password": "senha123"},
        headers={"Accept": "application/json"},
        timeout=5,
    )
    # requests parses cookies; HttpOnly lives in the raw Set-Cookie header
    raw = s.cookies._cookies  # type: ignore[attr-defined]
    found = False
    for _domain, paths in raw.items():
        for _path, jar in paths.items():
            for name, cookie in jar.items():
                if name.upper() in ("PHPSESSID",):
                    assert cookie.has_nonstandard_attr("HttpOnly"), "session cookie deve ser HttpOnly"
                    found = True
    assert found, "PHPSESSID não encontrado"


def test_api_requires_login(base_url: str) -> None:
    r = requests.post(
        f"{base_url}/api/monitor_secure.php",
        json={"metric": "pings"},
        timeout=5,
    )
    assert r.status_code == 401
