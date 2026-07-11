#!/usr/bin/env python3
"""Capture WF-010 AI-assisted course generation with real form login."""

import html
import json
import os
import re
import subprocess
from datetime import datetime
from pathlib import Path

from playwright.sync_api import expect, sync_playwright


ROOT = Path("/home/hainn/projects/everlms")
BASE_URL = "https://everlms.ddev.site"
OUT = ROOT / ".agent/docs/E-Quality/E00-workflows/round-1/_screens/010-ai-assisted-content-generation"

INSTRUCTOR_USER = "sabine.mueller"
PASSWORD = os.environ.get("EVERLMS_INSTRUCTOR_PASSWORD", "ChangeMe@123")
SUCCESS_PROVIDER = os.environ.get("EVERLMS_AI_PROVIDER", "openai")


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


def login(page, username: str, before_name: str, after_name: str) -> None:
    page.goto(f"{BASE_URL}/user/login", wait_until="domcontentloaded", timeout=60000)
    expect(page.locator("input[name='name']")).to_be_visible(timeout=15000)
    shot(page, before_name)
    page.locator("input[name='name']").fill(username)
    page.locator("input[name='pass']").fill(PASSWORD)
    shot(page, after_name)
    page.locator("input[type='submit']").click()
    page.wait_for_url(re.compile(r".*/user/\d+.*"), timeout=30000)
    expect(page.locator("body")).to_contain_text(username.split(".")[0].capitalize(), timeout=15000)


def drush_eval(code: str) -> str:
    return subprocess.check_output(
        ["ddev", "drush", "php:eval", code],
        cwd=str(ROOT),
        text=True,
    ).strip()


