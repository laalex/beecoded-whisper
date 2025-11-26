# Task Estimation Guide

## Time Estimates

| Size | Hours | Description |
|------|-------|-------------|
| XS | 0.5-1 | Trivial change, config update |
| S | 1-2 | Simple feature, single file |
| M | 2-4 | Multiple files, some complexity |
| L | 4-8 | Complex feature, multiple components |
| XL | 8+ | Break down further |

## Complexity Factors

Add time for:
- **New technology**: +50%
- **Integration with external API**: +25%
- **Database migration**: +1h
- **UI/UX complexity**: +25-50%
- **Security considerations**: +25%

## Red Flags (Break Down Further)

- Task takes more than 1 day
- Unclear requirements
- Multiple "and" in description
- Touches more than 3 services
- Requires research

## Example Breakdowns

**Bad**: "Implement user authentication"
**Good**:
- Create User model and migration (2h)
- Implement registration endpoint (2h)
- Implement login endpoint (2h)
- Add JWT token handling (2h)
- Create auth middleware (1h)
- Write tests (2h)
