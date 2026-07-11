# WF-003-002: Certificate delivery UI and PDF layout need polish

## Status
Done

## Workflow
WF-003 - Certificate issuance

## Evidence
- Screenshot: `.agent/docs/E-Quality/E00-workflows/round-1/_screens/003-certificate-issuance/005-student-certificates-after-issue.png`
- Screenshot: `.agent/docs/E-Quality/E00-workflows/round-1/_screens/003-certificate-issuance/006-student-certificate-pdf-opened.png`
- Screenshot: `.agent/docs/E-Quality/E00-workflows/round-1/_screens/003-certificate-issuance/009-public-verify-valid-result.png`

## Problem
- The learner certificate card preview collapses into a single inline strip instead of a structured certificate preview.
- The preview says `EverLMS` while the live product brand is `SilverCare`.
- The public verification form renders too bare because the form does not carry the CSS hook used by the existing page fixes.
- The generated PDF date underline crosses the date text.

## Impact
The certificate is functionally issued and verifiable, but the delivery experience feels unfinished and less trustworthy for a credential workflow.

## Expected
- Certificate card preview is structured, scannable, and branded as SilverCare.
- Verification form uses the project visual system.
- PDF date/signature footer has clean line placement with no text overlap.

## Resolution Plan
- Add a scoped layout block to the learner certificates template.
- Change the preview brand copy to SilverCare.
- Add the `lms-certificate-verification-form` class in the verification form.
- Update the active certificate template layout so the date divider does not overlap text.
- Rebuild cache, regenerate the PDF, and recapture WF-003 screenshots.

## Resolution
- Added scoped layout CSS to `lms-my-certificates.html.twig` so the certificate card preview renders as a structured credential.
- Changed learner preview brand copy from `EverLMS` to `SilverCare`.
- Added an attached `lms_certificate/verification` library for the public verification form.
- Added `lms_certificate_update_10003` to adjust the active PDF template footer.
- Regenerated the issued PDF for certificate `52`.

## Retest
- Recaptured `/my-certificates`, PDF render, and public verify screenshots.
- Visual check confirms the card preview is structured, the verify page is styled as a centered card, and the PDF date no longer has a divider through the text.
