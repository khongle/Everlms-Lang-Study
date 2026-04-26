# Task: e-Invoicing (Hóa Đơn Điện Tử) per Decree 123/2020

**ID:** TASK-154
**Unit:** new (raise)
**Group:** G-COMMERCE
**Priority:** P1
**Estimate:** 24h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Triển khai module `lms_einvoice` để phát hành hóa đơn điện tử (HĐĐT) tuân thủ Nghị định 123/2020/NĐ-CP và Thông tư 78/2021/TT-BTC của Bộ Tài chính. Hóa đơn phải được ký số (digital signature) và gửi lên Tổng cục Thuế (TCT / GDT) qua cổng kết nối. Tích hợp với một trong các nhà cung cấp HĐĐT được cấp phép: VNPT-Invoice, Misa meInvoice, hoặc Easy Invoice (EasyInvoice).

Luồng nghiệp vụ: đơn hàng chuyển sang trạng thái PAID → `InvoiceIssuanceSubscriber` trigger → gọi API nhà cung cấp HĐĐT → nhận về mã CKS (chữ ký số) + `invNo` (số hóa đơn) → lưu vào `lms_einvoice` entity → gửi email PDF cho khách hàng. Trường hợp lỗi: retry queue với exponential backoff, alert admin sau 3 lần thất bại.

## Acceptance Criteria

- [ ] Module `lms_einvoice` tại `web/modules/custom/lms_einvoice/` với `lms_einvoice.info.yml`
- [ ] Config entity `InvoiceProvider` lưu endpoint, username, password, template_no, serial_no, tax_code của merchant
- [ ] Content entity `LmsEinvoice`: fields `order_id`, `inv_no`, `inv_date`, `buyer_name`, `buyer_tax_code`, `total_amount`, `vat_amount`, `pdf_url`, `status`
- [ ] `InvoiceService::issueFromOrder(Order $order)` — build XML/JSON payload theo schema nhà cung cấp chọn
- [ ] Event subscriber `InvoiceIssuanceSubscriber` lắng nghe `OrderPaidEvent` → queue invoice job
- [ ] Queue worker `InvoiceIssueWorker`: gọi API, retry tối đa 3 lần với backoff 5 min / 30 min / 2h
- [ ] Admin nhận email alert nếu invoice phát hành thất bại sau 3 lần retry
- [ ] Admin UI: danh sách HĐĐT, filter by date/status, download PDF, button "Phát hành lại"
- [ ] PDF invoice link gửi kèm email xác nhận đơn hàng
- [ ] Unit test: `InvoicePayloadBuilderTest` — verify XML output đúng field mapping
- [ ] Hỗ trợ cả hóa đơn VAT 10% và 0% (phối hợp TASK-127)

## Technical Notes

- Module path: `web/modules/custom/lms_einvoice/`
- Provider interface: `src/InvoiceProviderInterface.php` — implementations trong `src/Provider/VnptInvoiceProvider.php`, `MisaProvider.php`, `EasyInvoiceProvider.php`
- VNPT-Invoice WSDL/REST endpoint: cần đăng ký tài khoản sandbox VNPT
- XML namespace: `<TTChung>` + `<DSHDon>` theo Phụ lục I, TT 78/2021
- Config form: chọn provider (select), nhập credentials, test connection button
- Buyer tax code lấy từ TASK-155 `field_tax_id` trên order
- Invoice số format: `{Serial}/{Counter}` — auto-increment per provider config
- Coordinate: TASK-127 (VAT), TASK-155 (tax ID), TASK-126 (PDF invoice)

## Dependencies

- TASK-127 (VAT calculation)
- TASK-155 (VN business tax ID)
- TASK-050 (idempotency / retry pattern)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
