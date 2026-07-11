#!/usr/bin/env python3
"""WF-001 enroll path as a NON-member student (julia.nowak uid 71).

Tests the true 'Enroll Now' journey for a free course when the learner has
no membership entitlement (unlike maria who is a member).
"""
import subprocess
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from lib import BASE_URL, SCREENS, VIEWPORT, shot  # noqa: E402
from playwright.sync_api import sync_playwright  # noqa: E402

OUT = SCREENS / "001-enrollment-and-learning"
UID = 71
FREE_COURSE = 108


def uli(uid):
    out = subprocess.check_output(
        ["ddev", "drush", "user:login", f"--uid={uid}", "--no-browser"],
        cwd="/home/hainn/projects/everlms", text=True,
    )
    return out.strip().splitlines()[0].strip()


def main():
    login_url = uli(UID)
    with sync_playwright() as pw:
        browser = pw.chromium.launch(headless=True, args=["--no-sandbox", "--force-color-profile=srgb"])
        ctx = browser.new_context(viewport=VIEWPORT, ignore_https_errors=True)
        page = ctx.new_page()
        page.goto(login_url, wait_until="domcontentloaded", timeout=60000)

        # Course detail, genuinely not enrolled + no membership
        page.goto(f"{BASE_URL}/courses/{FREE_COURSE}", wait_until="domcontentloaded", timeout=60000)
        shot(page, OUT, "10-newstudent-course-detail-free-not-enrolled.png")

        # Click primary CTA (whatever its label) and follow
        cta = page.locator("aside a.btn-lms-primary, aside a.btn-lms-outline").first
        label = cta.inner_text().strip()
        print(f"  CTA label = {label!r}")
        cta.click(timeout=8000)
        page.wait_for_load_state("domcontentloaded", timeout=30000)
        print(f"  after CTA URL = {page.url}")
        shot(page, OUT, "11-newstudent-enroll-destination.png")

        ctx.close()
        browser.close()
    print(f"done -> {OUT}")


if __name__ == "__main__":
    main()
