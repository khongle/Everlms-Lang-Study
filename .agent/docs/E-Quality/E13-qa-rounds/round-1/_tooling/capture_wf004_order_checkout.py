#!/usr/bin/env python3
"""Capture WF-004 order checkout and payment approval with real sessions."""

from pathlib import Path

from playwright.sync_api import expect, sync_playwright


ROOT = Path("/home/hainn/projects/everlms")
BASE_URL = "https://everlms.ddev.site"
STUDENT_STATE = ROOT / ".agent/docs/E00-UI-test/_tooling/state/student.json"
MANAGER_STATE = ROOT / ".agent/docs/E00-UI-test/_tooling/state/manager.json"
OUT = ROOT / ".agent/docs/E-Quality/E00-workflows/round-1/_screens/004-order-checkout-and-payment"
COURSE_ID = 93
COURSE_TITLE = "Infection Control and Prevention"


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
        student_context = browser.new_context(
            viewport={"width": 1440, "height": 1200},
            ignore_https_errors=True,
            storage_state=str(STUDENT_STATE),
        )
        student = student_context.new_page()

        student.goto(f"{BASE_URL}/checkout/{COURSE_ID}", wait_until="domcontentloaded", timeout=60000)
        expect(student.locator("body")).to_contain_text(COURSE_TITLE, timeout=15000)
        expect(student.locator("body")).to_contain_text("$80.00", timeout=15000)
        shot(student, "001-student-checkout-review.png")

        bank_transfer = student.locator("input[name='payment_method'][value='bank_transfer']")
        expect(bank_transfer).to_be_attached(timeout=15000)
        bank_transfer.check(force=True)
        shot(student, "002-student-bank-transfer-selected.png")

        student.locator("#submit-button").click()
        student.wait_for_url("**/checkout/bank-transfer/**", timeout=30000)
        order_id = student.url.rstrip("/").split("/")[-1]
        expect(student.locator("body")).to_contain_text("80.00", timeout=15000)
        shot(student, "003-student-bank-transfer-instructions-before-receipt.png")

        receipt = OUT / "003-student-bank-transfer-instructions-before-receipt.png"
        receipt_input = student.locator(".lms-receipt-upload input[type='file']").first
        expect(receipt_input).to_be_attached(timeout=15000)
        shot(student, "004-student-receipt-upload-before-fill.png")
        receipt_input.set_input_files(str(receipt))
        shot(student, "005-student-receipt-after-file-selected.png")

        student.locator("input[value='Submit Receipt']").click()
        student.wait_for_url(BASE_URL + "/", timeout=30000)
        expect(student.locator("body")).to_contain_text("Payment receipt uploaded successfully", timeout=15000)
        shot(student, "006-student-receipt-submitted-confirmation.png")

        manager_context = browser.new_context(
            viewport={"width": 1440, "height": 1200},
            ignore_https_errors=True,
            storage_state=str(MANAGER_STATE),
        )
        manager = manager_context.new_page()

        manager.goto(f"{BASE_URL}/workspace/orders?search={order_id}&status=processing", wait_until="domcontentloaded", timeout=60000)
        expect(manager.locator("body")).to_contain_text(f"#{order_id}", timeout=15000)
        expect(manager.locator("body")).to_contain_text("Processing", timeout=15000)
        shot(manager, "007-manager-orders-processing-before-approval.png")

        manager.locator(f"a[href*='/workspace/orders/{order_id}/edit']").first.click()
        manager.wait_for_url(f"**/workspace/orders/{order_id}/edit**", timeout=30000)
        expect(manager.locator("select[name='field_status']")).to_be_visible(timeout=15000)
        shot(manager, "008-manager-order-review-before-status-change.png")

        manager.locator("select[name='field_status']").select_option("completed")
        shot(manager, "009-manager-order-review-after-completed-selected.png")

        manager.locator("input[value='Update Order']").click()
        manager.wait_for_url("**/workspace/orders", timeout=30000)
        expect(manager.locator("body")).to_contain_text("status has been updated", timeout=15000)
        shot(manager, "010-manager-orders-after-approval.png")

        student.goto(f"{BASE_URL}/my-courses", wait_until="domcontentloaded", timeout=60000)
        expect(student.locator("body")).to_contain_text(COURSE_TITLE, timeout=15000)
        shot(student, "011-student-my-courses-after-manager-approval.png")

        print(f"Captured WF-004 screenshots in {OUT}")
        print(f"Order ID: {order_id}")
        print(f"Final URL: {student.url}")

        manager_context.close()
        student_context.close()
        browser.close()


if __name__ == "__main__":
    main()
