# Feature: Content & Media Management

## 1. Tổng quan

Hỗ trợ đa dạng loại nội dung giáo dục: Video, H5P interactive, SCORM packages, rich text, và file attachments. CMS linh hoạt, dễ sử dụng cho instructor.

---

## 2. Video Integration

### 2.1 Uploaded Video (Self-hosted)

> Host video trực tiếp trên server — không phụ thuộc third-party, không ads, bảo vệ IP.

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| UV-01 | Direct upload | Upload video trực tiếp lên server (MP4, WebM, OGG) |
| UV-02 | Subtitles | Thêm subtitle files (SRT/VTT) cho accessibility |
| UV-03 | Streaming optimization | Built-in caching & performance tuning cho smooth streaming |
| UV-04 | No third-party dependency | Không ads, không bị takedown, full control |
| UV-05 | Privacy controls | Set viewing permissions by role hoặc membership |
| UV-06 | IP protection | Protect intellectual property, DRM-lite |
| UV-07 | Multiple formats | Hỗ trợ nhiều video formats |
| UV-08 | File size handling | Optimized cho large video files |

### 2.2 External Video Embed

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| V-01 | YouTube embed | Nhúng video YouTube qua URL/ID |
| V-02 | Vimeo embed | Nhúng video Vimeo qua URL/ID |
| V-03 | Generic embed | Hỗ trợ embed code từ bất kỳ platform |

### 2.3 Video Player Common Features

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| V-04 | Video player | Responsive video player với controls |
| V-05 | Video progress | Track % video đã xem |
| V-06 | Video thumbnail | Auto-generate hoặc custom thumbnail |
| V-07 | Playback speed | Cho phép user chỉnh tốc độ phát (0.5x → 2x) |

---

## 3. Zoom Integration

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| Z-01 | Live class scheduling | Tạo live session gắn với lesson/course |
| Z-02 | Zoom meeting creation | Auto-create Zoom meeting qua API |
| Z-03 | Join button | Nút Join hiển thị khi session sắp bắt đầu |
| Z-04 | Recording link | Lưu recording link sau khi session kết thúc |
| Z-05 | Calendar integration | Hiển thị lịch live sessions trên calendar view |
| Z-06 | Notification | Nhắc nhở trước khi live session bắt đầu |

---

## 4. H5P Integration

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| H-01 | H5P embed | Nhúng H5P content vào lesson |
| H-02 | H5P types | Hỗ trợ: interactive video, quiz, presentation, flashcards, etc. |
| H-03 | Score tracking | Lưu điểm H5P vào lesson progress |
| H-04 | H5P library | Quản lý thư viện H5P content types |

---

## 5. SCORM Integration

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| S-01 | SCORM upload | Upload SCORM 1.2 / 2004 packages (.zip) |
| S-02 | SCORM player | Embedded player trong lesson page |
| S-03 | SCORM tracking | Lưu completion status, score, time spent |
| S-04 | SCORM management | Quản lý SCORM packages (upload, replace, delete) |

---

## 6. Content Management (CMS)

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| CMS-01 | Rich text editor | CKEditor 5 với full formatting |
| CMS-02 | Media library | Quản lý images, files, videos tập trung |
| CMS-03 | Content revision | Version history cho lessons & courses |
| CMS-04 | Content scheduling | Hẹn giờ publish/unpublish |
| CMS-05 | Content templates | Lesson templates để tái sử dụng layout |
| CMS-06 | Drag-drop media | Kéo thả media vào editor |
| CMS-07 | Webform support | Tích hợp Drupal Webform cho surveys, feedback forms |

---

## 7. File Management

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| F-01 | File upload | Upload: PDF, DOCX, PPTX, ZIP |
| F-02 | File size limit | Configurable max file size |
| F-03 | Downloadable resources | Đính kèm tài liệu cho lesson (user download) |
| F-04 | File access control | Chỉ enrolled users mới download được |
