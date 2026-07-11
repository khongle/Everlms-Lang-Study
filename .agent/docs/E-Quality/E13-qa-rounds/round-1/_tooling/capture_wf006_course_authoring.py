#!/usr/bin/env python3
"""Capture WF-006 course authoring and publishing through the real login form."""

import os
import re
from datetime import datetime
from pathlib import Path

from playwright.sync_api import expect, sync_playwright


ROOT = Path("/home/hainn/projects/everlms")
BASE_URL = "https://everlms.ddev.site"
OUT = ROOT / ".agent/docs/E-Quality/E00-workflows/round-1/_screens/006-course-authoring-and-publishing"

INSTRUCTOR_USER = "sabine.mueller"
INSTRUCTOR_PASSWORD = os.environ.get("EVERLMS_INSTRUCTOR_PASSWORD", "ChangeMe@123")  # credential-sweep:allow: test placeholder từ env, không phải secret thật


def settle(page) -> None:
    try:
        page.wait_for_load_state("networkidle", timeout=15000)
    except Exception:
        pass
    try:
        page.evaluate("document.fonts && document.fonts.ready")
    except Exception:
        pass
    page.wait_for_timeout(500)


def shot(page, name: str) -> None:
    settle(page)
    page.screenshot(path=str(OUT / name), full_page=True, animations="disabled")


