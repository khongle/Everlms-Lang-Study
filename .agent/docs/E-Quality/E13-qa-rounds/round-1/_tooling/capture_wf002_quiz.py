#!/usr/bin/env python3
"""Capture WF-002 quiz attempt and grading with a real student session."""

from pathlib import Path
import os

from playwright.sync_api import expect, sync_playwright


ROOT = Path("/home/hainn/projects/everlms")
BASE_URL = "https://everlms.ddev.site"
STATE = ROOT / ".agent/docs/E00-UI-test/_tooling/state/student.json"
OUT = ROOT / ".agent/docs/E-Quality/E00-workflows/round-1/_screens/002-quiz-attempt-and-grading"
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

ESSAY = """On my first shift I would begin by greeting the resident calmly, using simple German
phrases, a warm tone, and enough time so she does not feel rushed. I would confirm her preferred
name, language needs, cultural preferences, diet, faith or privacy requirements, and document them
clearly for the team. If communication is limited, I would arrange interpreter support or approved
translation assistance rather than relying on guessing, family pressure, or informal interpretation
for sensitive care decisions. I would orient her to the room, call bell, daily routine, meal times,
toileting support, and who is responsible for her care during the shift. I would assess immediate
comfort, pain, hydration, mobility, skin risk, fall risk, and emotional distress, then escalate any
clinical concern to the registered nurse or ward manager. I would invite a familiar object or family
contact if she wants that, introduce one consistent staff member, and check back regularly so trust
is built through small reliable actions."""


def settle(page) -> None:
    try:
        page.wait_for_load_state("networkidle", timeout=15000)
    except Exception:
        pass
    try:
        page.evaluate("document.fonts && document.fonts.ready")
    except Exception:
        pass
    page.wait_for_timeout(350)


def shot(page, name: str) -> None:
    settle(page)
    page.screenshot(path=str(OUT / name), full_page=True, animations="disabled")


def check(page, name: str, value: str) -> None:
    locator = page.locator(f"input[name='{name}'][value='{value}']")
    expect(locator).to_be_attached(timeout=15000)
    locator.check(force=True)


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)

    with sync_playwright() as p:
        browser = p.chromium.launch(
            headless=True,
            args=["--no-sandbox", "--force-color-profile=srgb"],
        )
        context = browser.new_context(
            viewport={"width": 1440, "height": 1200},
            ignore_https_errors=True,
            storage_state=str(STATE),
        )
        context.add_init_script(
            """
            localStorage.removeItem('lms_quiz_draft_/quiz/43/take');
            localStorage.removeItem('lms_quiz_end_time_/quiz/43/take');
            """
        )
        page = context.new_page()

        result_id = os.environ.get("WF002_RESULT_ID")
        if result_id:
            page.goto(f"{BASE_URL}/quiz-result/{result_id}", wait_until="domcontentloaded", timeout=60000)
            expect(page.locator("body")).to_contain_text("Pending Review", timeout=15000)
            shot(page, "004-student-quiz-result-pending-review.png")

            page.goto(f"{BASE_URL}/my-quiz-results", wait_until="domcontentloaded", timeout=60000)
            expect(page.locator("body")).to_contain_text("Pending Review", timeout=15000)
            shot(page, "005-student-my-quiz-results.png")

            print(f"Recaptured WF-002 result screenshots in {OUT}")
            print(f"Final URL: {page.url}")

            context.close()
            browser.close()
            return

        page.goto(f"{BASE_URL}/quiz/{QUIZ_ID}/take", wait_until="domcontentloaded", timeout=60000)
        expect(page.locator("body")).not_to_contain_text("Access denied", timeout=10000)
        expect(page.locator("#lms-quiz-actual-form")).to_be_visible(timeout=15000)
        expect(page.locator("body")).to_contain_text("Final Assessment", timeout=15000)
        shot(page, "001-student-quiz-before-answer.png")

        check(page, "q_179", "422")
        for answer_id in ["425", "426", "428"]:
            check(page, "q_180[]", answer_id)
        check(page, "q_181", "0")
        check(page, "q_182", "430")
        for index, value in MATCHING.items():
            page.locator(f"select[name='q_183_{index}']").select_option(value=value)
        page.locator("input[name='q_184']").fill("contact")
        check(page, "q_185", "434")
        check(page, "q_186", "0")
        page.evaluate(
            """
            (ordering) => {
              const list = document.querySelector('#sortable-187');
              for (const value of ordering) {
                const item = [...list.querySelectorAll('.lms-sortable-item')]
                  .find((element) => element.querySelector('input')?.value === value);
                if (item) {
                  list.appendChild(item);
                }
              }
              list.dispatchEvent(new Event('change', { bubbles: true }));
            }
            """,
            ORDERING,
        )
        for answer_id in ["437", "438", "440"]:
            check(page, "q_188[]", answer_id)
        check(page, "q_189", "442")
        page.locator("input[name='q_190']").fill("72 hours")
        check(page, "q_191", "0")
        for answer_id in ["445", "446", "447"]:
            check(page, "q_192[]", answer_id)
        page.locator("textarea[name='q_193']").fill(ESSAY)
        shot(page, "002-student-quiz-after-fill.png")

        page.locator("#btn-trigger-modal").click()
        expect(page.locator("#lms-submit-modal.active")).to_be_visible(timeout=15000)
        shot(page, "003-student-submit-confirmation-modal.png")

        page.locator("#btn-modal-confirm").click()
        page.wait_for_url("**/quiz-result/**", timeout=30000)
        expect(page.locator("body")).to_contain_text("Pending Manual Review", timeout=15000)
        shot(page, "004-student-quiz-result-pending-review.png")

        page.goto(f"{BASE_URL}/my-quiz-results", wait_until="domcontentloaded", timeout=60000)
        expect(page.locator("body")).to_contain_text("Final Assessment", timeout=15000)
        shot(page, "005-student-my-quiz-results.png")

        print(f"Captured WF-002 screenshots in {OUT}")
        print(f"Final URL: {page.url}")

        context.close()
        browser.close()


if __name__ == "__main__":
    main()
