# Task: Production Hosting Provider Selection & Environment Provisioning

**ID:** TASK-095
**Unit:** — (infra: cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P0
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Chọn nhà cung cấp hosting phù hợp cho EverLMS phục vụ khán giả Việt Nam. So sánh DigitalOcean, AWS (ap-southeast-1 Singapore), Vultr (SGP), và Linode/Akamai (SGP) dựa trên latency từ Hà Nội / TP.HCM, giá, SLA, và support. Kết quả: một ADR ghi lại quyết định + môi trường production được provisioned với 1× app server, 1× DB server (MariaDB 10.11), 1× Redis node.

Provisioning phải tuân theo security baseline: firewall (UFW/cloud security groups) chỉ mở cổng 22 (SSH từ IP whitelist), 80, 443. DB + Redis không expose ra public internet. Dùng DDEV config làm tham chiếu cho phiên bản PHP/MariaDB/Redis.

## Acceptance Criteria
- [ ] Benchmark latency từ ≥3 điểm ở VN (Hà Nội, TP.HCM, Đà Nẵng) tới từng provider (dùng `ping` + `mtr` + HTTP latency test)
- [ ] ADR được viết tại `.agent/docs/B-Architecture/B01-ADRs/ADR-XXX-prod-hosting-provider.md` với outcome rõ ràng
- [ ] 1× app server (≥4 vCPU, ≥8 GB RAM) provisioned với Ubuntu 22.04 LTS + Nginx + PHP 8.3-FPM
- [ ] 1× DB server (≥2 vCPU, ≥4 GB RAM) provisioned với MariaDB 10.11
- [ ] 1× Redis node (≥1 GB RAM) provisioned
- [ ] SSH key-based auth only — password auth disabled
- [ ] Firewall rules: app server chỉ nhận 80/443 public; DB/Redis chỉ nhận kết nối từ app server private IP
- [ ] `H01-infrastructure.md` updated với sơ đồ topology + IP ranges + instance specs
- [ ] Cost estimate (monthly) documented trong ADR

## Technical Notes
- PHP version phải match DDEV config: PHP 8.3
- MariaDB version: 10.11 (match local dev và CI — xem TASK-069)
- Redis version: ≥7.0
- Nên dùng managed DB nếu provider có (DigitalOcean Managed MySQL compatible w/ MariaDB, AWS RDS)
- Tham khảo `A07-disaster-recovery.md` cho RPO/RTO tier khi chọn storage class
- Security groups / UFW rules phải được committed vào IaC (Terraform hoặc Ansible playbook)
- Tham khảo `H01-infrastructure.md` cho format documentation

## Dependencies
- None (unblocks TASK-096, TASK-097, TASK-098, TASK-100, TASK-106)

## Definition of Done
- [ ] Code complete (IaC scripts committed)
- [ ] G1-G4 gates passed
- [ ] ADR committed và reviewed bởi SA
- [ ] `H01-infrastructure.md` updated với full topology
- [ ] Prod env accessible qua SSH từ team leads
- [ ] Cost estimate documented và approved
