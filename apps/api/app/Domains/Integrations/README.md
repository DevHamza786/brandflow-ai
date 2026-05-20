# Integrations Domain

Workspace-scoped OAuth integrations (LinkedIn first) with encrypted tokens, provider abstraction, and scheduler-ready refresh jobs.

## Table

`linkedin_integrations` — `provider` column supports future multi-platform providers.

## Repository contracts

- `LinkedInIntegrationRepositoryContract`
- `OAuthProviderContract` (implemented by `LinkedInOAuthProvider`)

## Services

- `LinkedInOAuthService` — connect / callback / disconnect orchestration
- `LinkedInTokenExchangeService` — authorization code → tokens
- `LinkedInTokenRefreshService` — refresh + expiry handling
- `LinkedInIntegrationLinkService` — upsert workspace integration rows
- `OAuthStateStore` — Redis CSRF state
- `IntegrationCredentialVault` — encryption audit helpers

## HTTP

| Method | Path | Notes |
|--------|------|-------|
| GET | `/api/v1/integrations/linkedin/connect` | Requires `X-Workspace-Id`; `?redirect=1` for browser redirect |
| GET | `/integrations/linkedin/callback` | LinkedIn OAuth callback (web route) |
| GET | `/api/v1/integrations/linkedin` | List workspace integrations |
| DELETE | `/api/v1/integrations/linkedin/{id}` | Disconnect |

## Jobs

- `RefreshLinkedInTokenJob` — single integration refresh
- `RefreshExpiringLinkedInTokensJob` — cron sweep (enqueue refreshes)

## LinkedIn scopes (env: `LINKEDIN_SCOPES`)

| Product enabled in Developer Portal | Typical scopes |
|-------------------------------------|----------------|
| Sign In with LinkedIn (OpenID) only | `openid`, `profile`, `email` |
| + Share on LinkedIn | add `w_member_social` |

Use comma-separated values in `.env`, e.g. `openid,profile,email,w_member_social`. If you see “scope is not valid”, the product is not added to the app or the app is still in a restricted state.

## Verify

```bash
php artisan migrate
php scripts/verify-linkedin-integration.php <workspace-uuid>
php artisan route:list | grep linkedin
docker compose exec postgres psql -U pbos -d pbos -c "\d linkedin_integrations"
```

## Related docs

- [PROJECT_ARCHITECTURE.md](../../../PROJECT_ARCHITECTURE.md) §7 LinkedIn Integration Flow
