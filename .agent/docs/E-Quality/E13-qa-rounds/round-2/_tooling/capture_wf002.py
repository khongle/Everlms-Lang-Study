#!/usr/bin/env python3
"""WF-002 Quiz attempt & grading — real student attempt (round-2).

Student maria.santos (uid 61) takes quiz 43 ("Final Assessment: Introduction to
German...", course 88, threshold 70). The quiz contains an essay (q_193) so the
result must land as *Pending Manual Review* with a provisional score — never
shown as passed until an instructor grades it.
"""
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from lib import BASE_URL, SCREENS, STATE_DIR, VIEWPORT, shot  # noqa: E402
from playwright.sync_api import expect, sync_playwright  # noqa: E402

OUT = SCREENS / "002-quiz-attempt-and-grading"
QUIZ_ID = 43

MATCHING = {
    0: "Yellow general waste bag",
    1: "Orange sealed and labeled bag",
    2: "Yellow sharps container — never recap",
    3: "Let bleed, wash, report, occupational health within 2 hours",
}
ORDERING = [
    "Let the wound bleed freely for a short time",
    "Wash thoroughly with soap and water",
    "Report immediately to your supervisor/charge nurse",
    "Visit occupational health within 2 hours",
    "Complete an Incident Report (Schadensmeldung)",
]
ESSAY = (
    "On my first shift I would greet the resident calmly in simple German, confirm her "
    "preferred name, language, cultural and dietary needs, and document them for the team. "
    "I would arrange interpreter support rather than guessing, orient her to the room, call "
    "bell and routine, assess pain, hydration, mobility, skin and fall risk, and escalate any "
    "clinical concern to the registered nurse. I would introduce one consistent staff member "
    "and check back regularly so trust is built through small, reliable actions."
)


def check(page, name, value):
    loc = page.locator(f"input[name='{name}'][value='{value}']")
    expect(loc).to_be_attached(timeout=15000)
    loc.check(force=True)


def main():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True, args=["--no-sandbox", "--force-color-profile=srgb"])
        context = browser.new_context(
            viewport=VIEWPORT, ignore_https_errors=True,
            storage_state=str(STATE_DIR / "student.json"),
        )
        context.add_init_script(
            "localStorage.removeItem('lms_quiz_draft_/quiz/43/take');"
            "localStorage.removeItem('lms_quiz_end_time_/quiz/43/take');"
        )
        page = context.new_page()

        page.goto(f"{BASE_URL}/quiz/{QUIZ_ID}/take", wait_until="domcontentloaded", timeout=60000)
        expect(page.locator("#lms-quiz-actual-form")).to_be_visible(timeout=15000)
        shot(page, OUT, "01-student-quiz-before-answer.png")

        check(page, "q_179", "422")
        for a in ["425", "426", "428"]:
            check(page, "q_180[]", a)
        check(page, "q_181", "0")
        check(page, "q_182", "430")
        for i, v in MATCHING.items():
            page.locator(f"select[name='q_183_{i}']").select_option(value=v)
        page.locator("input[name='q_184']").fill("contact")
        check(page, "q_185", "434")
        check(page, "q_186", "0")
        page.evaluate(
            """(ordering) => {
              const list = document.querySelector('#sortable-187');
              for (const value of ordering) {
                const item = [...list.querySelectorAll('.lms-sortable-item')]
                  .find((el) => el.querySelector('input')?.value === value);
                if (item) list.appendChild(item);
              }
              list.dispatchEvent(new Event('change', { bubbles: true }));
            }""",
            ORDERING,
        )
        for a in ["437", "438", "440"]:
            check(page, "q_188[]", a)
        check(page, "q_189", "442")
        page.locator("input[name='q_190']").fill("72 hours")
        check(page, "q_191", "0")
        for a in ["445", "446", "447"]:
            check(page, "q_192[]", a)
        page.locator("textarea[name='q_193']").fill(ESSAY)
        shot(page, OUT, "02-student-quiz-after-fill.png")

        page.locator("#btn-trigger-modal").click()
        expect(page.locator("#lms-submit-modal.active")).to_be_visible(timeout=15000)
        shot(page, OUT, "03-student-submit-confirmation-modal.png")

        page.locator("#btn-modal-confirm").click()
        page.wait_for_url("**/quiz-result/**", timeout=30000)
        result_url = page.url
        result_id = result_url.rstrip("/").split("/")[-1]
        shot(page, OUT, "04-student-quiz-result-pending-review.png")

        page.goto(f"{BASE_URL}/my-quiz-results", wait_until="domcontentloaded", timeout=60000)
        shot(page, OUT, "05-student-my-quiz-results.png")

        print(f"RESULT_ID={result_id}")
        print(f"RESULT_URL={result_url}")
        context.close()
        browser.close()


if __name__ == "__main__":
    main()
