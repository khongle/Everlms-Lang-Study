# Plan: Pronunciation Practice (chế độ học phát âm thứ 4)

> Trạng thái: **Đã code** (2026-09-10) — theo đúng thứ tự ở mục 6. Đã verify
> qua curl (session thật, uid 127): nút "Pronunciation Practice" render đúng
> trên `/learn/flashcards`, trang `/study?style=pronounce` trả về
> `direction=forward` + markup mic/fallback, `nextCard()` trả
> `pronunciation_level`, `submitReview()` với `pronunciation_correct` tăng/
> giảm đúng field_pronunciation_level (1↔2, kẹp ở biên), không có lỗi PHP
> trong watchdog. Chưa test tay trên trình duyệt thật (mic permission,
> Web Speech transcript, giao diện responsive) — xem mục 6 bước cuối và mục
> 7 (ngưỡng Levenshtein, số lần thử lại) khi có phản hồi người dùng thật.
> Ngày lập plan: 2026-09-09.

Bối cảnh: My Flashcards hiện có 3 chế độ học — `Study Now` (flip), `Spelling
Practice` (`?style=spell`), `Quiz Practice` (`?style=quiz`). Cả 3 dùng chung
route `/learn/flashcards/{deck}/study?style=...`, ghi điểm/"level" riêng vào
`CardStat`, và vẫn cập nhật lịch SRS thật (SM-2) song song. Chế độ mới —
**Pronunciation Practice** — sẽ nối đúng khuôn mẫu này với `style=pronounce`.

**Quyết định đã chốt:** dùng **Web Speech API** của trình duyệt để chấm phát
âm (miễn phí, client-side, không cần server/API key). Nhược điểm: chỉ chạy ổn
định trên Chrome/Edge, cần fallback cho Safari/Firefox — xem mục 2.

---

## 1. Khác biệt về UX so với Spelling/Quiz

Spelling và Quiz đều ép `direction=reverse` (che từ, bắt đoán từ từ nghĩa) vì
mục tiêu là *nhớ lại*. Pronunciation Practice mục tiêu là *phát âm đúng một từ
đã biết*, nên:

- Hiển thị **term + IPA (`field_pronunciation`) + nghĩa** ngay từ đầu (giữ
  `direction=forward` mặc định, không che gì).
- Nút loa "Nghe mẫu" tái dùng `speakCard()` sẵn có trong
  `js/flashcard-study.js` (ưu tiên `field_audio`, fallback TTS).
- Thêm nút "Ghi âm" mới — học viên bấm, đọc theo, hệ thống chấm ngay.

## 2. Luồng chấm điểm (client-side, Web Speech API)

1. Bấm mic → khởi tạo `SpeechRecognition` / `webkitSpeechRecognition`, `lang`
   map từ `deck.language` (vd `en` → `en-US`).
2. Lấy transcript từ `onresult`, chuẩn hoá cả transcript và `card.term`
   (lowercase, bỏ dấu câu, trim khoảng trắng thừa).
3. So khớp bằng tỉ lệ giống ký tự (Levenshtein) → 3 mức:
   - **Chính xác**: tỉ lệ giống ≥ ~0.75
   - **Gần đúng, thử lại**: 0.5–0.75
   - **Chưa đúng**: < 0.5

   Ngưỡng cụ thể cần tinh chỉnh sau khi thử thật với người dùng.
4. Cho phép ghi âm lại (giới hạn số lần thử, giống UX 3-exercise của Quiz),
   rồi mới coi là "xong thẻ".
5. **Fallback khi trình duyệt không hỗ trợ** (Safari/Firefox không có Web
   Speech API ổn định): ẩn nút mic, hiện 2 nút tự chấm "Tôi phát âm đúng /
   Cần luyện thêm" — giữ tính năng dùng được ở mọi trình duyệt thay vì chặn
   hẳn.
6. Xử lý khi bị từ chối quyền mic: thông báo hướng dẫn + rơi về fallback tự
   chấm ở bước 5.

## 3. Backend cần thêm

- **CardStat**: thêm `field_pronunciation_level` (integer, mặc định 1,
  khoảng 1–5 giống `field_spell_level` vì đây cũng là 1 kỹ năng đơn chứ
  không phải 3 dạng bài như Quiz) + 1 `hook_update_N` mới trong
  `lms_flashcard.install`, đúng khuôn `field_spell_level` /
  `field_quiz_level` đã có.
- **Card entity**: không cần field mới — `field_pronunciation` (IPA) và
  `field_audio` (audio mẫu) đã đủ.
- **StudyController::study()**: thêm `'pronounce'` vào danh sách style hợp
  lệ; **không** ép `direction=reverse` cho style này (khác spell/quiz).
- **FlashcardApiController::nextCard()**: thêm `'pronounce'` vào điều kiện
  `$drillAll` (để luyện hết cả bộ thẻ như spell/quiz, bất kể lịch SRS).
- **FlashcardApiController::submitReview()**: thêm nhánh đọc
  `pronunciation_correct` (bool) từ request, tự suy `quality` (đúng→Good,
  sai→Again) y hệt cách `spell_correct` / `quiz_correct` đang làm, cập nhật
  `field_pronunciation_level` với hằng số MIN/MAX riêng
  (`PRONUNCIATION_LEVEL_MIN`/`MAX`).

## 4. Frontend cần thêm

- `templates/lms-flashcard-my-decks.html.twig`: thêm nút thứ 4
  "Pronunciation Practice" (icon `fa-microphone`) cạnh 2 nút hiện có, link
  `?style=pronounce`.
- `js/flashcard-study.js`: hàm mới `buildPronunciationExercise()` (đặt cạnh
  `buildSpellPuzzle`/`buildQuizExercise`) xử lý toàn bộ luồng ở mục 2, cộng
  hàm `submitPronunciationResult()` gửi lên `/api/lms/flashcard/review`.
- CSS (`css/flashcard-study.css`, `css/flashcard-pages.css`): style nút mic
  (idle / đang ghi có hiệu ứng pulse / đang xử lý), thẻ kết quả đúng/gần
  đúng/sai, nút link mới trên trang My Flashcards.

## 5. Rủi ro cần lưu ý

- Web Speech API cần **secure context** (HTTPS) — cần test bằng URL https
  của ddev, không phải http (site hiện cảnh báo "Không bảo mật" khi vào qua
  http).
- Chỉ chạy ổn định trên Chrome/Edge; Safari/Firefox dùng fallback tự chấm ở
  mục 2.5.
- Giọng vùng miền / từ đồng âm có thể khiến transcript sai dù đọc đúng → luôn
  có lối thoát tự chấm thủ công.

## 6. Thứ tự triển khai khi bắt đầu code

1. `CardStat.field_pronunciation_level` + hook_update.
2. `StudyController` + `FlashcardApiController` (style=pronounce, drillAll,
   submitReview).
3. Nút mới trên trang My Flashcards.
4. JS ghi âm + chấm + fallback.
5. CSS.
6. Test tay: Chrome (có mic), Firefox/Safari (fallback), từ chối quyền mic.

## 7. Việc còn cần chốt trước khi code (nếu có thay đổi ý)

- Thang điểm `field_pronunciation_level`: hiện đề xuất 1–5 (giống Spelling).
- Ngưỡng so khớp Levenshtein: hiện đề xuất 0.75 / 0.5 — cần thử thật để tinh
  chỉnh.
- Số lần cho phép ghi âm lại trên 1 thẻ trước khi bắt buộc qua thẻ tiếp theo.
