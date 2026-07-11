#!/usr/bin/env python3
"""WF-008 reply + best-answer + anonymous teaser (round-2)."""
import subprocess
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from lib import BASE_URL, SCREENS, VIEWPORT, Session, run, shot  # noqa: E402
from playwright.sync_api import sync_playwright  # noqa: E402

OUT = SCREENS / "008-forum-discussion"
TOPIC = 44
REPLIER_UID = 71  # julia.nowak


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
        page.locator("form textarea").first.fill(text)


def main(pw):
    # 1. Second student (julia) replies
    login = uli(REPLIER_UID)
    b = pw.chromium.launch(headless=True, args=["--no-sandbox", "--force-color-profile=srgb"])
    ctx = b.new_context(viewport=VIEWPORT, ignore_https_errors=True)
    page = ctx.new_page()
    page.goto(login, wait_until="domcontentloaded", timeout=60000)
    page.goto(f"{BASE_URL}/forum/{TOPIC}", wait_until="networkidle", timeout=60000)
    shot(page, OUT, "06-replier-topic-before-reply.png")
    fill_body(page, "Document time, location, witnesses, resident condition and vitals in the "
                    "Sturzprotokoll, inform the registered nurse immediately, then the doctor and family.")
    page.locator("input[type='submit'][value*='Reply'], input[type='submit'][value*='Post'], "
                 "button[type='submit']").first.click()
    page.wait_for_load_state("networkidle", timeout=45000)
    shot(page, OUT, "07-replier-after-reply.png")
    ctx.close()
    b.close()

    # 2. Topic owner (maria) marks best answer
    with Session(pw, "student") as page:
        page.goto(f"{BASE_URL}/forum/{TOPIC}", wait_until="networkidle", timeout=60000)
        best = page.locator("a:has-text('Best Answer'), a:has-text('best answer'), a:has-text('Mark as Best'), "
                            "button:has-text('Best Answer')").first
        print("  best-answer control found:", best.count())
        if best.count():
            best.click()
            page.wait_for_load_state("networkidle", timeout=30000)
        shot(page, OUT, "08-owner-marked-best-answer-resolved.png")

    # 3. Anonymous teaser + updated list
    with Session(pw, None) as page:
        page.goto(f"{BASE_URL}/forum/{TOPIC}", wait_until="networkidle", timeout=60000)
        shot(page, OUT, "09-anon-topic-teaser.png")
        page.goto(f"{BASE_URL}/forum", wait_until="networkidle", timeout=60000)
        body = page.locator("body").inner_text()
        print("  anon list shows topic:", "WF008-R2" in body)
        shot(page, OUT, "10-anon-forum-list-updated.png")


if __name__ == "__main__":
    run(main)
