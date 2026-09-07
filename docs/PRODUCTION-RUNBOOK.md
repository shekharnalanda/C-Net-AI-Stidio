# C-Net AI Studio Production Runbook

## Release boundary

- Batch 7 output/export behavior remains backward compatible.
- Worker protocol remains V5 with per-device credentials; worker sources are not modified.
- Batch 8–11 migrations are additive and reversible.
- `.env`, stored media, database records and worker credentials are never synchronized from Git.

## Automated deployment

The cPanel deployment task copies the release while excluding `.env`, installs production dependencies, runs additive migrations, republishes the public directory, and rebuilds Laravel caches.

## Verification gates

1. `composer validate --strict`
2. `php artisan test`
3. `php artisan route:list`
4. `php artisan migrate:status`
5. HTTP 200: `/`, `/login`, `/api/v1/health`
6. Authenticated smoke checks: dashboard, AI Tools, Plan & Usage, projects and exports
7. Worker heartbeat and job claim remain V5/per-device

## Rollback

Deploy the previous Git commit. Do not roll back additive migrations while new application records exist. The new tables do not affect Batch 7 paths, so application rollback remains safe without destructive database actions.
