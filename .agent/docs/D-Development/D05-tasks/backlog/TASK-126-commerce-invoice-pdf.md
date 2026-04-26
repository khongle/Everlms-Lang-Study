# Task: Invoice PDF Generation per Order

**ID:** TASK-126
**Unit:** U-020 (lms_order)
**Group:** G-COMMERCE (hardening)
**Priority:** P1
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Generate a PDF invoice for each completed order. The invoice includes seller business address, buyer details, VAT number, itemised line items, subtotal, VAT amount, and total in VND. The PDF is attached to the order confirmation email and available for download from the learner's order history page. Implementation uses `dompdf` or `mpdf` (final choice per PROTO-005 outcome).

## Context

- Module: `lms_order`
- Vietnamese legal requirement: VAT invoices (hoa don VAT) must include seller tax code, buyer name/address, item description, and VAT breakdown
- PDF stored as a managed file on the order entity; not regenerated on every request

## Acceptance Criteria

- [ ] PDF generated at order status transition to `paid`
- [ ] Invoice contains: seller info (name, address, tax code), buyer name + email + optional VAT number, order date, order ID, line items (course/membership name + quantity + unit price + VAT rate + VAT amount), subtotal, total VAT, grand total in VND
- [ ] PDF attached to order confirmation email as file attachment
- [ ] "Download Invoice" link available on learner order detail page (`/orders/{id}`)
- [ ] Admin can re-generate invoice from order edit page (e.g., after buyer provides VAT number)
- [ ] PDF filename: `invoice-{order_id}-{date}.pdf`
- [ ] PDF stored as Drupal managed file (`file_managed` table) referenced from order entity
- [ ] Seller business details configurable via admin settings form (`/admin/lms/settings/invoice`)
- [ ] VAT calculation: uses values from TASK-127 (tax-inclusive/exclusive logic)

## Technical Notes

- PDF library: `dompdf/dompdf` (Composer); use if PROTO-005 selects it, else `mpdf/mpdf`
- New service: `web/modules/custom/lms_order/src/InvoicePdfService.php`
- HTML template for PDF: `web/modules/custom/lms_order/templates/lms-invoice-pdf.html.twig`
- Trigger: `hook_entity_update` on `lms_order` entity when `field_order_status` transitions to `paid`
- Store file reference: `field_invoice_pdf` (file field) on order entity
- Settings schema: `lms_order.invoice_settings` (config object for seller details)

## Dependencies

- TASK-127 (VAT calculation logic)
- TASK-050 (commerce hardening)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
