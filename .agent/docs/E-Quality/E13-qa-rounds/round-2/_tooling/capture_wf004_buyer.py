#!/usr/bin/env python3
"""WF-004 buyer path — julia.nowak (uid 71, non-member) buys paid course 90
($45) via bank transfer and uploads a receipt (round-2)."""
import subprocess
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from lib import BASE_URL, SCREENS, VIEWPORT, shot  # noqa: E402
from playwright.sync_api import expect, sync_playwright  # noqa: E402

OUT = SCREENS / "004-order-checkout-and-payment"
UID = 71
COURSE = 90


def uli(uid):
    out = subprocess.check_output(
        ["ddev", "drush", "user:login", f"--uid={uid}", "--no-browser"],
        cwd="/home/hainn/projects/everlms", text=True)
    return out.strip().splitlines()[0].strip()


def main():
    login = uli(UID)
    OUT.mkdir(parents=True, exist_ok=True)
    with sync_playwright() as pw:
        b = pw.chromium.launch(headless=True, args=["--no-sandbox", "--force-color-profile=srgb"])
        ctx = b.new_context(viewport=VIEWPORT, ignore_https_errors=True)
        page = ctx.new_page()
        page.goto(login, wait_until="domcontentloaded", timeout=60000)

        page.goto(f"{BASE_URL}/checkout/{COURSE}", wait_until="domcontentloaded", timeout=60000)
        shot(page, OUT, "01-buyer-checkout-before.png")

        bt = page.locator("input[name='payment_method'][value='bank_transfer']")
        expect(bt).to_be_attached(timeout=15000)
        bt.check(force=True)
        shot(page, OUT, "02-buyer-bank-transfer-selected.png")

        page.locator("#submit-button").click()
        page.wait_for_url("**/checkout/bank-transfer/**", timeout=30000)
        order_id = page.url.rstrip("/").split("/")[-1]
        shot(page, OUT, "03-buyer-bank-transfer-instructions.png")

        rin = page.locator(".lms-receipt-upload input[type='file']").first
        expect(rin).to_be_attached(timeout=15000)
        shot(page, OUT, "04-buyer-receipt-before-upload.png")
        rin.set_input_files(str(OUT / "03-buyer-bank-transfer-instructions.png"))
        shot(page, OUT, "05-buyer-receipt-after-file-selected.png")

        page.locator("input[value='Submit Receipt'], button:has-text('Submit Receipt')").first.click()
        page.wait_for_load_state("domcontentloaded", timeout=30000)
        shot(page, OUT, "06-buyer-receipt-submitted.png")

        print(f"ORDER_ID={order_id}")
        print(f"final_url={page.url}")
        ctx.close()
        b.close()


if __name__ == "__main__":
    main()
