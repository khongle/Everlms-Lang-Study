# Flow: Course Creation (Instructor/Admin)

```mermaid
flowchart TD
    A[Create Course] --> B[Fill Course Info]
    B --> C[Set Pricing]
    C --> D[Assign Category & Tags]
    D --> E[Save as Draft]
    E --> F[Add Chapters]
    F --> G[Add Lessons to Chapters]
    G --> H{Content Type?}
    H -->|Video| I[Add Video URL/Upload]
    H -->|Text| J[Write Content]
    H -->|Quiz| K[Create/Link Quiz]
    H -->|H5P| L[Embed H5P]
    H -->|SCORM| M[Upload SCORM Package]
    H -->|Zoom| N[Create Zoom Meeting]
    I & J & K & L & M & N --> O[Configure Lesson Settings]
    O --> P{More Lessons?}
    P -->|Yes| G
    P -->|No| Q{More Chapters?}
    Q -->|Yes| F
    Q -->|No| R[Add Final Quiz Optional]
    R --> S[Link Certificate Template]
    S --> T[Preview Course]
    T --> U{Ready?}
    U -->|No| V[Edit]
    V --> B
    U -->|Yes| W[Publish Course]
    W --> X[Course Live 🚀]
```

## AI-Assisted Creation (Optional)

```mermaid
flowchart TD
    A[Click 'Generate with AI'] --> B[Enter Topic + Level + Language]
    B --> C[Set Generation Options]
    C --> C1[Target Audience]
    C --> C2[Number of Chapters: 5-15]
    C --> C3[Lessons per Chapter: 3-8]
    C --> C4[Include Quizzes: Yes/No]
    C1 & C2 & C3 & C4 --> D[Send to AI Service]
    D --> E[AI Generates Course Outline]
    E --> F[Display Preview: Chapters + Lesson Titles]
    F --> G{Approve Outline?}
    G -->|No| H[Edit Outline / Regenerate]
    H --> D
    G -->|Yes| I[AI Generates Lesson Content]
    I --> J[Progress Bar: Generating 1/24...]
    J --> K{Include Quizzes?}
    K -->|Yes| L[AI Generates Quiz Questions per Chapter]
    K -->|No| M[Skip]
    L & M --> N[All Content Ready]
    N --> O[Instructor Reviews Each Lesson]
    O --> P[Edit/Refine AI Content]
    P --> Q{More Lessons to Review?}
    Q -->|Yes| O
    Q -->|No| R[Continue Normal Flow → Preview → Publish]
```

### AI Generation Capabilities
| Content | AI Action | Quality |
|---------|-----------|---------|
| Course outline | Generate chapter + lesson structure | High — well-organized |
| Lesson text | Generate rich text with headings, lists, code samples | Medium — needs review |
| Quiz questions | Generate MC/SC/TF questions per chapter | Medium — verify correctness |
| Course description | Generate SEO-optimized description | High |

> [!IMPORTANT]
> AI-generated content **luôn ở trạng thái Draft**. Instructor **bắt buộc** review và approve từng lesson trước khi publish.

