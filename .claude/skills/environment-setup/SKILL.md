---
name: environment-setup
description: Docker configuration for development, testing, and production environments. Use when setting up local development, configuring Docker Compose V2, adding new services (Redis, Elasticsearch, etc.), or preparing deployment environments.
version: Docker Compose V2, PHP 8.3+, Node 22 LTS
---

# Environment Setup Skill

## Version Requirements
- **Docker**: 24+ with Compose V2
- **PHP**: 8.3+ (8.4 recommended)
- **Node.js**: 22 LTS
- **PostgreSQL**: 17
- **Redis**: 7.4
- **nginx**: 1.27

## Workflow

1. **Determine Environment** → dev, test, or prod
2. **Configure Services** → Docker Compose V2 setup (no version field needed)
3. **Set Variables** → Environment configuration
4. **Initialize** → Database, cache, queues
5. **Verify** → Health checks and connectivity

## Environment Structure

```
docker/
├── development/
│   ├── Dockerfile.backend
│   ├── Dockerfile.frontend
│   ├── docker-compose.yml
│   └── .env.example
├── testing/
│   ├── Dockerfile.backend
│   ├── docker-compose.yml
│   └── .env.testing
└── production/
    ├── Dockerfile.backend
    ├── Dockerfile.frontend
    ├── docker-compose.yml
    └── .env.production
```

## Service Templates

### Development Stack
- PHP 8.3 with Laravel 12
- Node 22 LTS with React 19
- PostgreSQL 17
- Redis 7.4
- Mailpit (modern email testing, replaces Mailhog)

### Testing Stack
- Same as dev, isolated network
- Test database (wiped between runs)
- Mocked external services

### Production Stack
- Multi-stage builds (smaller images)
- No dev dependencies
- Health checks enabled
- Log aggregation ready
- OPcache optimized for PHP 8.3+

## Common Services

Add these as needed:
- **Redis**: Caching, sessions, queues
- **Elasticsearch**: Search functionality
- **MinIO**: S3-compatible storage
- **RabbitMQ**: Message queue

## Best Practices

1. **Never commit secrets** → Use `.env` files (gitignored)
2. **Pin versions** → Avoid `latest` tags (use `postgres:17-alpine`, not `postgres:latest`)
3. **Health checks** → All services should have them
4. **Named volumes** → Persist data properly
5. **Network isolation** → Each env has own network
6. **No version field** → Docker Compose V2 deprecated the `version` field
7. **Alpine images** → Use `-alpine` variants for smaller images

## Quick Start Commands

```bash
# Development
cd docker/development && docker-compose up -d

# Testing
cd docker/testing && docker-compose up -d

# Stop all
docker-compose down
```

## References
- Docker templates: `assets/docker/`
- Environment variables: `references/env-vars.md`
