---
trigger: always_on
description: Quy tắc khi tạo UI mockup với generate_image (Stitch AI) và quản lý design assets.
---

## 1. STITCH AI = `generate_image`
Khi user hỏi "Stitch AI", "UI Mockups" → sử dụng `generate_image` tool.

## 2. PNG ONLY
Generate **CHỈ** PNG images. **KHÔNG** generate HTML/CSS/JS trừ khi user yêu cầu rõ "code" hoặc "implementation".

## 3. CHẤT LƯỢNG & ĐỘ PHÂN GIẢI
- Luôn chỉ định **"High Quality, 1920x1080 Full HD, Aspect Ratio 16:9"** trong prompt.
- Text phải rõ ràng và đọc được (pseudo-text chấp nhận nhưng layout phải clear).

## 4. TUÂN THỦ NỘI DUNG
Follow layout rules chính xác (VD: nếu "No Charts" → không có charts).

## 5. TỔ CHỨC ASSETS
Di chuyển generated artifacts vào thư mục `docs/design/assets/` tương ứng sau khi tạo.