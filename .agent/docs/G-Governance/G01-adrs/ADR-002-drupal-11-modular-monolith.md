# ADR-002 - Drupal 11 modular monolith with custom entities

**Status:** Accepted (as-built) · **Date:** 2026-06-21

## Context
The LMS domain (courses, quizzes, orders, certificates) needs precise schemas, relationships, and access rules.

## Decision
Build as a **modular monolith on Drupal 11**: one install + DB, ~28 `lms_*` modules, each owning **custom content/config entities** (not nodes), fields as base fields in code. No microservices; no Drupal Commerce.

## Consequences
- Shared-DB transactional consistency (order→enrollment→certificate); simple ops.
- Precise domain modelling; 29 entities.
- Single-deploy coupling; scaling is vertical/horizontal-Drupal, not per-service.
