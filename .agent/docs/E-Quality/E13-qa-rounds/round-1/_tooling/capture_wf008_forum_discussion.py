#!/usr/bin/env python3
"""Capture WF-008 forum topic, reply, and best-answer workflow."""

import os
import re
from datetime import datetime
from pathlib import Path

from playwright.sync_api import expect, sync_playwright


ROOT = Path("/home/hainn/projects/everlms")
BASE_URL = "https://everlms.ddev.site"
OUT = ROOT / ".agent/docs/E-Quality/E00-workflows/round-1/_screens/008-forum-discussion"

AUTHOR_USER = "maria.santos"
REPLIER_USER = "ana.kovacs"
PASSWORD = os.environ.get("EVERLMS_STUDENT_PASSWORD", "ChangeMe@123")


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


def login(page, username: str, before_name: str, after_name: str) -> None:
    page.goto(f"{BASE_URL}/user/login", wait_until="domcontentloaded", timeout=60000)
    expect(page.locator("input[name='name']")).to_be_visible(timeout=15000)
    shot(page, before_name)
    page.locator("input[name='name']").fill(username)
    page.locator("input[name='pass']").fill(PASSWORD)
    shot(page, after_name)
    page.locator("input[type='submit']").click()
    page.wait_for_url(re.compile(r".*/user/\d+.*"), timeout=30000)
    expect(page.locator("body")).to_contain_text(username.split(".")[0].capitalize(), timeout=15000)


def fill_text_body(page, value: str, editor_index: int = 0) -> None:
    textarea = page.locator("textarea[name='field_body[0][value]']")
    if textarea.count() and textarea.first.is_visible():
        textarea.first.fill(value)
        return

    editors = page.locator(".ck-editor__editable")
    if editors.count() > editor_index:
        editors.nth(editor_index).click()
        editors.nth(editor_index).fill(value)
        return

    raise RuntimeError("Could not find a forum body editor")


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)

    stamp = datetime.now().strftime("%Y%m%d-%H%M%S")
    title = f"WF008 Safe transfer discussion {stamp}"
    topic_body = (
        "What checks should we run before a two-person wheelchair transfer "
        "when the resident is anxious?"
    )
    reply_body = (
        "I confirm brakes, footrests, transfer belt fit, and explain each step "
        "before moving so the resident can anticipate the transfer."
    )

    with sync_playwright() as p:
        browser = p.chromium.launch(
            headless=True,
            args=["--no-sandbox", "--force-color-profile=srgb"],
        )

        anonymous_context = browser.new_context(
            viewport={"width": 1440, "height": 1100},
            ignore_https_errors=True,
        )
        anon = anonymous_context.new_page()
        anon.goto(f"{BASE_URL}/forum", wait_until="domcontentloaded", timeout=60000)
        expect(anon.locator("body")).to_contain_text("Community", timeout=15000)
        shot(anon, "001-anonymous-forum-index-before-login.png")

        author_context = browser.new_context(
            viewport={"width": 1440, "height": 1100},
            ignore_https_errors=True,
        )
        author = author_context.new_page()
        login(
            author,
            AUTHOR_USER,
            "002-student-author-login-before-fill.png",
            "003-student-author-login-after-fill.png",
        )

        author.goto(f"{BASE_URL}/forum", wait_until="domcontentloaded", timeout=60000)
        expect(author.locator("body")).to_contain_text("Community", timeout=15000)
        expect(author.locator("body")).to_contain_text("New Topic", timeout=15000)
        shot(author, "004-student-author-forum-index-before-topic.png")

        author.goto(f"{BASE_URL}/forum/new", wait_until="domcontentloaded", timeout=60000)
        expect(author.locator("input[name='name[0][value]']")).to_be_visible(timeout=15000)
        shot(author, "005-student-author-new-topic-before-fill.png")

        author.locator("input[name='name[0][value]']").fill(title)
        fill_text_body(author, topic_body)
        category = author.locator("select[name='field_category']")
        if category.count():
            category.select_option(label="Course Questions")
        shot(author, "006-student-author-new-topic-after-fill.png")

        author.locator("input[name='op']").click()
        author.wait_for_url(re.compile(r".*/forum/\d+.*"), timeout=30000)
        expect(author.locator("body")).to_contain_text(title, timeout=15000)
        expect(author.locator("body")).not_to_contain_text("FRESH CONTROLLER LOADED", timeout=5000)
        topic_url = author.url
        topic_id_match = re.search(r"/forum/(\d+)", topic_url)
        if not topic_id_match:
            raise RuntimeError(f"Could not parse topic id from {topic_url}")
        topic_id = topic_id_match.group(1)
        shot(author, "007-student-author-topic-created-detail.png")

        replier_context = browser.new_context(
            viewport={"width": 1440, "height": 1100},
            ignore_https_errors=True,
        )
        replier = replier_context.new_page()
        login(
            replier,
            REPLIER_USER,
            "008-student-replier-login-before-fill.png",
            "009-student-replier-login-after-fill.png",
        )

        replier.goto(topic_url, wait_until="domcontentloaded", timeout=60000)
        expect(replier.locator("body")).to_contain_text(title, timeout=15000)
        expect(replier.locator("input[name='op']")).to_be_visible(timeout=15000)
        shot(replier, "010-student-replier-topic-detail-before-reply.png")

        fill_text_body(replier, reply_body)
        shot(replier, "011-student-replier-reply-after-fill.png")

        replier.locator("input[name='op']").click()
        replier.wait_for_url(re.compile(r".*/forum/\d+.*"), timeout=30000)
        expect(replier.locator("body")).to_contain_text(reply_body, timeout=15000)
        shot(replier, "012-student-replier-topic-detail-after-reply.png")

        author.goto(topic_url, wait_until="domcontentloaded", timeout=60000)
        expect(author.locator("body")).to_contain_text(reply_body, timeout=15000)
        best_link = author.locator("a[title='Mark as Best Answer']").first
        expect(best_link).to_be_visible(timeout=15000)
        shot(author, "013-student-author-topic-detail-before-best-answer.png")

        best_link.click()
        author.wait_for_url(re.compile(r".*/forum/\d+.*"), timeout=30000)
        expect(author.locator("body")).to_contain_text("Best Answer", timeout=15000)
        expect(author.locator("body")).to_contain_text("Resolved", timeout=15000)
        shot(author, "014-student-author-topic-detail-after-best-answer.png")

        anon.goto(topic_url, wait_until="domcontentloaded", timeout=60000)
        expect(anon.locator("body")).to_contain_text(title, timeout=15000)
        expect(anon.locator("body")).to_contain_text("Join the Discussion", timeout=15000)
        shot(anon, "015-anonymous-topic-detail-teaser-after-reply.png")

        anon.goto(f"{BASE_URL}/forum", wait_until="domcontentloaded", timeout=60000)
        expect(anon.locator("body")).to_contain_text(title, timeout=15000)
        expect(anon.locator("body")).to_contain_text("Replies", timeout=15000)
        shot(anon, "016-anonymous-forum-index-resolved-topic-visible.png")

        print(f"Captured WF-008 screenshots in {OUT}")
        print(f"Topic ID: {topic_id}")
        print(f"Topic title: {title}")

        replier_context.close()
        author_context.close()
        anonymous_context.close()
        browser.close()


if __name__ == "__main__":
    main()
