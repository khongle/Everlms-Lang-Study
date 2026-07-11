#!/usr/bin/env python3
"""Capture WF-007 registration and onboarding with real web/API flows."""

import html
import json
import re
import subprocess
from datetime import datetime
from pathlib import Path

from playwright.sync_api import expect, sync_playwright


ROOT = Path("/home/hainn/projects/everlms")
BASE_URL = "https://everlms.ddev.site"
OUT = ROOT / ".agent/docs/E-Quality/E00-workflows/round-1/_screens/007-user-registration-and-onboarding"


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


def render_api_page(page, title: str, blocks: list[tuple[str, object]]) -> None:
    block_html = []
    for heading, data in blocks:
        if isinstance(data, str):
            body = data
        else:
            body = json.dumps(data, indent=2, sort_keys=True)
        block_html.append(
            f"""
            <section class="block">
              <h2>{html.escape(heading)}</h2>
              <pre>{html.escape(body)}</pre>
            </section>
            """
        )

    page.set_content(
        f"""
        <!doctype html>
        <html>
        <head>
          <meta charset="utf-8">
          <title>{html.escape(title)}</title>
          <style>
            :root {{
              color-scheme: light;
              font-family: Inter, Arial, sans-serif;
              background: #f6f7fb;
              color: #172033;
            }}
            body {{
              margin: 0;
              padding: 48px;
            }}
            main {{
              max-width: 1080px;
              margin: 0 auto;
            }}
            h1 {{
              font-size: 28px;
              margin: 0 0 24px;
            }}
            .block {{
              background: #fff;
              border: 1px solid #d9dee8;
              border-radius: 8px;
              margin: 0 0 18px;
              padding: 20px;
              box-shadow: 0 8px 24px rgba(23, 32, 51, 0.06);
            }}
            h2 {{
              font-size: 16px;
              margin: 0 0 12px;
            }}
            pre {{
              margin: 0;
              white-space: pre-wrap;
              word-break: break-word;
              font: 14px/1.5 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            }}
          </style>
        </head>
        <body>
          <main>
            <h1>{html.escape(title)}</h1>
            {''.join(block_html)}
          </main>
        </body>
        </html>
        """,
        wait_until="domcontentloaded",
    )


def redact_token(value: str) -> str:
    if not value:
        return ""
    return f"{value[:8]}...{value[-6:]}"


def redact_response(data: dict) -> dict:
    redacted = dict(data)
    for key in ("otp_token", "access_token", "refresh_token"):
        if key in redacted:
            redacted[key] = redact_token(str(redacted[key]))
    return redacted


def post_json(api, path: str, payload: dict) -> tuple[int, dict]:
    response = api.post(path, data=json.dumps(payload), headers={"Content-Type": "application/json"})
    try:
        body = response.json()
    except Exception:
        body = {"raw": response.text()}
    return response.status, body


def drush_eval(code: str) -> str:
    return subprocess.check_output(
        ["ddev", "drush", "php:eval", code],
        cwd=str(ROOT),
        text=True,
    ).strip()


def get_otp_code(otp_token: str) -> str:
    code = (
        "$s=\\Drupal::service('keyvalue.expirable')->get('lms_mobile_api.otp')->get('"
        + otp_token
        + "'); echo $s['otp_code'] ?? '';"
    )
    return drush_eval(code)


