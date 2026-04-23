# EverLMS — AI Content Generation Guide

> This guide tells AI agents (Claude Code, Gemini) how to generate content for EverLMS from seed input.

## Prerequisites

1. File `.agent/seed/seed-input.json` must exist (created via admin form at `/workspace/content-tools/seed-input`)
2. Read `.agent/docs-v2/01-requirement/` for entity structure reference

## Step 1: Read Seed Input

```
Read .agent/seed/seed-input.json
```

Extract:
- Organization info (name, industry, description)
- Training program config (language, tone, detail level)
- Course list (titles, categories, chapters/lessons counts, keywords)
- Instructor list
- Sample data flags (what to generate)
- Advanced settings

## Step 2: Generate Content Files

Generate JSON files into `.agent/generated/` in this order:

### 2.1 Users

**File:** `users/instructors.json` and `users/students.json`

Create instructor accounts from seed input. Generate student accounts with Vietnamese names if language=vi, English names otherwise. Use format:

```json
{
  "instructors": [
    {"username": "instructor01", "email": "...", "display_name": "...", "password": "Instructor@123", "role": "instructor", "bio": "...", "expertise": "..."}
  ]
}
```

```json
{
  "students": [
    {"username": "student01", "email": "student01@example.com", "display_name": "...", "password": "Student@123", "role": "student"}
  ]
}
```

### 2.2 Courses (one file per course)

**File:** `courses/course-{NNN}-{slug}.json`

For EACH course in seed input, generate a complete course with chapters and lessons. The content must:
- Match the course's keywords and category
- Be in the specified language (vi/en/bilingual)
- Match the specified tone (professional/friendly/academic)
- Have the specified detail level for lesson content
- Include realistic, educational content — not placeholder text

```json
{
  "course": {
    "title": "...",
    "description": "<p>HTML description</p>",
    "short_description": "1-2 sentences",
    "category": "Programming",
    "level": "beginner",
    "language": "vi",
    "pricing_type": "paid",
    "price": 500000,
    "currency": "VND",
    "duration": "20 hours",
    "status": "published",
    "featured": false,
    "instructor_email": "instructor@example.com"
  },
  "chapters": [
    {
      "title": "Chapter title",
      "description": "Chapter description",
      "weight": 0,
      "lessons": [
        {
          "title": "Lesson title",
          "type": "text",
          "content": "<h2>...</h2><p>Full lesson content in HTML...</p>",
          "duration": 15,
          "is_preview": true,
          "weight": 0
        }
      ]
    }
  ]
}
```

**Lesson content guidelines:**
- `text` type: Write 300-800 words per lesson with headings, paragraphs, code blocks, lists
- `video` type: Include `video_url` field with placeholder URL from advanced settings
- First lesson of first chapter should have `is_preview: true`
- Duration in minutes (estimate based on content length)

### 2.3 Quizzes (for courses with `has_final_quiz: true`)

**File:** `quizzes/quiz-course-{NNN}-final.json`

Generate 10-20 questions covering all 8 question types. Questions MUST relate to the course content:

```json
{
  "quiz": {
    "title": "Final Quiz: {course_title}",
    "course_ref": "course-{NNN}",
    "instructions": "Quiz instructions...",
    "pass_threshold": 70,
    "time_limit": 30,
    "max_attempts": 3,
    "display_style": "question_step"
  },
  "questions": [
    {
      "type": "single_choice|multiple_choice|true_false|fill_blank|short_answer|matching|ordering|essay",
      "text": "Question text",
      "points": 5,
      "answers": [{"text": "...", "is_correct": true/false}],
      "explanation": "Why this is correct..."
    }
  ]
}
```

**Question type distribution (for 15 questions):**
- 3x single_choice (4 options each)
- 2x multiple_choice (4 options, 2+ correct)
- 2x true_false
- 2x fill_blank (with 1-2 accepted answers)
- 1x short_answer
- 2x matching (4-6 pairs)
- 1x ordering (4-6 items)
- 2x essay (with rubric and min_words)

### 2.4 Certificates (for courses with `has_certificate: true`)

**File:** `certificates/certificate-templates.json`

```json
{
  "templates": [
    {"name": "Certificate - {course_title}", "layout": "landscape"}
  ]
}
```

### 2.5 Commerce (if sample_data flags enabled)

**File:** `commerce/membership-plans.json`

```json
{
  "membership_plans": [
    {"name": "Basic Plan", "price": 199000, "duration": "monthly", "tier": "basic"},
    {"name": "Premium Plan", "price": 499000, "duration": "monthly", "tier": "premium"},
    {"name": "Enterprise Plan", "price": 999000, "duration": "yearly", "tier": "enterprise"}
  ]
}
```

### 2.6 Community (if sample_data flags enabled)

**File:** `community/forum-topics.json`, `community/ratings.json`, `community/badges.json`

Generate 2-3 forum topics per course, ratings (3-5 stars), and badge definitions.

### 2.7 Manifest

**File:** `manifest.json`

```json
{
  "generated_at": "ISO 8601 date",
  "generated_by": "Claude Code (Opus 4.6)",
  "seed_input_hash": "first 8 chars of seed input JSON md5",
  "summary": {
    "total_courses": 5,
    "total_chapters": 25,
    "total_lessons": 100,
    "total_quizzes": 5,
    "total_questions": 75,
    "total_instructors": 3,
    "total_students": 20
  },
  "files": [
    {"path": "users/instructors.json", "type": "users", "status": "ready"},
    {"path": "courses/course-001-react-fundamentals.json", "type": "course", "status": "ready"}
  ],
  "import_order": [
    "users/instructors.json",
    "users/students.json",
    "courses/course-001-react-fundamentals.json",
    "quizzes/quiz-course-001-final.json",
    "certificates/certificate-templates.json",
    "commerce/membership-plans.json",
    "community/forum-topics.json",
    "community/badges.json"
  ]
}
```

## Step 3: Token Optimization

- Generate ONE course at a time (each course = 1 file)
- If context window is limited, split across sessions:
  - Session 1: Users + Courses 1-3
  - Session 2: Courses 4-N
  - Session 3: Quizzes
  - Session 4: Commerce + Community + Manifest
- Check `.agent/generated/` at start of each session to see what's already done
- Don't re-read requirement docs if already in context

## Step 4: After Generation

Tell the user:
```
Content generated successfully! Files are in .agent/generated/

To import into the system:
  drush everlms:import --dry-run    # Validate first
  drush everlms:import              # Import all content

To clean and re-generate:
  drush everlms:clean               # Clean existing content
  # Then re-run content generation
```

## Important Notes

- All HTML content should use `full_html` format
- Entity field names use `field_` prefix (e.g., `field_price`, `field_description`)
- Course entity uses `name` field (not `title`) for the label
- Respect the `language`, `tone`, and `detail_level` settings from seed input
- Generate realistic, educational content — not Lorem ipsum
- Questions must test knowledge from the actual lesson content
