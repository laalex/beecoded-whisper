# Claude Code Autonomous Development Pipeline

A boilerplate that enables Claude Code to operate autonomously - just provide business requirements and watch it handle planning, architecture, TDD, implementation, and documentation automatically.

## 🎯 Key Difference: Autonomous Mode

Unlike manual workflows where you invoke each command, this boilerplate includes an **Autonomous Workflow Engine** that:

1. **Classifies** your request automatically
2. **Loads** relevant skills without prompting
3. **Executes** the full workflow (spec → architecture → TDD → implement → docs)
4. **Enforces** quality gates automatically
5. **Reports** progress and results

## Quick Start

1. Extract to your project folder
2. Customize `CLAUDE.md` with project details
3. Use `KICKOFF.md` for initial setup
4. Then just describe what you want built!

## Commands

| Command | Purpose | Example |
|---------|---------|---------|
| `/do [request]` | **Main orchestrator** - handles any request | `/do add user registration with email verification` |
| `/build [feature]` | Build complete feature | `/build invoice management system` |
| `/fix [bug]` | Fix with TDD | `/fix login fails with special characters` |
| `/improve [target]` | Refactor safely | `/improve UserService is too large` |

## Example Usage

After setup, just describe what you need:

```
/do Users should be able to create teams and invite members via email
```

Claude will automatically:
- ✅ Create feature spec
- ✅ Design API endpoints
- ✅ Plan database schema
- ✅ Write tests first
- ✅ Implement backend
- ✅ Implement frontend
- ✅ Run all tests
- ✅ Check file sizes
- ✅ Update documentation

## How It Works

The `CLAUDE.md` contains a **Decision Engine** that:

```
INPUT (Business Request)
        ↓
   CLASSIFY (new feature? bug? refactor?)
        ↓
   LOAD SKILLS (automatically)
        ↓
   EXECUTE PHASES
   ├── Specification
   ├── Architecture
   ├── Tests First (TDD)
   ├── Implementation
   ├── Quality Check
   └── Documentation
        ↓
   OUTPUT (Complete feature + report)
```

## Enforced Rules

These are **non-negotiable** in autonomous mode:

| Rule | Enforcement |
|------|-------------|
| TDD | Cannot implement without tests first |
| File Size | Auto-split if > 300 lines |
| Documentation | Auto-update after every task |
| Quality | Must pass all checks before "complete" |

## Project Structure

```
├── CLAUDE.md              # Autonomous workflow engine
├── KICKOFF.md             # Project initialization
├── .claude/
│   ├── commands/          # Orchestrator commands
│   │   ├── do.md          # Main autonomous command
│   │   ├── build.md       # Feature builder
│   │   ├── fix.md         # Bug fixer
│   │   └── improve.md     # Refactoring
│   ├── settings.toml      # Behavior configuration
│   └── skills/            # Loaded automatically as needed
├── docs/
├── docker/
├── src/
├── tests/
└── .github/workflows/
```

## Customization

Edit `.claude/settings.toml` to adjust behavior:

```toml
[workflow]
mode = autonomous          # or "assisted" for confirmations
enforce_tdd = true
max_file_lines = 300

[automation]
auto_create_specs = true
auto_update_docs = true
auto_run_tests = true
```

## Why Autonomous Mode?

| Manual Mode | Autonomous Mode |
|-------------|-----------------|
| `/plan` → wait → `/architect` → wait → `/test` → wait... | `/do add user auth` → complete feature |
| Remember to run tests | Tests run automatically |
| Remember to update docs | Docs update automatically |
| Hope files stay small | Auto-split enforced |

## Best Practices

1. **Be specific** in business requests - more detail = better results
2. **Trust the process** - let it complete before interrupting
3. **Review outputs** - autonomous doesn't mean unchecked
4. **Iterate** - use `/improve` after features are complete

## Support

Customize by editing:
- `CLAUDE.md` - Workflow rules and project context
- `.claude/commands/*.md` - Command behaviors
- `.claude/skills/*/SKILL.md` - Domain knowledge
- `.claude/settings.toml` - Automation settings
