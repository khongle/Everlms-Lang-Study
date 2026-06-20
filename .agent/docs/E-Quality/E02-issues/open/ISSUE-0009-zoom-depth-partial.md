# ISSUE-0009 - Zoom attendance/recording partial

**Severity:** P2 · **Status:** open · **Story:** [E08S07](../../../D-Development/D03-stories/E08S07-lms_zoom.md)

## Description
`lms_zoom` supports meeting scheduling + join links, but attendance tracking and recording handling appear partial/incomplete.

## Impact
Compliance training that requires verified live attendance can't fully rely on Zoom data.

## Fix
Confirm Zoom API scope; implement attendance capture + recording links; add tests with a mocked Zoom client.
