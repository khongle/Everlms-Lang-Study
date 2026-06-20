# INT-003 - ZaloPay

## Requirement
Offer the Vietnamese wallet **ZaloPay** as a checkout option via the `@PaymentGateway` plugin `zalopay`, for local-market buyers.

## Participating modules
[lms_payment](../../C-Design/C05-modules/lms_payment.md)

## Contract expectations
1. **Auth/authz:** ZaloPay app id / key (config YAML today — R-001); server-side.
2. **Failure/retry/fallback:** failed flow leaves order `pending`; retry/switch gateway.
3. **CI test fixture/stub:** ZaloPay sandbox creds + mocked client.
4. **Operational owner + alerting:** Commerce owner. ⚠️ **Stub** — gateway is recognised but not production-complete; documented in [E06](../../E-Quality/E06-implementation-status/_index.md).

## Design link
[C06 INT-003](../../C-Design/C06-apis/INT-003-zalopay.md)
