---
description: Backup database with DDEV Drush sql-dump (e.g. --path databases/custom.sql.gz)
tags: [backup, database, drush]
---

# `site-bk` Workflow

Backup database của Drupal site đang chạy trên DDEV.

## Usage

- `site-bk` (Default): Backup tới `databases/backup.sql.gz`
- `site-bk --path databases/my_backup.sql.gz`: Backup tới path tùy chỉnh

## Instructions for Agent

1.  **Analyze Request**:
    -   Xác định backup path. Default: `databases/backup.sql.gz`.
    -   Nếu user cung cấp `--path`, sử dụng path đó.

2.  **Execute**:

    ```bash
    # Step 1: Tạo thư mục backup (cùng cấp web/)
    mkdir -p databases

    # Step 2: Chạy sql-dump
    ddev drush sql-dump --gzip --result-file=../databases/backup.sql
    ```

    > [!NOTE]
    > `--result-file` là **relative to Drupal root** (`web/`).
    > Dùng `../` để trỏ ra project root. `--gzip` tự thêm `.gz`.

    Nếu user chỉ định path khác (VD: `--path databases/20250215.sql.gz`):
    ```bash
    mkdir -p databases
    ddev drush sql-dump --gzip --result-file=../databases/20250215.sql
    ```

3.  **Verification**:
    -   `ls -la databases/` — kiểm tra file tồn tại và kích thước.

4.  **Lưu ý**:
    -   File cũ sẽ bị **ghi đè**.
    -   Database backup sẽ được **commit vào Git** (không ignore).