---
name: documentation
description: Documentation standards, knowledge management, and keeping docs in sync with code. Use when writing documentation, updating README, creating API docs, managing changelogs, or cleaning up outdated documentation.
---

# Documentation Skill

## Workflow

1. **Identify** → What needs documentation?
2. **Template** → Use appropriate template
3. **Write** → Clear, concise, examples
4. **Update** → Keep related docs in sync
5. **Clean** → Remove completed TODOs

## Documentation Types

### Code Documentation
- **Inline comments**: Why, not what
- **PHPDoc/JSDoc**: Public APIs only
- **README**: Setup and usage

### Project Documentation
- **Feature specs**: `docs/features/`
- **Architecture**: `docs/architecture/`
- **Decisions**: `docs/decisions/`
- **API docs**: `docs/api/`

## Writing Standards

### Be Concise
```markdown
# Bad
The user registration process allows new users to create accounts
by submitting their personal information through a form.

# Good
Create user accounts via registration form.
```

### Include Examples
```markdown
## API Usage

```bash
curl -X POST https://api.example.com/users \
  -H "Content-Type: application/json" \
  -d '{"name": "John", "email": "john@example.com"}'
```
```

### Keep Current
- Update with every feature
- Remove obsolete sections
- Version API documentation
- Date last update

## README Template

```markdown
# Project Name

Brief description.

## Quick Start

\`\`\`bash
git clone ...
docker-compose up -d
\`\`\`

## Development

Prerequisites and setup instructions.

## Testing

How to run tests.

## Deployment

How to deploy.

## Contributing

Guidelines for contributors.
```

## API Documentation

Use OpenAPI 3.0 format:
```yaml
openapi: 3.0.0
info:
  title: API Name
  version: 1.0.0
paths:
  /users:
    post:
      summary: Create user
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/CreateUser'
      responses:
        '201':
          description: Created
```

## Changelog Format

```markdown
# Changelog

## [1.2.0] - 2024-01-15

### Added
- User registration feature

### Changed
- Updated authentication flow

### Fixed
- Login redirect issue

### Removed
- Deprecated v1 endpoints
```

## Documentation Checklist

When completing a feature:
- [ ] Feature spec updated/closed
- [ ] API docs updated
- [ ] README reflects changes
- [ ] Changelog entry added
- [ ] TODOs removed from CLAUDE.md
- [ ] ADR created if architectural changes

## Sync with CLAUDE.md

After every significant change:
1. Move completed tasks to "Completed" section
2. Add new tasks discovered
3. Update "Last updated" date
4. Keep sprint scope accurate

## References
- Templates: `references/templates/`
- Style guide: `references/style-guide.md`
