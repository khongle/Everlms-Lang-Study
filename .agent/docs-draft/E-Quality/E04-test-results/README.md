# E04 — Test Results Index

> Run logs từ các lần thực thi test. Mỗi file là một run log độc lập.
> **Không chứa test definitions** — xem E03 cho specs.

## Run Log Format

Mỗi file trong thư mục này tuân theo format sau:

```
RUN-{NNN}-{YYYY-MM-DD}.md
```

### Frontmatter bắt buộc

```markdown
| Field | Value |
|-------|-------|
| Run ID | RUN-{NNN} |
| Date | YYYY-MM-DD HH:MM |
| Trigger | [manual | CI | pre-release] |
| Branch | {branch name} |
| Commit | {short SHA} |
| Runner | {agent ID or "human"} |
| Gate | G3 / G4 / pre-release |
```

### Sections

```markdown
## Summary
| Test Type | Total | Pass | Fail | Skip | Coverage |
|-----------|-------|------|------|------|----------|
| Unit      | N     | N    | N    | N    | N%       |
| Kernel    | N     | N    | N    | N    | —        |
| E2E       | N     | N    | N    | N    | —        |

## Failures
| Test ID | Test Name | Error Message | Linked Issue |
|---------|-----------|---------------|--------------|

## New Issues Found
| Issue | Severity | Linked to |
|-------|----------|-----------|

## Gate Decision
| Gate | Result | Notes |
|------|--------|-------|
| G1 | PASS/FAIL | |
| G2 | PASS/FAIL | |
| G3 | PASS/FAIL | |
| G4 | PASS/FAIL | |
```

## Registry

| Run ID | Date | Trigger | Gate | Pass | Fail | Branch |
|--------|------|---------|------|------|------|--------|
| *(chưa có run nào)* | — | — | — | — | — | — |

## Trạng thái

Chưa có test run nào được thực hiện. Test definitions đã được tạo trong E03.  
Run đầu tiên sẽ được ghi lại sau khi G-HARDENING units (U-057–U-070) được implement và G3 gate được chạy.

## Links

- Test definitions: E03-test-definitions/
- Test strategy + tools: E01-test-strategy.md
- Issues phát hiện: E02-issues/
