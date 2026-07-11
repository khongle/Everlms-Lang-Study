#!/usr/bin/env python3
"""WF-002 instructor grading — sabine.mueller (uid 56) grades maria's pending
essay on quiz result 98, finalising the result (round-2)."""
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from lib import BASE_URL, SCREENS, Session, run, shot  # noqa: E402

OUT = SCREENS / "002-quiz-attempt-and-grading"
RESULT_ID = 98


def main(pw):
    with Session(pw, "instructor") as page:
        page.goto(f"{BASE_URL}/manage/quiz-results/{RESULT_ID}/grade",
                  wait_until="domcontentloaded", timeout=60000)
        body = page.locator("body").inner_text()
        if "Access denied" in body or "not have access" in body.lower():
            print("ACCESS DENIED for instructor")
        shot(page, OUT, "06-instructor-grade-form-before.png")

        # Award full marks to every pending response (fill each number input to its max).
        inputs = page.locator("input[type='number']")
        n = inputs.count()
        print(f"  pending inputs: {n}")
        for i in range(n):
            el = inputs.nth(i)
            mx = el.get_attribute("max") or "1"
            el.fill(mx)
        fb = page.locator("textarea")
        if fb.count():
            fb.first.fill("Thoughtful, safe, resident-centred answer that covers communication, "
                           "assessment, escalation and trust-building. Full marks.")
        shot(page, OUT, "07-instructor-grade-form-after-fill.png")

        page.get_by_role("button", name="Finalize grade").click()
        page.wait_for_load_state("domcontentloaded", timeout=30000)
        print(f"  after submit URL = {page.url}")
        shot(page, OUT, "08-instructor-result-finalized.png")


if __name__ == "__main__":
    run(main)
