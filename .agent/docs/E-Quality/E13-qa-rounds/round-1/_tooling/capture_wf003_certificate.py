#!/usr/bin/env python3
"""Capture WF-003 certificate issuance with a real student session."""

from pathlib import Path
import os
import subprocess

from playwright.sync_api import expect, sync_playwright


ROOT = Path("/home/hainn/projects/everlms")
BASE_URL = "https://everlms.ddev.site"
STATE = ROOT / ".agent/docs/E00-UI-test/_tooling/state/student.json"
OUT = ROOT / ".agent/docs/E-Quality/E00-workflows/round-1/_screens/003-certificate-issuance"
QUIZ_ID = 81


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


def check(page, name: str, value: str) -> None:
    locator = page.locator(f"input[name='{name}'][value='{value}']")
    expect(locator).to_be_attached(timeout=15000)
    locator.check(force=True)


def absolute_url(href: str) -> str:
    if href.startswith("http"):
        return href
    if href.startswith("/"):
        return BASE_URL + href
    return BASE_URL + "/" + href


def capture_certificate_delivery(browser, context, page) -> None:
    page.goto(f"{BASE_URL}/my-certificates", wait_until="domcontentloaded", timeout=60000)
    expect(page.locator("body")).to_contain_text("Emotional and Spiritual Support for the Dying", timeout=15000)
    expect(page.locator(".lms-certificate-card__button").first).to_be_visible(timeout=15000)
    shot(page, "005-student-certificates-after-issue.png")

    certificate_code = page.locator(".lms-certificate-card__meta-value--mono").first.inner_text().strip()

    with page.expect_download(timeout=60000) as download_info:
        page.locator(".lms-certificate-card__button").first.click()
    download = download_info.value
    pdf_path = OUT / f"certificate-{certificate_code}.pdf"
    download.save_as(str(pdf_path))

    output_prefix = OUT / "006-student-certificate-pdf-opened"
    subprocess.run(
        ["pdftoppm", "-png", "-singlefile", "-r", "160", str(pdf_path), str(output_prefix)],
        check=True,
    )

    public_context = browser.new_context(
        viewport={"width": 1440, "height": 1200},
        ignore_https_errors=True,
    )
    public_page = public_context.new_page()
    public_page.goto(f"{BASE_URL}/certificate/verify", wait_until="domcontentloaded", timeout=60000)
    expect(public_page.locator("input[name='certificate_code']")).to_be_visible(timeout=15000)
    shot(public_page, "007-public-verify-before-fill.png")

    public_page.locator("input[name='certificate_code']").fill(certificate_code)
    shot(public_page, "008-public-verify-after-fill.png")

    public_page.locator("input[value='Verify']").click()
    expect(public_page.locator("body")).to_contain_text("VALID CERTIFICATE", timeout=15000)
    expect(public_page.locator("body")).to_contain_text(certificate_code, timeout=15000)
    shot(public_page, "009-public-verify-valid-result.png")

    print(f"Certificate code: {certificate_code}")
    print(f"Final URL: {public_page.url}")

    public_context.close()


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)

    with sync_playwright() as p:
        browser = p.chromium.launch(
            headless=True,
            args=["--no-sandbox", "--force-color-profile=srgb"],
        )
        context = browser.new_context(
            viewport={"width": 1440, "height": 1200},
            ignore_https_errors=True,
            storage_state=str(STATE),
            accept_downloads=True,
        )
        context.add_init_script(
            """
            localStorage.removeItem('lms_quiz_draft_/quiz/81/take');
            localStorage.removeItem('lms_quiz_end_time_/quiz/81/take');
            """
        )
        page = context.new_page()

        if os.environ.get("WF003_DELIVERY_ONLY"):
            capture_certificate_delivery(browser, context, page)
            print(f"Recaptured WF-003 delivery screenshots in {OUT}")
            context.close()
            browser.close()
            return

        page.goto(f"{BASE_URL}/my-certificates", wait_until="domcontentloaded", timeout=60000)
        expect(page.locator("body")).to_contain_text("My Certificates", timeout=15000)
        shot(page, "001-student-certificates-before-pass.png")

        page.goto(f"{BASE_URL}/quiz/{QUIZ_ID}/take", wait_until="domcontentloaded", timeout=60000)
        expect(page.locator("#lms-quiz-actual-form")).to_be_visible(timeout=15000)
        expect(page.locator("body")).to_contain_text("Final Assessment: Emotional Support", timeout=15000)
        shot(page, "002-student-final-quiz-before-answer.png")

        check(page, "q_331", "803")
        check(page, "q_332", "0")
        for answer_id in ["807", "808"]:
            check(page, "q_333[]", answer_id)
        shot(page, "003-student-final-quiz-after-fill.png")

        page.locator("#btn-trigger-modal").click()
        expect(page.locator("#lms-submit-modal.active")).to_be_visible(timeout=15000)
        page.locator("#btn-modal-confirm").click()
        page.wait_for_url("**/quiz-result/**", timeout=30000)
        expect(page.locator("body")).to_contain_text("Passed", timeout=15000)
        shot(page, "004-student-quiz-passed-result.png")

        capture_certificate_delivery(browser, context, page)

        print(f"Captured WF-003 screenshots in {OUT}")

        context.close()
        browser.close()


if __name__ == "__main__":
    main()
