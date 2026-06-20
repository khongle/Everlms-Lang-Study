# S03 — Topology

EverLMS là **single-region, single-AZ** monolithic deployment.
Không có multi-region, không có active-standby, không có load balancing cluster.

---

## Regions & Environments

| Environment | Region | AZ | Infrastructure |
|-------------|--------|----|----------------|
| Local Dev | N/A | N/A | DDEV (Docker) |
| Staging | Vietnam (proposed) | Single AZ | VPS hoặc Docker Compose |
| Production | Vietnam (proposed) | Single AZ | VPS hoặc k8s single-node |

> **Status: PROPOSED** — Production infrastructure chưa được provisioned.
> Target là VPS tại datacenter Việt Nam (để giảm latency cho users VN) hoặc k8s single-node.

---

## Deployment Diagram

```
[Browser / Mobile]
       |
       | HTTPS (443)
       v
[Nginx 1.24+]  <-- serves static files directly (public/sites/default/files)
       |
       | FastCGI (TCP 9000)
       v
[PHP-FPM 8.3]  <-- runs Drupal 11 application
       |
       +-----> [MariaDB 10.11]   (port 3306, same host or sidecar)
       |           [lms_* tables — 47+ tables]
       |
       +-----> [Redis]           (port 6379, planned — cache + session)
       |           Status: PROPOSED, currently using file cache
       |
       +-----> [File System]     (local disk)
                   /public  — user-uploaded files, CDN-served
                   /private — certificates, secure downloads
                   /scorm   — SCORM package storage

[Cron Host / Same VPS]
       |
       | PHP CLI (cron every 15 min)
       v
[Drupal Cron + Queue Worker]
       |
       +-----> [MariaDB 10.11]   (same DB, queue tables)
       +-----> [OpenAI API]      (outbound HTTPS)
       +-----> [Gemini API]      (outbound HTTPS)
       +-----> [Pollinations.ai] (outbound HTTPS)

[External Services — outbound only]
  Stripe API     → https://api.stripe.com
  PayPal API     → https://api.paypal.com     (PROPOSED)
  Zoom API v2    → https://api.zoom.us/v2
  OpenAI API     → https://api.openai.com/v1
  Gemini API     → https://generativelanguage.googleapis.com
  Pollinations   → https://image.pollinations.ai
  SMTP Server    → (configured externally)
```

---

## Network Notes

- Nginx terminates TLS; PHP-FPM runs on localhost only.
- MariaDB bound to localhost (or Docker network in DDEV).
- Redis (when added) bound to localhost.
- No VPC, no private subnet — single host model.
- Outbound calls to external APIs go via host's public IP.

---

## DNS / Domain

- Production domain: TBD (e.g., `everlms.vn`).
- Certificate: Let's Encrypt (Certbot) or managed cert from hosting provider.
- TTL for main A record: recommend 300s for flexibility during deploys.

---

## Scalability Notes

Current topology supports a single server. Vertical scaling (larger VPS) is the primary scale path.
Horizontal scaling would require:
1. Shared file system (NFS or S3) for `/public` and `/private`.
2. External Redis for shared session/cache.
3. MariaDB moved to dedicated host or managed DB service.
