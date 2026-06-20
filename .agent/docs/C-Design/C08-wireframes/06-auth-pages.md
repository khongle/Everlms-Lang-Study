# Wireframe: Auth Pages

## Login Page

```
┌─────────────────────────────────────────────────────────────────────┐
│ HEADER (minimal: logo + back to home)                                │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│              ┌────────────────────────────────┐                      │
│              │        Welcome Back            │                      │
│              │                                │                      │
│              │ [Continue with Google]         │                      │
│              │ [Continue with Facebook]       │                      │
│              │                                │                      │
│              │ ────── or ──────               │                      │
│              │                                │                      │
│              │ Email                          │                      │
│              │ [________________________]     │                      │
│              │ Password                       │                      │
│              │ [________________________] 👁  │                      │
│              │                                │                      │
│              │ ☐ Remember me    Forgot PW?    │                      │
│              │                                │                      │
│              │ [        Log In         ]      │                      │
│              │                                │                      │
│              │ Don't have an account?         │                      │
│              │ [Register here]                │                      │
│              └────────────────────────────────┘                      │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

## Register Page

```
┌─────────────────────────────────────────────────────────────────────┐
│ HEADER                                                               │
├─────────────────────────────────────────────────────────────────────┤
│              ┌────────────────────────────────┐                      │
│              │       Create Account           │                      │
│              │                                │                      │
│              │ [Sign up with Google]          │                      │
│              │ [Sign up with Facebook]        │                      │
│              │                                │                      │
│              │ ────── or ──────               │                      │
│              │                                │                      │
│              │ Full Name                      │                      │
│              │ [________________________]     │                      │
│              │ Email                          │                      │
│              │ [________________________]     │                      │
│              │ Password                       │                      │
│              │ [________________________] 👁  │                      │
│              │ Confirm Password               │                      │
│              │ [________________________] 👁  │                      │
│              │                                │                      │
│              │ ☑ I agree to Terms & Privacy   │                      │
│              │                                │                      │
│              │ [      Create Account    ]     │                      │
│              │                                │                      │
│              │ Already have an account?       │                      │
│              │ [Log in here]                  │                      │
│              └────────────────────────────────┘                      │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Implementation status (as of 2026-06-20)

**as-built (core Drupal auth; social login is target-state).**

Login, register, and password-reset pages are served by Drupal core user module. Custom theme overrides exist: `page--user--login.html.twig`, `page--user--register.html.twig`, `page--user--password.html.twig`, `user-login-form.html.twig`, `user-register-form.html.twig` in `lms_theme`. The depicted centered card layout with dark glassmorphism styling is applied through these theme overrides.

Social login (depicted as "Continue with Google / Facebook") is **not implemented** — no OAuth2/social module is present. Password strength meter on register is **target-state**.

For the mobile app, OTP-based registration and login flows are implemented in `lms_mobile_api` (`MobileAuthController`) at `/api/v1/user/register`, `/api/v1/user/register/verify-otp`, `/api/v1/oauth/token`.

Related: [../C07-sitemap.md](../C07-sitemap.md)