def fill_editor(page, index: int, value: str) -> None:
    editors = page.locator(".ck-editor__editable")
    if editors.count() > index:
        editors.nth(index).click()
        editors.nth(index).fill(value)


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)

    stamp = datetime.now().strftime("%Y%m%d-%H%M%S")
    title = f"WF006 Mobility Safety Essentials {stamp}"
    chapter = "Foundations of Safe Mobility"
    lesson = "Safe transfer checklist"

    with sync_playwright() as p:
        browser = p.chromium.launch(
            headless=True,
            args=["--no-sandbox", "--force-color-profile=srgb"],
        )

        instructor_context = browser.new_context(
            viewport={"width": 1440, "height": 1200},
            ignore_https_errors=True,
        )
        page = instructor_context.new_page()

        page.goto(f"{BASE_URL}/user/login", wait_until="domcontentloaded", timeout=60000)
        expect(page.locator("input[name='name']")).to_be_visible(timeout=15000)
        shot(page, "001-instructor-login-before-fill.png")

        page.locator("input[name='name']").fill(INSTRUCTOR_USER)
        page.locator("input[name='pass']").fill(INSTRUCTOR_PASSWORD)
        shot(page, "002-instructor-login-after-fill.png")

        page.locator("input[type='submit']").click()
        page.wait_for_url("**/user/56**", timeout=30000)
        expect(page.locator("body")).to_contain_text("Sabine", timeout=15000)

        page.goto(f"{BASE_URL}/workspace/courses", wait_until="domcontentloaded", timeout=60000)
        expect(page.locator("body")).to_contain_text("Course Management", timeout=15000)
        shot(page, "003-instructor-course-list-before-create.png")

        page.goto(f"{BASE_URL}/workspace/courses/create", wait_until="domcontentloaded", timeout=60000)
        expect(page.locator("input[name='name[0][value]']")).to_be_visible(timeout=15000)
        shot(page, "004-instructor-course-create-before-fill.png")

        page.locator("input[name='name[0][value]']").fill(title)
        fill_editor(page, 0, "A practical starter course for safe resident mobility and transfer routines.")
        fill_editor(page, 1, "Instructors publish this course to validate the full course, chapter, and lesson authoring path.")
        shot(page, "005-instructor-course-info-after-fill.png")

        page.get_by_role("button", name="Course Collection").click()
        expect(page.locator("input[name='status[value]']")).to_be_visible(timeout=15000)
        page.locator("input[name='status[value]']").uncheck(force=True)
        page.locator("select[name='field_level']").select_option(label="Beginner")
        page.locator("select[name='field_language']").select_option(label="en")
        page.locator("select[name='field_price_type']").select_option("free")
        page.locator("input[name='field_price[0][value]']").fill("0")
        shot(page, "006-instructor-course-collection-after-fill-draft.png")

        page.get_by_role("button", name="Chapter & lesson").click()
        expect(page.locator("input[name='ief-field_chapters-form-add']")).to_be_visible(timeout=15000)
        shot(page, "007-instructor-curriculum-before-add.png")

        page.locator("input[name='ief-field_chapters-form-add']").click()
        expect(page.locator("input[name='field_chapters[form][0][name][0][value]']")).to_be_visible(timeout=20000)
        shot(page, "008-instructor-chapter-before-fill.png")

        page.locator("input[name='field_chapters[form][0][name][0][value]']").fill(chapter)
        shot(page, "009-instructor-chapter-after-fill-before-lesson.png")

        page.locator("input[name='ief-field_chapters-form-0-field_lessons-form-add']").click()
        expect(page.locator("input[name='field_chapters[form][0][field_lessons][form][0][name][0][value]']")).to_be_visible(timeout=20000)
        shot(page, "010-instructor-lesson-before-fill.png")

        page.locator("input[name='field_chapters[form][0][field_lessons][form][0][name][0][value]']").fill(lesson)
        page.locator("select[name='field_chapters[form][0][field_lessons][form][0][field_type]']").select_option("text")
        page.locator("input[name='field_chapters[form][0][field_lessons][form][0][field_duration][0][value]']").fill("12")
        shot(page, "011-instructor-lesson-after-fill.png")

        page.locator("input[name='ief-add-submit-field_chapters-form-0-field_lessons-form']").click()
        expect(page.locator("body")).to_contain_text(lesson, timeout=20000)
        shot(page, "012-instructor-chapter-after-lesson-created.png")

        page.locator("input[name='ief-add-submit-field_chapters-form']").click()
        expect(page.locator("body")).to_contain_text(chapter, timeout=20000)
        shot(page, "013-instructor-curriculum-after-chapter-created.png")

        page.locator("input[name='op'][value='Save']").click()
        page.wait_for_url("**/workspace/courses", timeout=30000)
        expect(page.locator("body")).to_contain_text(title, timeout=15000)
        expect(page.locator("body")).to_contain_text("Draft", timeout=15000)
        shot(page, "014-instructor-course-list-draft-created.png")

        row = page.locator("tr", has_text=title).first
        edit_href = row.locator("a[href*='/workspace/courses/'][href*='/edit']").first.get_attribute("href")
        if not edit_href:
            raise RuntimeError("Could not find the new course edit link")
        course_id_match = re.search(r"/workspace/courses/(\d+)/edit", edit_href)
        if not course_id_match:
            raise RuntimeError(f"Could not parse course id from {edit_href}")
        course_id = course_id_match.group(1)

        page.goto(f"{BASE_URL}/workspace/courses/{course_id}/edit", wait_until="domcontentloaded", timeout=60000)
        expect(page.locator("body")).to_contain_text(title, timeout=15000)
        shot(page, "015-instructor-course-edit-before-publish.png")

        page.get_by_role("button", name="Course Collection").click()
        page.locator("input[name='status[value]']").check(force=True)
        shot(page, "016-instructor-course-edit-after-publish-selected.png")

        page.locator("input[name='op'][value='Save']").click()
        page.wait_for_url("**/workspace/courses", timeout=30000)
        page.goto(f"{BASE_URL}/workspace/courses?search={title}", wait_until="domcontentloaded", timeout=60000)
        expect(page.locator("body")).to_contain_text(title, timeout=15000)
        expect(page.locator("body")).to_contain_text("Published", timeout=15000)
        shot(page, "017-instructor-course-list-published.png")

        anonymous_context = browser.new_context(
            viewport={"width": 1440, "height": 1200},
            ignore_https_errors=True,
        )
        public = anonymous_context.new_page()
        public.goto(f"{BASE_URL}/courses?search={title}", wait_until="domcontentloaded", timeout=60000)
        expect(public.locator("body")).to_contain_text(title, timeout=15000)
        shot(public, "018-anonymous-catalog-course-visible.png")

        public.goto(f"{BASE_URL}/courses/{course_id}", wait_until="domcontentloaded", timeout=60000)
        expect(public.locator("body")).to_contain_text(title, timeout=15000)
        public.get_by_text("Curriculum", exact=True).click()
        expect(public.locator("body")).to_contain_text(chapter, timeout=15000)
        expect(public.locator("body")).to_contain_text(lesson, timeout=15000)
        shot(public, "019-anonymous-course-detail-curriculum-visible.png")

        print(f"Captured WF-006 screenshots in {OUT}")
        print(f"Course ID: {course_id}")
        print(f"Course title: {title}")

        anonymous_context.close()
        instructor_context.close()
        browser.close()


if __name__ == "__main__":
    main()
