# EverLMS — Mobile App Specification

## 1. Tổng quan

Mobile app cung cấp trải nghiệm học tập di động cho EverLMS. Kết nối backend qua REST API.

> Chi tiết feature requirements → xem [by-features/11-mobile-app.md](../by-features/11-mobile-app.md)

---

## 2. Screen Map

### 2.1 Authentication Screens
| # | Screen | API Endpoint | Mô tả |
|---|--------|-------------|-------|
| S-01 | Splash | — | Logo + loading |
| S-02 | Onboarding | — | 3-slide intro (first launch) |
| S-03 | Login | `POST /api/user/login` | Email + password |
| S-04 | Social Login | `POST /api/user/social-login` | Google / Facebook |
| S-05 | Register | `POST /api/user/register` | Registration form |
| S-06 | Forgot Password | `POST /api/user/password-reset` | Email input |

### 2.2 Main Screens (Bottom Tab Bar)
| # | Screen | Tab | API Endpoint |
|---|--------|-----|-------------|
| S-10 | Home | 🏠 Home | `GET /api/courses/featured` |
| S-11 | Explore / Search | 🔍 Explore | `GET /api/courses?q=...` |
| S-12 | My Learning | 📚 My Learning | `GET /api/user/courses` |
| S-13 | Notifications | 🔔 Notifications | `GET /api/user/notifications` |
| S-14 | Profile | 👤 Profile | `GET /api/user/profile` |

### 2.3 Course Screens
| # | Screen | API Endpoint |
|---|--------|-------------|
| S-20 | Course Detail | `GET /api/courses/{id}` |
| S-21 | Course Curriculum | `GET /api/courses/{id}/curriculum` |
| S-22 | Course Reviews | `GET /api/courses/{id}/reviews` |
| S-23 | Course Instructor | `GET /api/users/{uid}/profile` |

### 2.4 Learning Screens
| # | Screen | API Endpoint |
|---|--------|-------------|
| S-30 | Lesson View | `GET /api/lessons/{id}` |
| S-31 | Video Player | — (native player) |
| S-32 | Text Lesson | `GET /api/lessons/{id}/content` |
| S-33 | Quiz Taking | `GET /api/quizzes/{id}/questions` |
| S-34 | Quiz Result | `GET /api/quiz-results/{id}` |

### 2.5 Profile Screens
| # | Screen | API Endpoint |
|---|--------|-------------|
| S-40 | Edit Profile | `PUT /api/user/profile` |
| S-41 | My Certificates | `GET /api/user/certificates` |
| S-42 | Certificate View | `GET /api/certificates/{id}` |
| S-43 | My Orders | `GET /api/user/orders` |
| S-44 | Settings | — (local) |

### 2.6 Commerce Screens
| # | Screen | API Endpoint |
|---|--------|-------------|
| S-50 | Cart | — (local state) |
| S-51 | Checkout | `POST /api/orders` |
| S-52 | Payment | Stripe/PayPal SDK |
| S-53 | Success | — |
| S-54 | Membership | `GET /api/user/membership` |

---

## 3. API Requirements

### 3.1 Authentication
| Method | Endpoint | Request | Response |
|--------|----------|---------|----------|
| POST | `/api/user/login` | `{email, password}` | `{token, user}` |
| POST | `/api/user/register` | `{name, email, password}` | `{token, user}` |
| POST | `/api/user/social-login` | `{provider, token}` | `{token, user}` |
| POST | `/api/user/refresh-token` | `{refresh_token}` | `{token}` |
| POST | `/api/user/password-reset` | `{email}` | `{message}` |

### 3.2 Courses
| Method | Endpoint | Mô tả |
|--------|----------|-------|
| GET | `/api/courses` | List (filters: category, level, q, sort) |
| GET | `/api/courses/featured` | Featured courses |
| GET | `/api/courses/{id}` | Course detail |
| GET | `/api/courses/{id}/curriculum` | Chapters + lessons |
| GET | `/api/courses/{id}/reviews` | Reviews |
| POST | `/api/courses/{id}/enroll` | Enroll |

### 3.3 Learning
| Method | Endpoint | Mô tả |
|--------|----------|-------|
| GET | `/api/user/courses` | My enrolled courses |
| GET | `/api/lessons/{id}` | Lesson content |
| POST | `/api/lessons/{id}/complete` | Mark complete |
| POST | `/api/lessons/{id}/progress` | Update progress |
| GET | `/api/quizzes/{id}/questions` | Quiz questions |
| POST | `/api/quizzes/{id}/submit` | Submit quiz |
| GET | `/api/quiz-results/{id}` | Quiz result detail |

### 3.4 User
| Method | Endpoint | Mô tả |
|--------|----------|-------|
| GET | `/api/user/profile` | My profile |
| PUT | `/api/user/profile` | Update profile |
| GET | `/api/user/certificates` | My certificates |
| GET | `/api/user/notifications` | Notifications |
| PUT | `/api/user/notifications/{id}/read` | Mark as read |
| GET | `/api/user/orders` | Order history |
| GET | `/api/user/membership` | Membership info |

---

## 4. Push Notification Types

| # | Event | Title | Body |
|---|-------|-------|------|
| PN-01 | Enrollment | "Welcome!" | "You enrolled in {course}" |
| PN-02 | Quiz Result | "Quiz Complete" | "Score: {score}% on {quiz}" |
| PN-03 | Certificate | "Certificate Earned 🎉" | "Download your certificate" |
| PN-04 | Live Session | "Live in 15 min" | "{course} live session starting" |
| PN-05 | Inactive Remind | "Continue Learning" | "Continue {course}?" |
| PN-06 | Payment | "Payment Received" | "Order #{id} confirmed" |
