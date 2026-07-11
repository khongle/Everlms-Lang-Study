#!/usr/bin/env python3
"""WF-006 create persistence — instructor creates a draft course via the real
entity form and saves. Round-2."""
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from lib import BASE_URL, SCREENS, Session, run, shot  # noqa: E402

OUT = SCREENS / "006-course-authoring-and-publishing"
TITLE = "WF006-R2 Geriatric Safety Basics"


def main(pw):
    with Session(pw, "instructor") as page:
        page.goto(f"{BASE_URL}/workspace/courses/create", wait_until="networkidle", timeout=60000)
        name = page.locator("input[name='name[0][value]']")
        if not name.count():
            name = page.locator("form input[type='text']").first
        name.fill(TITLE)
        shot(page, OUT, "03-instructor-create-form-after-fill.png")
        page.get_by_role("button", name="Save").first.click()
        page.wait_for_load_state("networkidle", timeout=45000)
        print("  after save URL:", page.url)
        shot(page, OUT, "04-instructor-after-save.png")


if __name__ == "__main__":
    run(main)
