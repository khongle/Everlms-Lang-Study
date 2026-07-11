#!/usr/bin/env python3
"""WF-008 Forum discussion — anonymous list, student topic, second-student reply,
best-answer/resolved, anonymous teaser + updated list. Round-2."""
import subprocess
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from lib import BASE_URL, SCREENS, VIEWPORT, Session, run, shot  # noqa: E402
from playwright.sync_api import sync_playwright  # noqa: E402

OUT = SCREENS / "008-forum-discussion"
CATEGORY = "General Discussion"
TITLE = "WF008-R2: How do I document a resident fall correctly?"


def uli(uid):
    out = subprocess.check_output(["ddev", "drush", "user:login", f"--uid={uid}", "--no-browser"],
                                  cwd="/home/hainn/projects/everlms", text=True)
    return out.strip().splitlines()[0].strip()


def fill_body(page, text):
    ed = page.locator(".ck-editor__editable").first
    if ed.count():
        ed.click()
        page.keyboard.type(text)
    else:
        ta = page.locator("textarea").first
        ta.fill(text)


def main(pw):
    # 1. Anonymous forum list
    with Session(pw, None) as page:
        page.goto(f"{BASE_URL}/forum", wait_until="networkidle", timeout=60000)
        shot(page, OUT, "01-anon-forum-list-before.png")

    # 2. Student maria creates a topic
    topic_url = None
    with Session(pw, "student") as page:
        page.goto(f"{BASE_URL}/forum", wait_until="networkidle", timeout=60000)
        shot(page, OUT, "02-student-forum-list-logged-in.png")
        page.goto(f"{BASE_URL}/forum/new", wait_until="networkidle", timeout=60000)
        shot(page, OUT, "03-student-new-topic-before.png")
        page.locator("input[name='title[0][value]'], input#edit-title-0-value, form input[type='text']").first.fill(TITLE)
        cat = page.locator("select[name='field_category']")
        if cat.count():
            cat.select_option(label=CATEGORY)
        fill_body(page, "A resident slipped near the bathroom. What exactly must go into the "
                        "Sturzprotokoll and who do I notify first? Thanks!")
        shot(page, OUT, "04-student-new-topic-after-fill.png")
        page.locator("input[type='submit'][value*='Save'], input[type='submit'][value*='Post'], "
                     "button[type='submit']").first.click()
        page.wait_for_load_state("networkidle", timeout=45000)
        topic_url = page.url
        print("TOPIC_URL=", topic_url)
        shot(page, OUT, "05-student-topic-created.png")

    print("done topic; url:", topic_url)


if __name__ == "__main__":
    run(main)
