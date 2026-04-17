"""PoC: usuário 'free' tenta virar 'pro' mandando tier no body.

Contra monitor.php (vulnerável) o bypass funciona.
Contra monitor_secure.php (fix) o servidor ignora o body e mantém 'free'.
"""
import pytest


@pytest.mark.parametrize(
    "endpoint, bypass_possivel",
    [
        ("/api/monitor.php",        True),   # vulnerável
        ("/api/monitor_secure.php", False),  # seguro
    ],
)
def test_tier_upgrade_via_body(base_url: str, session_free, endpoint: str, bypass_possivel: bool) -> None:
    r = session_free.post(
        f"{base_url}{endpoint}",
        json={"tier": "pro", "userId": 1, "metric": "pings"},
        timeout=5,
    )
    assert r.status_code == 200, r.text
    body = r.json()
    tier_efetivo = body["tier_used"]

    if bypass_possivel:
        assert tier_efetivo == "pro", (
            f"endpoint vulneravel deveria aceitar tier do body, recebeu {tier_efetivo}"
        )
    else:
        assert tier_efetivo == "free", (
            f"endpoint seguro deveria ignorar tier do body e manter free, recebeu {tier_efetivo}"
        )


@pytest.mark.parametrize(
    "endpoint, idor_possivel",
    [
        ("/api/monitor.php",        True),
        ("/api/monitor_secure.php", False),
    ],
)
def test_idor_via_userid(base_url: str, session_free, endpoint: str, idor_possivel: bool) -> None:
    """Usuário free tenta consultar recursos de user_id=3 (admin)."""
    r = session_free.post(
        f"{base_url}{endpoint}",
        json={"tier": "free", "userId": 3, "metric": "pings"},
        timeout=5,
    )
    assert r.status_code == 200
    body = r.json()

    if idor_possivel:
        assert body["user_id"] == 3, "endpoint vulneravel deveria refletir userId do body"
    else:
        assert body["user_id"] != 3, "endpoint seguro nao deveria aceitar userId do body"
