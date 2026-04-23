# Feature: Mobile App

## 1. Tổng quan

Mobile app cơ bản (iOS & Android) built with **Flutter** hoặc **React Native**, cung cấp trải nghiệm học tập on-the-go. Kết nối với EverLMS backend qua REST API / JSON:API.

---

## 2. Core Mobile Features

### 2.1 Authentication
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| MA-01 | Login | Email/password + social login (Google, Facebook) |
| MA-02 | Registration | Email registration với verification |
| MA-03 | Biometric login | Fingerprint / Face ID |
| MA-04 | Token management | JWT / OAuth2 token refresh |
| MA-05 | Remember me | Persistent session |

### 2.2 Course Browsing
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| MB-01 | Course listing | Grid view với search & filters |
| MB-02 | Course detail | Full course info, chapters, lessons preview |
| MB-03 | Category browsing | Browse by category tree |
| MB-04 | Search | Full-text course search |
| MB-05 | Reviews | Xem ratings & reviews |

### 2.3 Learning Experience
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| ML-01 | Video playback | Native video player với controls |
| ML-02 | Text lessons | Rendered rich text content |
| ML-03 | Quiz taking | Full quiz taking experience (all 8 types) |
| ML-04 | Progress tracking | Real-time progress sync |
| ML-05 | Offline mode | Download lessons cho offline access |
| ML-06 | Resume learning | Continue from last position |
| ML-07 | Notes | Personal notes per lesson |

### 2.4 Profile & Account
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| MP-01 | Profile view/edit | Avatar, name, bio |
| MP-02 | My courses | Enrolled courses với progress |
| MP-03 | Certificates | View & download certificates |
| MP-04 | Notifications | Push notification center |
| MP-05 | Settings | Language, notification preferences |

### 2.5 Commerce
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| MC-01 | Course purchase | In-app purchase flow |
| MC-02 | Payment methods | Stripe / PayPal in-app |
| MC-03 | Order history | Xem lịch sử mua hàng |
| MC-04 | Subscription | Manage membership trong app |

---

## 3. Technical Requirements

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| MT-01 | API connection | REST API / JSON:API từ Drupal backend |
| MT-02 | Push notifications | Firebase Cloud Messaging (FCM) |
| MT-03 | Image caching | Efficient image loading & caching |
| MT-04 | Deep linking | URL scheme cho course/lesson links |
| MT-05 | App Store | Tuân thủ App Store / Google Play guidelines |
| MT-06 | Minimum OS | iOS 15+ / Android 10+ |
| MT-07 | Responsive | Hỗ trợ phone & tablet layouts |
| MT-08 | Performance | App startup < 3s, smooth scrolling 60fps |
