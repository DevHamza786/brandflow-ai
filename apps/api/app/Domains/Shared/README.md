# Shared Domain Kernel

Cross-cutting primitives used by all bounded contexts.

## Contents

| Path | Purpose |
|------|---------|
| `Contracts/` | Base repository contracts |
| `Data/` | Abstract DTO base class |
| `Jobs/` | `BaseQueueJob` for Redis queue conventions |
| `Providers/` | Abstract `DomainServiceProvider` |

## Rules

- No business logic in this namespace.
- Domains may depend on `Shared`; `Shared` must not depend on other domains.
