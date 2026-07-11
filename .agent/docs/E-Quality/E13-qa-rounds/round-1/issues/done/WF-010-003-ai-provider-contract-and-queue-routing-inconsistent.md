---
id: WF-010-003
workflow: WF-010
title: AI provider interface and queue worker do not share the same provider contract
severity: medium
category: architecture
status: done
roles: instructor, content_editor
source: code review
found: 2026-06-30
---

# WF-010-003 - Provider contract and queue routing inconsistent

## Problem
The queue worker calls `generateText()` on AI provider plugins, and the provider
classes implement that method, but `AiProviderInterface` does not declare it.
The queue worker also hardcodes `openai`, so content generation can diverge from
the provider used for draft generation.

## Evidence
- `CourseContentGenerator::processItem()` calls `generateText()`.
- `OpenAiProvider`, `GeminiProvider`, and `PollinationsProvider` implement
  `generateText()`.
- `AiProviderInterface` only declares `generateJson()` and `generateImage()`.
- `CourseContentGenerator` hardcodes `createInstance('openai')`.

## Impact
Provider plugins have an implicit contract instead of an enforced one, and the
async lesson generation step may route to a different provider than the editor
selected.

## Expected
The provider interface declares text generation, and queue items carry the
provider used for the reviewed draft with a config-default fallback.

## Fix Plan
- Add `generateText()` to `AiProviderInterface`.
- Pass `provider_id` into content-generation queue items.
- Make `CourseContentGenerator` use the queued provider or configured default.

## Resolution
- `AiProviderInterface` now declares `generateText()`.
- Course draft save queues lesson generation with the selected `provider_id`.
- `CourseContentGenerator` uses the queued provider, with configured default and
  `openai` fallback.

## Verification
- PHP lint passed for `AiProviderInterface`,
  `CourseContentGenerator`, `CourseBuilderForm`, and `CourseBuilderService`.
- WF-010 final course `138` queued generated text lessons after save.
