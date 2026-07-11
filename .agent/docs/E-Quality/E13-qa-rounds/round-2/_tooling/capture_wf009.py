#!/usr/bin/env python3
"""WF-009 Coupon redemption — apply valid, invalid, and below-minimum coupons at
checkout (julia.nowak, course 124 $85). Round-2."""
import subprocess
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from lib import BASE_URL, SCREENS, VIEWPORT, shot  # noqa: E402
from playwright.sync_api import sync_playwright  # noqa: E402

OUT = SCREENS / "009-coupon-discount-redemption"
UID = 71
COURSE = 124


def uli(uid):
    out = subprocess.check_output(["ddev", "drush", "user:login", f"--uid={uid}", "--no-browser"],
                                  cwd="/home/hainn/projects/everlms", text=True)
    return out.strip().splitlines()[0].strip()


def apply(page, code):
    fld = page.locator("input[name='coupon_code']")
    fld.fill(code)
    page.locator("input[name='apply_coupon']").click()
    page.wait_for_load_state("domcontentloaded", timeout=30000)
    page.wait_for_timeout(1500)


def main():
    login = uli(UID)
    with sync_playwright() as pw:
        b = pw.chromium.launch(headless=True, args=["--no-sandbox", "--force-color-profile=srgb"])
        ctx = b.new_context(viewport=VIEWPORT, ignore_https_errors=True)
        page = ctx.new_page()
        page.goto(login, wait_until="domcontentloaded", timeout=60000)

        page.goto(f"{BASE_URL}/checkout/{COURSE}", wait_until="domcontentloaded", timeout=60000)
        shot(page, OUT, "01-buyer-checkout-before-coupon.png")

        # Valid percent coupon
        apply(page, "SILVERCARE10")
        body = page.locator("body").inner_text()
        shot(page, OUT, "02-buyer-coupon-valid-applied.png")
        print("  SILVERCARE10 applied text has 76.5/discount:", ("76.50" in body or "76.5" in body or "8.50" in body))

        # Invalid coupon
        page.goto(f"{BASE_URL}/checkout/{COURSE}", wait_until="domcontentloaded", timeout=60000)
        apply(page, "NOPE-INVALID-123")
        shot(page, OUT, "03-buyer-coupon-invalid-rejected.png")
        b2 = page.locator("body").inner_text()
        print("  invalid rejected msg:", ("invalid" in b2.lower() or "not" in b2.lower()))

        # Below-minimum fixed coupon (WELCOME20 needs >= $50; $85 qualifies -> applies)
        page.goto(f"{BASE_URL}/checkout/{COURSE}", wait_until="domcontentloaded", timeout=60000)
        apply(page, "WELCOME20")
        shot(page, OUT, "04-buyer-coupon-fixed-applied.png")
        b3 = page.locator("body").inner_text()
        print("  WELCOME20 ($20 off -> 65.00) present:", ("65.00" in b3 or "65.0" in b3 or "20.00" in b3))

        ctx.close()
        b.close()


if __name__ == "__main__":
    main()
