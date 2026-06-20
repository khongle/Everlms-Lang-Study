# ADR-003 - Plugin hosts + strategy for extensibility

**Status:** Accepted (as-built) · **Date:** 2026-06-21

## Context
Payments, AI providers, and quiz grading vary and must be swappable without touching callers.

## Decision
Use **plugin hosts**: `@PaymentGateway` (lms_payment: bank_transfer/stripe/paypal/zalopay), `@AiProvider` (lms_ai: OpenAI/Gemini/Pollinations), and a **strategy factory** for quiz grading (`GradingStrategyFactory`, one strategy per question type). Cross-module side effects via **events** (`QuizPassedEvent`).

## Consequences
- Open/Closed: add a plugin, not a branch.
- Clear seams for testing (mock the plugin/provider).
- Slight indirection; plugin discovery overhead (negligible).