def get_user_summary(email: str) -> dict:
    code = (
        "$u=user_load_by_mail('"
        + email
        + "'); if (!$u) { echo '{}'; } else { echo json_encode(["
        + "'uid'=>(int)$u->id(),"
        + "'name'=>$u->getAccountName(),"
        + "'mail'=>$u->getEmail(),"
        + "'status'=>(int)$u->isActive(),"
        + "'roles'=>array_values($u->getRoles())"
        + "]); }"
    )
    return json.loads(drush_eval(code) or "{}")


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)

    stamp = datetime.now().strftime("%Y%m%d%H%M%S")
    web_name = f"wf007web{stamp}"
    web_email = f"{web_name}@example.test"
    mobile_name = f"wf007mobile{stamp}"
    mobile_email = f"{mobile_name}@example.test"
    mobile_password = "WF007Mobile!234"

    with sync_playwright() as p:
        browser = p.chromium.launch(
            headless=True,
            args=["--no-sandbox", "--force-color-profile=srgb"],
        )

        web_context = browser.new_context(
            viewport={"width": 1440, "height": 1100},
            ignore_https_errors=True,
        )
        page = web_context.new_page()
        page.goto(f"{BASE_URL}/user/register", wait_until="domcontentloaded", timeout=60000)
        expect(page.locator("input[name='mail']")).to_be_visible(timeout=15000)
        shot(page, "001-visitor-web-register-before-fill.png")

        page.locator("input[name='mail']").fill(web_email)
        page.locator("input[name='name']").fill(web_name)
        shot(page, "002-visitor-web-register-after-fill.png")

        page.locator("input[type='submit']").click()
        page.wait_for_load_state("domcontentloaded", timeout=30000)
        expect(page.locator("body")).to_contain_text("email", timeout=15000)
        shot(page, "003-visitor-web-register-submitted-confirmation.png")

        api = p.request.new_context(
            base_url=BASE_URL,
            ignore_https_errors=True,
            extra_http_headers={"Accept": "application/json"},
        )
        api_page = web_context.new_page()

        mobile_register_payload = {
            "name": mobile_name,
            "email": mobile_email,
            "password": mobile_password,
            "password_confirm": mobile_password,  # credential-sweep:allow: test payload field
        }
        register_status, register_body = post_json(api, "/api/v1/user/register", mobile_register_payload)
        if register_status != 200 or "otp_token" not in register_body:
            raise RuntimeError(f"Mobile register failed: {register_status} {register_body}")
        render_api_page(
            api_page,
            "WF-007 Mobile API Register",
            [
                ("POST /api/v1/user/register", {**mobile_register_payload, "password": "***", "password_confirm": "***"}),  # credential-sweep:allow: test payload field
                ("Response", {"http_status": register_status, **redact_response(register_body)}),
            ],
        )
        shot(api_page, "004-mobile-api-register-request-response.png")

        pending_summary = get_user_summary(mobile_email)
        render_api_page(
            api_page,
            "WF-007 Mobile Pending Account",
            [
                ("Database user before OTP verify", pending_summary),
                ("Expected state", "Blocked account with student role until OTP verification activates it."),
            ],
        )
        shot(api_page, "005-mobile-api-pending-user-before-otp-verify.png")

        resend_status, resend_body = post_json(
            api,
            "/api/v1/user/register/resend-otp",
            {"otp_token": register_body["otp_token"]},
        )
        if resend_status != 200 or "otp_token" not in resend_body:
            raise RuntimeError(f"Mobile resend OTP failed: {resend_status} {resend_body}")
        render_api_page(
            api_page,
            "WF-007 Mobile API Resend OTP",
            [
                ("POST /api/v1/user/register/resend-otp", {"otp_token": redact_token(register_body["otp_token"])}),
                ("Response", {"http_status": resend_status, **redact_response(resend_body)}),
            ],
        )
        shot(api_page, "006-mobile-api-resend-otp-request-response.png")

        otp_code = get_otp_code(resend_body["otp_token"])
        if not re.match(r"^\d{6}$", otp_code):
            raise RuntimeError("Could not retrieve a valid OTP code from the server-side session")

        verify_payload = {
            "otp_token": resend_body["otp_token"],
            "otp_code": otp_code,
        }
        render_api_page(
            api_page,
            "WF-007 Mobile API Verify OTP Before Submit",
            [
                ("POST /api/v1/user/register/verify-otp", {"otp_token": redact_token(verify_payload["otp_token"]), "otp_code": "***"}),
                ("Server-side QA", "OTP code was read from the expirable key-value session for this test only."),
            ],
        )
        shot(api_page, "007-mobile-api-verify-otp-before-submit.png")

        verify_status, verify_body = post_json(api, "/api/v1/user/register/verify-otp", verify_payload)
        if verify_status != 200 or verify_body.get("status") != "verified":
            raise RuntimeError(f"Mobile verify OTP failed: {verify_status} {verify_body}")
        render_api_page(
            api_page,
            "WF-007 Mobile API Verify OTP Response",
            [
                ("Response", {"http_status": verify_status, **redact_response(verify_body)}),
            ],
        )
        shot(api_page, "008-mobile-api-verify-otp-after-submit-token-issued.png")

        token = verify_body["access_token"]
        me_response = api.get(
            "/api/v1/user/me",
            headers={"X-Authorization": f"Bearer {token}"},
        )
        me_body = me_response.json()
        if me_response.status != 200 or "student" not in me_body.get("roles", []):
            raise RuntimeError(f"Mobile /me failed: {me_response.status} {me_body}")
        render_api_page(
            api_page,
            "WF-007 Mobile API Authenticated Profile",
            [
                ("GET /api/v1/user/me", {"X-Authorization": f"Bearer {redact_token(token)}"}),
                ("Response", {"http_status": me_response.status, **me_body}),
            ],
        )
        shot(api_page, "009-mobile-api-authenticated-me-student-role.png")

        duplicate_status, duplicate_body = post_json(
            api,
            "/api/v1/user/register",
            mobile_register_payload,
        )
        if duplicate_status != 422 or duplicate_body.get("error") != "validation_error":
            raise RuntimeError(f"Mobile duplicate email validation failed: {duplicate_status} {duplicate_body}")
        render_api_page(
            api_page,
            "WF-007 Mobile API Duplicate Email",
            [
                ("POST /api/v1/user/register", {**mobile_register_payload, "password": "***", "password_confirm": "***"}),  # credential-sweep:allow: test payload field
                ("Response", {"http_status": duplicate_status, **duplicate_body}),
            ],
        )
        shot(api_page, "010-mobile-api-duplicate-email-validation.png")

        web_summary = get_user_summary(web_email)
        mobile_summary = get_user_summary(mobile_email)
        render_api_page(
            api_page,
            "WF-007 Final Database Verification",
            [
                ("Web user", web_summary),
                ("Mobile user", mobile_summary),
            ],
        )
        shot(api_page, "011-system-database-user-role-verification.png")

        print(f"Captured WF-007 screenshots in {OUT}")
        print(f"Web user: {web_email}")
        print(f"Mobile user: {mobile_email}")
        print(f"Mobile uid: {mobile_summary.get('uid')}")

        api.dispose()
        web_context.close()
        browser.close()


if __name__ == "__main__":
    main()
