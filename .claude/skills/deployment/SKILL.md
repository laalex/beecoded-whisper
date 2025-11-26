---
name: deployment
description: CI/CD pipeline configuration, deployment workflows, and production releases. Use when setting up GitHub Actions, configuring deployment pipelines, managing environments, or releasing to production.
version: GitHub Actions, PostgreSQL 17, PHP 8.3+, Node 22 LTS
---

# Deployment Skill

## Pipeline Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        CI/CD PIPELINE                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────┐   ┌──────┐   ┌───────┐   ┌─────────┐   ┌──────────┐ │
│  │ Push │──▶│ Lint │──▶│ Test  │──▶│  Build  │──▶│  Deploy  │ │
│  └──────┘   └──────┘   └───────┘   └─────────┘   └──────────┘ │
│                                          │                     │
│                                          ▼                     │
│                              ┌─────────────────────┐           │
│                              │    Staging          │           │
│                              └──────────┬──────────┘           │
│                                         │ E2E Tests            │
│                                         ▼                      │
│                              ┌─────────────────────┐           │
│                              │   Production        │           │
│                              │   (manual approval) │           │
│                              └─────────────────────┘           │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

## GitHub Actions Workflow

### CI Workflow (`.github/workflows/ci.yml`)
Triggered on: Push to any branch, PRs

```yaml
name: CI

on:
  push:
    branches: ['**']
  pull_request:
    branches: [main, develop]

jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: pdo, pdo_pgsql, redis
      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: '22'
          cache: 'npm'
      - name: Install Dependencies
        run: |
          composer install --no-interaction
          npm ci
      - name: Lint Backend
        run: composer lint
      - name: Lint Frontend
        run: npm run lint

  test:
    needs: lint
    runs-on: ubuntu-latest
    services:
      postgres:
        image: postgres:17-alpine
        env:
          POSTGRES_PASSWORD: testing
          POSTGRES_DB: testing
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5
      redis:
        image: redis:7.4-alpine
        options: >-
          --health-cmd "redis-cli ping"
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: pdo, pdo_pgsql, redis
          coverage: xdebug
      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: '22'
          cache: 'npm'
      - name: Install Dependencies
        run: |
          composer install --no-interaction
          npm ci
      - name: Backend Tests
        run: php artisan test --coverage
        env:
          DB_CONNECTION: pgsql
          DB_HOST: localhost
          DB_DATABASE: testing
          DB_PASSWORD: testing
      - name: Frontend Tests
        run: npm run test:coverage
```

### CD Workflow (`.github/workflows/deploy.yml`)
Triggered on: Merge to main/develop

```yaml
name: Deploy

on:
  push:
    branches: [main, develop]

jobs:
  deploy-staging:
    if: github.ref == 'refs/heads/develop'
    runs-on: ubuntu-latest
    environment: staging
    steps:
      - name: Deploy to Staging
        run: ./deploy.sh staging

  deploy-production:
    if: github.ref == 'refs/heads/main'
    runs-on: ubuntu-latest
    environment: production
    steps:
      - name: Deploy to Production
        run: ./deploy.sh production
```

## Environment Strategy

| Branch | Environment | Deploy | Approval |
|--------|-------------|--------|----------|
| `feature/*` | None | No | - |
| `develop` | Staging | Auto | No |
| `main` | Production | Auto | Yes |

## Deployment Checklist

### Before Deploy
- [ ] All tests pass
- [ ] Code reviewed
- [ ] No security vulnerabilities
- [ ] Database migrations ready
- [ ] Environment variables set

### During Deploy
- [ ] Zero-downtime deployment
- [ ] Run migrations
- [ ] Clear caches
- [ ] Warm caches

### After Deploy
- [ ] Health check passes
- [ ] Smoke tests pass
- [ ] Monitor error rates
- [ ] Monitor performance

## Rollback Strategy

```bash
# Quick rollback to previous version
./deploy.sh rollback

# Rollback to specific version
./deploy.sh rollback v1.2.3
```

## Secret Management

- Use GitHub Secrets for CI/CD
- Use vault/SSM for runtime secrets
- Never commit secrets
- Rotate regularly

```yaml
# Using secrets in GitHub Actions
env:
  DATABASE_URL: ${{ secrets.DATABASE_URL }}
  API_KEY: ${{ secrets.API_KEY }}
```

## Health Checks

```php
// Laravel health endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'database' => DB::connection()->getPdo() ? 'connected' : 'error',
        'cache' => Cache::has('health') || Cache::put('health', true) ? 'working' : 'error',
    ]);
});
```

## Monitoring

Post-deploy monitoring:
- Error tracking (Sentry)
- APM (New Relic/Datadog)
- Logs (CloudWatch/ELK)
- Uptime (Pingdom/UptimeRobot)

## References
- Workflow templates: `assets/github-workflows/`
- Deploy scripts: `scripts/deploy.sh`
