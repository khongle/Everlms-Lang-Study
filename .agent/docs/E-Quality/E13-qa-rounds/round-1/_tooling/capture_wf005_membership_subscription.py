#!/usr/bin/env python3
"""Capture WF-005 membership subscription with a real clean student."""

import subprocess
from pathlib import Path

from playwright.sync_api import expect, sync_playwright


ROOT = Path("/home/hainn/projects/everlms")
BASE_URL = "https://everlms.ddev.site"
MANAGER_STATE = ROOT / ".agent/docs/E00-UI-test/_tooling/state/manager.json"
OUT = ROOT / ".agent/docs/E-Quality/E00-workflows/round-1/_screens/005-membership-subscription"
STUDENT_UID = 67
PLAN_ID = 8
PLAN_TITLE = "Professional Plan"
PLAN_PRICE = "$29.00"
COURSE_ID = 89
COURSE_TITLE = "Advanced Dementia Care and Communication"


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


def one_time_login_url(uid: int) -> str:
    output = subprocess.check_output(
        ["ddev", "drush", "user:login", f"--uid={uid}", "--no-browser"],
        cwd=str(ROOT),
        text=True,
    )
    return output.strip().splitlines()[0].strip()


def login_student(browser):
    context = browser.new_context(
        viewport={"width": 1440, "height": 1200},
        ignore_https_errors=True,
    )
    page = context.new_page()
    page.goto(one_time_login_url(STUDENT_UID), wait_until="domcontentloaded", timeout=60000)
    page.goto(f"{BASE_URL}/user", wait_until="domcontentloaded", timeout=60000)
    expect(page.locator("body")).not_to_contain_text("Log in", timeout=15000)
    return context, page


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)

    with sync_playwright() as p:
        browser = p.chromium.launch(
            headless=True,
            args=["--no-sandbox", "--force-color-profile=srgb"],
        )

        student_context, student = login_student(browser)

        student.goto(f"{BASE_URL}/my-membership", wait_until="domcontentloaded", timeout=60000)
        expect(student.locator("body")).to_contain_text("No Active Plans", timeout=15000)
        shot(student, "001-student-my-membership-before-subscription.png")

        student.goto(f"{BASE_URL}/courses/{COURSE_ID}", wait_until="domcontentloaded", timeout=60000)
        expect(student.locator("body")).to_contain_text(COURSE_TITLE, timeout=15000)
        expect(student.locator("body")).to_contain_text("Enroll Now", timeout=15000)
        shot(student, "002-student-paid-course-before-membership.png")

        student.goto(f"{BASE_URL}/pricing", wait_until="domcontentloaded", timeout=60000)
        expect(student.locator("body")).to_contain_text(PLAN_TITLE, timeout=15000)
        shot(student, "003-student-pricing-plans.png")

        student.goto(f"{BASE_URL}/checkout/membership/{PLAN_ID}", wait_until="domcontentloaded", timeout=60000)
        expect(student.locator("body")).to_contain_text(PLAN_TITLE, timeout=15000)
        expect(student.locator("body")).to_contain_text(PLAN_PRICE, timeout=15000)
        shot(student, "004-student-membership-checkout-before-payment.png")

        bank_transfer = student.locator("input[name='payment_method'][value='bank_transfer']")
        expect(bank_transfer).to_be_attached(timeout=15000)
        bank_transfer.check(force=True)
        shot(student, "005-student-membership-checkout-bank-transfer-selected.png")

        student.locator("#submit-button").click()
        student.wait_for_url("**/checkout/bank-transfer/**", timeout=30000)
        order_id = student.url.rstrip("/").split("/")[-1]
        expect(student.locator("body")).to_contain_text("29.00", timeout=15000)
        shot(student, "006-student-membership-bank-transfer-instructions.png")

        receipt = OUT / "006-student-membership-bank-transfer-instructions.png"
        receipt_input = student.locator(".lms-receipt-upload input[type='file']").first
        expect(receipt_input).to_be_attached(timeout=15000)
        shot(student, "007-student-membership-receipt-upload-before-fill.png")
        receipt_input.set_input_files(str(receipt))
        shot(student, "008-student-membership-receipt-after-file-selected.png")

        student.locator("input[value='Submit Receipt']").click()
        student.wait_for_url(BASE_URL + "/", timeout=30000)
        expect(student.locator("body")).to_contain_text("Payment receipt uploaded successfully", timeout=15000)
        shot(student, "009-student-membership-receipt-submitted-confirmation.png")

        manager_context = browser.new_context(
            viewport={"width": 1440, "height": 1200},
            ignore_https_errors=True,
            storage_state=str(MANAGER_STATE),
        )
        manager = manager_context.new_page()

        manager.goto(f"{BASE_URL}/workspace/orders?search={order_id}&status=processing", wait_until="domcontentloaded", timeout=60000)
        expect(manager.locator("body")).to_contain_text(f"#{order_id}", timeout=15000)
        expect(manager.locator("body")).to_contain_text("Processing", timeout=15000)
        shot(manager, "010-manager-membership-order-processing-before-approval.png")

        manager.locator(f"a[href*='/workspace/orders/{order_id}/edit']").first.click()
        manager.wait_for_url(f"**/workspace/orders/{order_id}/edit**", timeout=30000)
        expect(manager.locator("body")).to_contain_text(f"Review & Approve Order: #{order_id}", timeout=15000)
        expect(manager.locator("body")).to_contain_text(f"{PLAN_TITLE} (Membership)", timeout=15000)
        shot(manager, "011-manager-membership-order-review-before-status-change.png")

        manager.locator("select[name='field_status']").select_option("completed")
        shot(manager, "012-manager-membership-order-review-after-completed-selected.png")

        manager.locator("input[value='Update Order']").click()
        manager.wait_for_url("**/workspace/orders", timeout=30000)
        expect(manager.locator("body")).to_contain_text("status has been updated", timeout=15000)
        shot(manager, "013-manager-membership-orders-after-approval.png")

        student.goto(f"{BASE_URL}/my-membership", wait_until="domcontentloaded", timeout=60000)
        expect(student.locator("body")).to_contain_text(PLAN_TITLE, timeout=15000)
        expect(student.locator("body")).to_contain_text("active", timeout=15000)
        shot(student, "014-student-my-membership-after-activation.png")

        student.goto(f"{BASE_URL}/courses/{COURSE_ID}", wait_until="domcontentloaded", timeout=60000)
        expect(student.locator("body")).to_contain_text(COURSE_TITLE, timeout=15000)
        expect(student.locator("body")).to_contain_text("Unlocked", timeout=15000)
        expect(student.locator("body")).to_contain_text("Continue Learning", timeout=15000)
        shot(student, "015-student-paid-course-unlocked-by-membership.png")

        print(f"Captured WF-005 screenshots in {OUT}")
        print(f"Order ID: {order_id}")
        print(f"Student UID: {STUDENT_UID}")
        print(f"Plan ID: {PLAN_ID}")

        manager_context.close()
        student_context.close()
        browser.close()


if __name__ == "__main__":
    main()
