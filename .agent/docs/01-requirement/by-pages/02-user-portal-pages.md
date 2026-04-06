# User Portal Pages (23)

> Pages for authenticated students / learners.

| # | Page | Route | Template | Mô tả |
|---|------|-------|----------|-------|
| UP-01 | My Courses | `/my-courses` | `page--my-courses` | Enrolled courses + progress bars |
| UP-02 | Course Learning | `/learn/{course_slug}` | `page--learn` | Course content with sidebar curriculum |
| UP-03 | Lesson View | `/learn/{course}/{lesson}` | `page--lesson` | Lesson content (video/text/H5P/SCORM/Zoom) |
| UP-04 | Take Quiz | `/take-quiz/{quiz_id}` | `page--take-quiz` | Quiz taking interface + timer |
| UP-05 | Quiz Result | `/take-quiz/result/{result_id}` | `page--quiz-result` | Score, pass/fail, answer review |
| UP-06 | My Profile | `/user/{uid}` | `page--user-profile` | Profile view with stats |
| UP-07 | Edit Profile | `/user/{uid}/edit` | `page--user-edit` | Edit profile form |
| UP-08 | My Certificates | `/my-certificates` | `page--my-certificates` | Certificate listing |
| UP-09 | Certificate View | `/my-certificates/{id}` | `page--certificate` | Certificate detail + download PDF |
| UP-10 | My Orders | `/my-orders` | `page--my-orders` | Order history |
| UP-11 | Order Detail | `/my-orders/{order_id}` | `page--order-detail` | Order detail + invoice |
| UP-12 | My Membership | `/my-membership` | `page--my-membership` | Current membership + manage subscription |
| UP-13 | Checkout | `/checkout` | `page--checkout` | Cart → Coupon → Payment flow |
| UP-14 | Payment Success | `/checkout/success` | `page--payment-success` | Confirmation + next steps |
| UP-15 | Notifications | `/notifications` | `page--notifications` | Notification listing with filters |
| UP-16 | My Wishlist | `/my-wishlist` | `page--wishlist` | Saved courses |
| UP-17 | Forum | `/forum` | `page--forum` | Forum topic listing |
| UP-18 | Forum Topic | `/forum/{topic_id}` | `page--forum-topic` | Topic detail + threaded replies |
| UP-19 | Zoom Sessions | `/my-sessions` | `page--sessions` | Upcoming live sessions calendar |
| UP-20 | Learning Paths | `/learning-paths` | `page--learning-paths` | Browsable learning path listing |
| UP-21 | Learning Path Detail | `/learning-paths/{id}` | `page--learning-path` | Path timeline, courses, progress |
| UP-22 | Leaderboard | `/leaderboard` | `page--leaderboard` | XP rankings, top learners, badge showcase |
| UP-23 | My Notes | `/my-notes` | `page--my-notes` | All personal notes by course/lesson |

