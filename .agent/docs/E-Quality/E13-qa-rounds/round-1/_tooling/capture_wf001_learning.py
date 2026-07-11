#!/usr/bin/env python3
"""Capture WF-001 enrollment and learning with a real student session."""

from pathlib import Path

from playwright.sync_api import expect, sync_playwright


ROOT = Path("/home/hainn/projects/everlms")
BASE_URL = "https://everlms.ddev.site"
STATE = ROOT / ".agent/docs/E00-UI-test/_tooling/state/student.json"
OUT = ROOT / ".agent/docs/E-Quality/E00-workflows/round-1/_screens/001-enrollment-and-learning"
COURSE_ID = 88
LESSON_ID = 1175
NEXT_LESSON_ID = 1176


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


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)

    with sync_playwright() as p:
        browser = p.chromium.launch(
            headless=True,
            args=["--no-sandbox", "--force-color-profile=srgb"],
        )
        context = browser.new_context(
            viewport={"width": 1440, "height": 1100},
            ignore_https_errors=True,
            storage_state=str(STATE),
        )
        page = context.new_page()

        page.goto(f"{BASE_URL}/dashboard", wait_until="domcontentloaded", timeout=60000)
        expect(page.locator("body")).to_contain_text("Dashboard", timeout=15000)
        shot(page, "001-student-dashboard-before-learning.png")

        page.goto(f"{BASE_URL}/my-courses", wait_until="domcontentloaded", timeout=60000)
        expect(page.locator("body")).to_contain_text("Introduction to German Geriatric Care Standards", timeout=15000)
        shot(page, "002-student-my-courses-deduped.png")

        page.goto(f"{BASE_URL}/courses/{COURSE_ID}", wait_until="domcontentloaded", timeout=60000)
        expect(page.locator("body")).to_contain_text("Introduction to German Geriatric Care Standards", timeout=15000)
        shot(page, "003-student-course-detail-enrolled.png")

        page.goto(f"{BASE_URL}/learn/{COURSE_ID}/{LESSON_ID}", wait_until="domcontentloaded", timeout=60000)
        expect(page.locator("#lms-mark-complete")).to_be_visible(timeout=15000)
        shot(page, "004-student-lesson-before-complete.png")

        page.locator("#lms-mark-complete").click()
        expect(page.locator("#lms-mark-complete")).to_contain_text("Completed", timeout=15000)
        shot(page, "005-student-lesson-after-complete-click.png")

        page.wait_for_url(f"**/learn/{COURSE_ID}/{NEXT_LESSON_ID}", timeout=30000)
        expect(page.locator("body")).to_contain_text("Mandatory Patient Documentation", timeout=15000)
        shot(page, "006-student-next-lesson-progress-updated.png")

        print(f"Captured WF-001 screenshots in {OUT}")
        print(f"Final URL: {page.url}")

        context.close()
        browser.close()


if __name__ == "__main__":
    main()
