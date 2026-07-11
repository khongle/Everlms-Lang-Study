#!/usr/bin/env python3
"""WF-003 revoke + re-verify — manager revokes cert 53, public verifier shows it
as invalid; then (restored by caller) it is valid again. Round-2."""
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from lib import BASE_URL, SCREENS, Session, run, shot  # noqa: E402

OUT = SCREENS / "003-certificate-issuance"
CERT_ID = 53
CODE = "FDB2B9BA40"
MODE = sys.argv[1] if len(sys.argv) > 1 else "revoke"


def verify(pw, name):
    with Session(pw, None) as page:
        page.goto(f"{BASE_URL}/certificate/verify", wait_until="networkidle", timeout=60000)
        page.locator("input[name='certificate_code']").fill(CODE)
        page.get_by_role("button", name="Verify").click()
        page.wait_for_timeout(2000)
        shot(page, OUT, name)
        t = page.locator("body").inner_text()
        print("  verify text has 'Maria Santos':", "Maria Santos" in t,
              "| 'REVOKED':", "REVOKED" in t.upper(), "| 'VALID':", "VALID CERTIFICATE" in t.upper())


def main(pw):
    if MODE == "revoke":
        with Session(pw, "manager") as page:
            page.goto(f"{BASE_URL}/workspace/certificates/{CERT_ID}/revoke",
                      wait_until="domcontentloaded", timeout=60000)
            body = page.locator("body").inner_text()
            print("  revoke page access denied:", "Access denied" in body)
            page.locator("textarea[name='revoke_reason']").fill("QA round-2 revoke test (restored afterwards).")
            shot(page, OUT, "05-manager-revoke-form.png")
            page.locator("form input[type='submit'], form button[type='submit']").first.click()
            page.wait_for_load_state("domcontentloaded", timeout=30000)
            print("  after revoke URL:", page.url)
        verify(pw, "06-anonymous-verify-revoked.png")
    else:
        verify(pw, "04-anonymous-verify-result-valid.png")


if __name__ == "__main__":
    run(main)
