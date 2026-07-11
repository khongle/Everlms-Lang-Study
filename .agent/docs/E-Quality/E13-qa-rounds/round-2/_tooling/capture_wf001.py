#!/usr/bin/env python3
"""WF-001 Enrollment & learning — real student session capture (round-2).

Full flow: discover -> course detail (not enrolled) -> enroll CTA ->
my-courses -> lesson player -> mark complete -> progress.
Student: maria.santos (uid 61). Fresh-enroll target: course 108 (free).
Learning target: course 88 (already enrolled), first incomplete lesson 1176.
"""
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from lib import BASE_URL, SCREENS, Session, run, shot  # noqa: E402

OUT = SCREENS / "001-enrollment-and-learning"
NEW_COURSE = 108          # free, maria NOT enrolled -> fresh enroll demo
LEARN_COURSE = 88         # already enrolled
INCOMPLETE_LESSON = 1176  # first not-yet-completed lesson in course 88


def main(pw):
    with Session(pw, "student") as page:
        # 1. Dashboard (continue learning entry)
        page.goto(f"{BASE_URL}/dashboard", wait_until="domcontentloaded", timeout=60000)
        shot(page, OUT, "01-student-dashboard.png")

        # 2. Course catalog (discover)
        page.goto(f"{BASE_URL}/courses", wait_until="domcontentloaded", timeout=60000)
        shot(page, OUT, "02-student-course-catalog.png")

        # 3. Course detail, NOT enrolled (free course 108) — enroll CTA visible
        page.goto(f"{BASE_URL}/courses/{NEW_COURSE}", wait_until="domcontentloaded", timeout=60000)
        shot(page, OUT, "03-student-course-detail-not-enrolled.png")

        # 4. Click the primary enroll CTA and observe where it lands
        try:
            cta = page.locator("a.btn-lms-primary", has_text="Enroll").first
            cta.click(timeout=8000)
            page.wait_for_load_state("domcontentloaded", timeout=30000)
        except Exception as exc:
            print(f"  enroll CTA click issue: {exc}")
        print(f"  after enroll click URL = {page.url}")
        shot(page, OUT, "04-student-enroll-destination.png")

        # 5. Course detail, already enrolled (course 88) — enrolled state
        page.goto(f"{BASE_URL}/courses/{LEARN_COURSE}", wait_until="domcontentloaded", timeout=60000)
        shot(page, OUT, "05-student-course-detail-enrolled.png")

        # 6. My courses
        page.goto(f"{BASE_URL}/my-courses", wait_until="domcontentloaded", timeout=60000)
        shot(page, OUT, "06-student-my-courses.png")

        # 7. Lesson player, before mark complete
        page.goto(f"{BASE_URL}/learn/{LEARN_COURSE}/{INCOMPLETE_LESSON}",
                  wait_until="domcontentloaded", timeout=60000)
        shot(page, OUT, "07-student-lesson-before-complete.png")

        # 8. Mark complete
        try:
            btn = page.locator("#lms-mark-complete")
            btn.click(timeout=8000)
            page.wait_for_timeout(2500)
        except Exception as exc:
            print(f"  mark-complete issue: {exc}")
        print(f"  after mark-complete URL = {page.url}")
        shot(page, OUT, "08-student-lesson-after-complete.png")

        # 9. Dashboard, progress updated
        page.goto(f"{BASE_URL}/dashboard", wait_until="domcontentloaded", timeout=60000)
        shot(page, OUT, "09-student-dashboard-progress.png")

    print(f"WF-001 real screenshots -> {OUT}")


if __name__ == "__main__":
    run(main)
