#!/usr/bin/env python3
"""WF-004 manager review — manager filters processing orders, reviews order 154,
approves (Completed → Enroll). Round-2."""
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from lib import BASE_URL, SCREENS, Session, run, shot  # noqa: E402

OUT = SCREENS / "004-order-checkout-and-payment"
ORDER = 154


def main(pw):
    with Session(pw, "manager") as page:
        # Filtered order list (processing)
        page.goto(f"{BASE_URL}/workspace/orders?search={ORDER}&status=processing",
                  wait_until="networkidle", timeout=60000)
        shot(page, OUT, "07-manager-orders-list-processing.png")
        body = page.locator("body").inner_text()
        print("  list shows order:", f"{ORDER}" in body, "| Processing:", "Processing" in body)

        # Review/approve form
        page.goto(f"{BASE_URL}/workspace/orders/{ORDER}/edit", wait_until="networkidle", timeout=60000)
        shot(page, OUT, "08-manager-order-review-before.png")

        page.locator("select[name='field_status']").select_option("completed")
        shot(page, OUT, "09-manager-order-status-completed-selected.png")
        page.get_by_role("button", name="Update Order").click()
        page.wait_for_load_state("networkidle", timeout=30000)
        shot(page, OUT, "10-manager-order-approved.png")
        print("  after approve URL:", page.url)


if __name__ == "__main__":
    run(main)
