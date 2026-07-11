#!/usr/bin/env python3
"""WF-003 Certificate issuance — learner view, public verification, prototype.

Certificate FDB2B9BA40 (maria.santos uid 61, course 88) was issued via the
WF-002 grading finalisation. Here we capture the learner certificate page, the
public verifier (valid), and the prototype for comparison.
"""
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from lib import BASE_URL, SCREENS, Session, proto_shot, run, shot  # noqa: E402

OUT = SCREENS / "003-certificate-issuance"
CODE = "FDB2B9BA40"


def main(pw):
    # Learner certificate list
    with Session(pw, "student") as page:
        page.goto(f"{BASE_URL}/my-certificates", wait_until="networkidle", timeout=60000)
        shot(page, OUT, "01-student-my-certificates.png")
        body = page.locator("body").inner_text()
        print("  my-certs contains code:", CODE in body)

    # Public verifier (anonymous)
    with Session(pw, None) as page:
        page.goto(f"{BASE_URL}/certificate/verify", wait_until="networkidle", timeout=60000)
        shot(page, OUT, "02-anonymous-verify-before.png")
        page.locator("input[name='certificate_code']").fill(CODE)
        shot(page, OUT, "03-anonymous-verify-after-fill.png")
        page.get_by_role("button", name="Verify").click()
        page.wait_for_timeout(2500)
        shot(page, OUT, "04-anonymous-verify-result-valid.png")
        res = page.locator("body").inner_text()
        print("  verify shows valid/name:", ("Valid" in res or "maria" in res.lower() or "Santos" in res))

    # Prototype
    proto_shot(pw, "my-certificates.html", OUT, "01-student-my-certificates-prototype.png")
    print(f"done -> {OUT}")


if __name__ == "__main__":
    run(main)
