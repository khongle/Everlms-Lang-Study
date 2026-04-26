# Task: HTTP/3 + QUIC Support

**ID:** TASK-217
**Unit:** — (Nginx + CloudFlare infra)
**Group:** G-PERFORMANCE
**Priority:** P2
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Enable HTTP/3 (QUIC) on the EverLMS Nginx server (TASK-103) and verify CloudFlare HTTP/3 pass-through (TASK-097). HTTP/3 improves latency and throughput on mobile and lossy networks by eliminating TCP head-of-line blocking. Deliverable is a verified configuration with a how-to guide committed to the infra docs.

## Acceptance Criteria

- [ ] **Nginx HTTP/3:**
  - [ ] Nginx compiled with `--with-http_v3_module` or OpenResty equivalent; verify with `nginx -V | grep http_v3`
  - [ ] `listen 443 quic reuseport;` added alongside existing `listen 443 ssl http2;`
  - [ ] `add_header Alt-Svc 'h3=":443"; ma=86400';` response header added (advertises HTTP/3 to clients)
  - [ ] UDP port 443 open in firewall/security group rules
- [ ] **CloudFlare (TASK-097):**
  - [ ] CloudFlare "HTTP/3 (with QUIC)" toggle enabled in Speed → Protocols dashboard
  - [ ] `CF-Ray` header response verified; `curl --http3 https://{SITE_DOMAIN}/` returns 200
- [ ] **TLS requirements:**
  - [ ] TLS 1.3 enabled on Nginx (required by QUIC spec)
  - [ ] `ssl_protocols TLSv1.3;` (TLS 1.2 retained for fallback on non-QUIC connections)
- [ ] **Verification:**
  - [ ] `curl --http3 https://{SITE_DOMAIN}/` → HTTP/3 response
  - [ ] Browser Network tab (Chrome → Protocol column): shows `h3` for main document requests
  - [ ] HTTP/2 fallback still works when QUIC blocked (verify with firewall UDP block simulation)
- [ ] **Documentation:**
  - [ ] New file `H07-observability-as-code/http3-quic-setup.md`: Nginx config snippet, firewall rules, CloudFlare toggle, verification commands
  - [ ] Nginx config diff committed to `.agent/infra/nginx/`
- [ ] No PHP or Drupal application code changes required

## Technical Notes

- Nginx QUIC support: available in Nginx mainline ≥ 1.25.0 (built with BoringSSL or QuicTLS); check DDEV nginx image version
- If DDEV nginx image does not support HTTP/3, document as "production-only" step and use CloudFlare as HTTP/3 termination point (CF → origin over HTTP/2 is acceptable)
- DDEV local: HTTP/3 not required in dev; configure only for staging/prod
- CloudFlare → origin: CloudFlare terminates QUIC client-side; CloudFlare → origin can remain HTTP/2 (standard practice)
- Firewall: UDP 443 must be allowed (many firewalls only allow TCP 443 by default)
- `Alt-Svc` header: also add via CloudFlare `Transform Rules` as backup if Nginx header is stripped

## Dependencies

- TASK-103 (Nginx configuration — base nginx.conf that this task modifies)
- TASK-097 (CloudFlare — HTTP/3 toggle + certificate termination)

## Definition of Done

- [ ] HTTP/3 enabled and verified on staging + production
- [ ] `curl --http3` returns 200 from staging URL
- [ ] HTTP/2 fallback confirmed working
- [ ] `http3-quic-setup.md` committed to `H07-observability-as-code/`
- [ ] G1-G4 gates passed (infra config review)
- [ ] No new debt introduced
