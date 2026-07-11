#!/usr/bin/env python3
"""Capture WF-009 coupon redemption with a real student session."""

from pathlib import Path

from playwright.sync_api import expect, sync_playwright


ROOT = Path("/home/hainn/projects/everlms")
BASE_URL = "https://everlms.ddev.site"
STATE = ROOT / ".agent/docs/E00-UI-test/_tooling/state/student.json"
OUT = ROOT / ".agent/docs/E-Quality/E00-workflows/round-1/_screens/009-coupon-discount-redemption"
COURSE_ID = 127
COUPON = "SILVERCARE10"


def settle(page) -> None:
    try:
        page.wait_for_load_state("networkidle", timeout=15000)
    except Exception:
        pass
    try:
        page.evaluate("document.fonts && document.fonts.ready")
    except Exception:
        pass
    page.wait_for_timeout(350)


def shot(page, name: str) -> None:
    settle(page)
    page.screenshot(path=str(OUT / name), full_page=True, animations="disabled")


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)

    with sync_playwright() as p:
        browser = p.chromium.launch(
            headless=True,
            args=["--no-sandbox", "--force-color-profile=srgb"],
        )
        context = browser.new_context(
            viewport={"width": 1440, "height": 1100},
            ignore_https_errors=True,
            storage_state=str(STATE),
        )
        page = context.new_page()

        page.goto(f"{BASE_URL}/checkout/{COURSE_ID}", wait_until="domcontentloaded", timeout=60000)
        expect(page.locator("body")).not_to_contain_text("Access denied", timeout=10000)
        expect(page.locator("body")).not_to_contain_text("Log in", timeout=10000)
        expect(page.locator("input[name='coupon_code']")).to_be_visible(timeout=15000)
        shot(page, "001-student-checkout-before-coupon.png")

        page.locator("input[name='coupon_code']").fill(COUPON)
        shot(page, "002-student-coupon-after-fill.png")

        page.locator("input[name='apply_coupon']").click()
        page.wait_for_url(f"**/checkout/{COURSE_ID}?coupon={COUPON}", timeout=30000)
        expect(page.locator("body")).to_contain_text("Discount", timeout=15000)
        expect(page.locator("body")).to_contain_text("$45.00", timeout=15000)
        shot(page, "003-student-coupon-applied.png")

        bank_transfer = page.locator("input[name='payment_method'][value='bank_transfer']")
        expect(bank_transfer).to_be_attached(timeout=15000)
        bank_transfer.check(force=True)
        shot(page, "004-student-bank-transfer-selected.png")

        page.locator("#submit-button").click()
        page.wait_for_url("**/checkout/bank-transfer/**", timeout=30000)
        expect(page.locator("body")).to_contain_text("45.00", timeout=15000)
        shot(page, "005-student-bank-transfer-instructions.png")

        receipt = OUT / "005-student-bank-transfer-instructions.png"
        receipt_input = page.locator(".lms-receipt-upload input[type='file']").first
        expect(receipt_input).to_be_attached(timeout=15000)
        receipt_input.set_input_files(str(receipt))
        shot(page, "006-student-receipt-after-file-selected.png")

        page.locator("input[value='Submit Receipt']").click()
        page.wait_for_url(BASE_URL + "/", timeout=30000)
        expect(page.locator("body")).to_contain_text("Payment receipt uploaded successfully", timeout=15000)
        shot(page, "007-student-receipt-submitted-confirmation.png")

        print(f"Captured WF-009 screenshots in {OUT}")
        print(f"Final URL: {page.url}")

        context.close()
        browser.close()


if __name__ == "__main__":
    main()
