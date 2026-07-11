# C16 Sheet - 12 objects

Giải pháp ở cấp object. Nguồn: [C15](../C15-object-steps/README.md).

| Concept | Item | Drupal primitive to use | Owner module | Implementation pattern | Source evidence | Tests/checks |
| ------- | ---- | ----------------------- | ------------ | ---------------------- | --------------- | ------------ |
| Object | Course | content entity + publish status | lms_course | create→publish→archive | C15 course | lifecycle |
| Object | Order | content entity + state field | lms_order | create→pay→archive | C15 order | state |
| Object | Certificate | content entity + event | lms_certificate | issue→revoke→expire | C15 certificate | idempotency |
| Object | Membership | content entity + dates | lms_membership | active→cancel→expire | C15 membership | entitlement |
| Object | Quiz result | content entity + grading | lms_quiz | submit→grade→finalize | C15 quiz-result | grade |
