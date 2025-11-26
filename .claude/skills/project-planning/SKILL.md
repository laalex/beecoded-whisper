---
name: project-planning
description: Feature research, requirements gathering, and specification writing. Use when starting a new feature, researching implementation approaches, creating feature specs, defining acceptance criteria, or breaking down work into tasks.
---

# Project Planning Skill

## Workflow

1. **Understand Request** → Clarify what the user wants to build
2. **Research** → Find similar implementations, best practices
3. **Specify** → Create detailed feature specification
4. **Decompose** → Break into implementable tasks

## Feature Specification Process

### Step 1: Requirements Gathering
Ask clarifying questions:
- What problem does this solve?
- Who are the users?
- What are must-have vs nice-to-have features?
- Are there existing similar features to reference?

### Step 2: Create Feature Spec
Create file in `docs/features/FEATURE-NAME.md` using template in `references/feature-template.md`

### Step 3: Define Acceptance Criteria
Write testable criteria using Given-When-Then format:
```
Given [context]
When [action]
Then [expected result]
```

### Step 4: Task Breakdown
Split feature into tasks that can be completed in 1-4 hours:
- Each task should be independently testable
- Order by dependencies
- Identify blockers early

## Research Guidelines

When researching implementations:
1. Check official documentation first
2. Look for proven patterns in production systems
3. Consider scalability implications
4. Document trade-offs in ADR format

## Output Checklist

- [ ] Feature spec created in `docs/features/`
- [ ] Acceptance criteria defined
- [ ] Tasks broken down and estimated
- [ ] Dependencies identified
- [ ] CLAUDE.md updated with new tasks

## References
- Feature template: `references/feature-template.md`
- Task estimation guide: `references/estimation.md`
