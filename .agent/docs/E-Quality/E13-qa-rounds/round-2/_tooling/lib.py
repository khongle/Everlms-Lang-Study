#!/usr/bin/env python3
"""Shared capture helpers for round-2 workflow QA.

Every capture_wfNNN_*.py imports from here so real-page and prototype
screenshots are produced with identical viewport / settle behaviour.
"""

from pathlib import Path

from playwright.sync_api import sync_playwright

ROOT = Path("/home/hainn/projects/everlms")
BASE_URL = "https://everlms.ddev.site"
STATE_DIR = ROOT / ".agent/docs/E00-UI-test/_tooling/state"
PROTO_DIR = ROOT / ".agent/docs/C-Design/C10-prototypes/html"
SCREENS = ROOT / ".agent/docs/E-Quality/E00-workflows/round-2/_screens"

VIEWPORT = {"width": 1440, "height": 1100}


def settle(page, pause: int = 400) -> None:
    """Wait for network + fonts + a short paint pause before a screenshot."""
    try:
        page.wait_for_load_state("networkidle", timeout=15000)
    except Exception:
        pass
    try:
        page.evaluate("document.fonts && document.fonts.ready")
    except Exception:
        pass
    page.wait_for_timeout(pause)


def shot(page, out_dir: Path, name: str, full_page: bool = True) -> Path:
    settle(page)
    out_dir.mkdir(parents=True, exist_ok=True)
    dest = out_dir / name
    page.screenshot(path=str(dest), full_page=full_page, animations="disabled")
    print(f"  shot  {name}")
    return dest


class Session:
    """Context manager giving a logged-in (or anonymous) page for one role."""

    def __init__(self, pw, role: str | None):
        self.pw = pw
        self.role = role

    def __enter__(self):
        self.browser = self.pw.chromium.launch(
            headless=True,
            args=["--no-sandbox", "--force-color-profile=srgb"],
        )
        kwargs = dict(viewport=VIEWPORT, ignore_https_errors=True)
        if self.role:
            state = STATE_DIR / f"{self.role}.json"
            kwargs["storage_state"] = str(state)
        self.context = self.browser.new_context(**kwargs)
        self.page = self.context.new_page()
        return self.page

    def __exit__(self, *exc):
        self.context.close()
        self.browser.close()


def run(fn):
    """fn(pw) -> None, wrapped in a playwright lifecycle."""
    with sync_playwright() as pw:
        fn(pw)


def proto_shot(pw, proto_file: str, out_dir: Path, name: str) -> Path:
    """Render a C10 prototype HTML file and full-page screenshot it."""
    src = PROTO_DIR / proto_file
    browser = pw.chromium.launch(headless=True, args=["--no-sandbox", "--force-color-profile=srgb"])
    ctx = browser.new_context(viewport=VIEWPORT, ignore_https_errors=True)
    page = ctx.new_page()
    page.goto(src.as_uri(), wait_until="domcontentloaded", timeout=30000)
    dest = shot(page, out_dir, name)
    ctx.close()
    browser.close()
    return dest