def render_verification_page(page, title: str, blocks: list[tuple[str, object]]) -> None:
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
              font-family: Inter, Arial, sans-serif;
              background: #f6f7fb;
              color: #172033;
            }}
            body {{
              margin: 0;
              padding: 48px;
            }}
            main {{
              max-width: 1120px;
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


def course_summary(course_id: str) -> dict:
    code = f"""
    $course = \\Drupal\\lms_course\\Entity\\Course::load({int(course_id)});
    if (!$course) {{ echo '{{}}'; return; }}
    $chapters = [];
    foreach ($course->get('field_chapters')->referencedEntities() as $chapter) {{
      $lessons = [];
      foreach ($chapter->get('field_lessons')->referencedEntities() as $lesson) {{
        $lessons[] = [
          'id' => (int) $lesson->id(),
          'title' => $lesson->label(),
          'type' => $lesson->get('field_type')->value,
          'quiz_id' => $lesson->get('field_quiz')->target_id ? (int) $lesson->get('field_quiz')->target_id : NULL,
          'status' => (int) $lesson->get('status')->value,
        ];
      }}
      $chapters[] = [
        'id' => (int) $chapter->id(),
        'title' => $chapter->label(),
        'lessons' => $lessons,
      ];
    }}
    $quiz_ids = \\Drupal::entityQuery('lms_quiz')->accessCheck(FALSE)->condition('field_course', {int(course_id)})->execute();
    $quizzes = [];
    foreach (\\Drupal::entityTypeManager()->getStorage('lms_quiz')->loadMultiple($quiz_ids) as $quiz) {{
      $quizzes[] = [
        'id' => (int) $quiz->id(),
        'title' => $quiz->label(),
        'questions' => count($quiz->get('field_questions')),
        'status' => (int) $quiz->get('status')->value,
      ];
    }}
    $queue = \\Drupal::database()->select('queue', 'q')
      ->condition('name', 'lms_ai_content_generator')
      ->countQuery()
      ->execute()
      ->fetchField();
    echo json_encode([
      'course' => [
        'id' => (int) $course->id(),
        'title' => $course->label(),
        'owner_uid' => (int) $course->getOwnerId(),
        'published_status' => (int) $course->get('status')->value,
        'price_type' => $course->get('field_price_type')->value,
      ],
      'chapters' => $chapters,
      'quizzes' => $quizzes,
      'ai_queue_items_total' => (int) $queue,
    ], JSON_PRETTY_PRINT);
    """
    return json.loads(drush_eval(code) or "{}")


def course_count_for_title(title: str) -> int:
    safe_title = title.replace("\\", "\\\\").replace("'", "\\'")
    code = (
        "$ids = \\Drupal::entityQuery('lms_course')->accessCheck(FALSE)"
        f"->condition('name', '{safe_title}')->execute(); echo count($ids);"
    )
    return int(drush_eval(code) or "0")


def fill_builder(page, topic: str, audience: str, tone: str, provider: str) -> None:
    page.locator("input[name='topic']").fill(topic)
    page.locator("input[name='audience']").fill(audience)
    page.locator("select[name='tone']").select_option(tone)
    page.locator("select[name='provider']").select_option(provider)


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)

    stamp = datetime.now().strftime("%Y%m%d-%H%M%S")
    success_topic = f"WF010 Safe wheelchair transfer micro-course {stamp}"
    failure_topic = f"WF010 Pollinations unsupported draft {stamp}"

    with sync_playwright() as p:
      browser = p.chromium.launch(
          headless=True,
          args=["--no-sandbox", "--force-color-profile=srgb"],
      )
      context = browser.new_context(
          viewport={"width": 1440, "height": 1100},
          ignore_https_errors=True,
      )
      page = context.new_page()

      login(
          page,
          INSTRUCTOR_USER,
          "001-instructor-login-before-fill.png",
          "002-instructor-login-after-fill.png",
      )

      page.goto(f"{BASE_URL}/manage/courses/ai-builder", wait_until="domcontentloaded", timeout=60000)
      expect(page.locator("input[name='topic']")).to_be_visible(timeout=15000)
      expect(page.locator("select[name='provider']")).to_be_visible(timeout=15000)
      shot(page, "003-instructor-ai-builder-before-fill.png")

      fill_builder(page, success_topic, "Beginner caregivers", "professional", SUCCESS_PROVIDER)
      shot(page, "004-instructor-ai-builder-after-fill.png")

      page.locator("input[name='generate_draft']").click()
      expect(page.locator("input[name='save_draft']")).to_be_visible(timeout=120000)
      expect(page.locator("body")).to_contain_text("Review generated draft", timeout=15000)
      expect(page.locator("body")).to_contain_text("Final knowledge check", timeout=15000)
      shot(page, "005-instructor-ai-builder-generated-draft-review.png")

      page.locator("input[name='save_draft']").click()
      page.wait_for_url(re.compile(r".*/manage/courses/\d+/edit.*"), timeout=60000)
      expect(page.locator("body")).to_contain_text("Draft course", timeout=15000)
      shot(page, "006-instructor-course-edit-after-save.png")

      match = re.search(r"/manage/courses/(\d+)/edit", page.url)
      if not match:
          raise RuntimeError(f"Could not parse course id from {page.url}")
      course_id = match.group(1)
      saved_summary = course_summary(course_id)
      saved_title = saved_summary.get("course", {}).get("title", "")
      if not saved_title:
          raise RuntimeError(f"Could not load saved course summary for {course_id}")

      page.goto(f"{BASE_URL}/workspace/courses", wait_until="domcontentloaded", timeout=60000)
      expect(page.locator("body")).to_contain_text(saved_title, timeout=15000)
      shot(page, "007-instructor-course-list-draft-visible.png")

      page.goto(f"{BASE_URL}/manage/courses/ai-builder", wait_until="domcontentloaded", timeout=60000)
      expect(page.locator("input[name='topic']")).to_be_visible(timeout=15000)
      shot(page, "008-instructor-ai-builder-failure-before-fill.png")

      before_failure_count = course_count_for_title(failure_topic)
      fill_builder(page, failure_topic, "Beginner caregivers", "professional", "pollinations")
      shot(page, "009-instructor-ai-builder-failure-after-fill.png")

      page.locator("input[name='generate_draft']").click()
      expect(page.locator("body")).to_contain_text("AI could not generate a valid course draft", timeout=30000)
      after_failure_count = course_count_for_title(failure_topic)
      if before_failure_count != after_failure_count:
          raise RuntimeError("Failure path created a course unexpectedly")
      shot(page, "010-instructor-ai-builder-provider-failure-no-save.png")

      summary = course_summary(course_id)
      verify_page = context.new_page()
      render_verification_page(
          verify_page,
          "WF-010 Database Verification",
          [
              ("Saved draft hierarchy", summary),
              ("Failure path integrity", {"title": failure_topic, "before": before_failure_count, "after": after_failure_count}),
              ("Expected", "Course remains unpublished draft; chapters, lessons, quiz, and queued lesson generation are real entities."),
          ],
      )
      shot(verify_page, "011-system-db-hierarchy-verification.png")

      print(f"Captured WF-010 screenshots in {OUT}")
      print(f"Course ID: {course_id}")
      print(f"Course title: {summary.get('course', {}).get('title', success_topic)}")
      print(f"Provider: {SUCCESS_PROVIDER}")

      verify_page.close()
      context.close()
      browser.close()


if __name__ == "__main__":
    main()
