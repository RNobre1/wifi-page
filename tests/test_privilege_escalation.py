"""PoC: usuário 'free' acessa endpoints administrativos."""


def test_admin_vulneravel_aceita_usuario_free(base_url: str, session_free) -> None:
    """admin.php só exige login — um user free consegue listar todos os usuários."""
    r = session_free.get(f"{base_url}/api/admin.php", timeout=5)
    assert r.status_code == 200, r.text
    body = r.json()
    assert body["ok"] is True
    assert "users" in body, "endpoint vulneravel expos dados administrativos ao user free"


def test_admin_secure_bloqueia_usuario_free(base_url: str, session_free) -> None:
    """admin_secure.php usa require_role('admin') — user free recebe 403."""
    r = session_free.get(f"{base_url}/api/admin_secure.php", timeout=5)
    assert r.status_code == 403, r.text
    body = r.json()
    assert body["error"] == "forbidden"
    assert body["actual"] == "free"


def test_admin_secure_bloqueia_usuario_pro(base_url: str, session_pro) -> None:
    r = session_pro.get(f"{base_url}/api/admin_secure.php", timeout=5)
    assert r.status_code == 403, r.text


def test_admin_secure_libera_admin(base_url: str, session_admin) -> None:
    r = session_admin.get(f"{base_url}/api/admin_secure.php", timeout=5)
    assert r.status_code == 200
    assert r.json()["ok"] is True
