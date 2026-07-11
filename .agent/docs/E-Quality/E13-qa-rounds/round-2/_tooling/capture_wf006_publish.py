#!/usr/bin/env python3
"""WF-006 draft→publish→visible — draft course hidden from catalog; instructor
publishes via the edit form; course then appears in the public catalog. Round-2."""
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from lib import BASE_URL, SCREENS, Session, run, shot  # noqa: E402

OUT = SCREENS / "006-course-authoring-and-publishing"
COURSE = 140
NAME = "WF006-R2"


def catalog_has(page):
    page.goto(f"{BASE_URL}/courses?search={NAME}", wait_until="networkidle", timeout=60000)
    return NAME in page.locator("body").inner_text()


def main(pw):
    # Draft hidden from anonymous catalog
    with Session(pw, None) as page:
        found = catalog_has(page)
        shot(page, OUT, "05-anon-catalog-draft-hidden.png")
        print("  draft in catalog (expect False):", found)

    # Instructor publishes via edit form
    with Session(pw, "instructor") as page:
        page.goto(f"{BASE_URL}/workspace/courses", wait_until="networkidle", timeout=60000)
        shot(page, OUT, "06-instructor-list-shows-draft.png")
        page.goto(f"{BASE_URL}/workspace/courses/{COURSE}/edit", wait_until="networkidle", timeout=60000)
        cb = page.locator("input[name='status[value]']")
        print("  published checkbox present:", cb.count(), "checked:", cb.first.is_checked() if cb.count() else "n/a")
        if cb.count():
            cb.first.check()
        shot(page, OUT, "07-instructor-edit-publish-checked.png")
        page.get_by_role("button", name="Save").first.click()
        page.wait_for_load_state("networkidle", timeout=45000)

    # Published now visible in anonymous catalog
    with Session(pw, None) as page:
        found = catalog_has(page)
        shot(page, OUT, "08-anon-catalog-published-visible.png")
        print("  published in catalog (expect True):", found)


if __name__ == "__main__":
    run(main)
