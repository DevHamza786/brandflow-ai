# Experimentation Engine

Scientific A/B infrastructure for BrandFlow AI — assign, observe, compare, optimize.

## Tables

- `experiments` — workspace experiments with optional links to optimization loops, workflow blueprints, coordinations
- `experiment_variants` — control/challenger arms with traffic weights
- `experiment_results` — assignments, observations, comparisons (idempotent)

## Core services

| Service | Role |
|---------|------|
| `ExperimentationEngine` | Orchestrates assign → observe → compare |
| `VariantAssignmentEngine` | Sticky weighted assignment |
| `ExperimentScoringEngine` | Aggregates engagement metrics |
| `StatisticalComparisonEngine` | Lift % + confidence narratives |

## API

- `GET /api/v1/experiments`
- `GET /api/v1/experiments/{id}`
- `POST /api/v1/experiments/assign`
- `POST /api/v1/experiments/{id}/compare`
- `POST /api/v1/experiments/demo-cycle`

Config: `config/experimentation.php` — templates for hook_ab, cta, posting_time.
