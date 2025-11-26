# Claude Code Autonomous Development Pipeline

## Instructions & Quick Reference

---

## Overview

This boilerplate enables **autonomous operation** of Claude Code. Instead of manually invoking commands for each phase, you simply provide business requirements and Claude Code handles the entire workflow automatically:

```
Your Input: "Add user registration with email verification"
     ↓
Claude Code Automatically:
  ✅ Creates feature specification
  ✅ Designs API endpoints
  ✅ Plans database schema
  ✅ Writes tests FIRST (TDD)
  ✅ Implements backend
  ✅ Implements frontend
  ✅ Runs quality checks
  ✅ Updates documentation
     ↓
Output: Complete feature with tests, docs, and clean code
```

---

## What's Included

| Category | Files | Purpose |
|----------|-------|---------|
| **Workflow Engine** | `CLAUDE.md` | Decision logic for autonomous operation |
| **Orchestrator Commands** | `/do`, `/build`, `/fix`, `/improve` | Main entry points for tasks |
| **Skills** | 10 specialized skills | Domain knowledge loaded automatically |
| **Templates** | 4 document templates | Feature specs, ADRs, API docs, test checklists |
| **Docker** | Dev, Test, Prod configurations | Complete containerization setup |
| **CI/CD** | GitHub Actions workflows | Automated testing and deployment |
| **Settings** | `.claude/settings.toml` | Behavior configuration |

### Tech Stack

- **Backend**: Laravel 11+ (PHP 8.2+)
- **Frontend**: React 18+ with TypeScript
- **Styling**: Tailwind CSS 3+
- **Database**: PostgreSQL 15+
- **Cache/Queue**: Redis
- **Containers**: Docker + Docker Compose

---

## Commands

| Command | Purpose | When to Use |
|---------|---------|-------------|
| `/do [request]` | **Main orchestrator** | Any task - it figures out the workflow |
| `/build [feature]` | Complete feature build | Complex features needing full stack |
| `/fix [bug]` | Bug fix with TDD | When something is broken |
| `/improve [target]` | Safe refactoring | Code quality improvements |

### Example Usage

```bash
# Build features
/do Users should be able to register with email and password
/do Add a dashboard showing weekly user activity
/build Complete invoice management with PDF generation

# Fix bugs
/fix Login fails when email contains special characters
/fix Dashboard charts not loading on mobile

# Improve code
/improve UserService class is getting too large
/improve Reduce duplication in API controllers
```

---

## Project Structure

```
claude-code-boilerplate-v2/
│
├── CLAUDE.md                     # 🤖 AUTONOMOUS WORKFLOW ENGINE
├── KICKOFF.md                    # Project initialization prompt
├── README.md                     # Quick reference
├── .gitignore
│
├── .claude/
│   ├── commands/                 # Orchestrator commands
│   │   ├── do.md                 # /do - Main autonomous orchestrator
│   │   ├── build.md              # /build - Feature builder
│   │   ├── fix.md                # /fix - Bug fixer with TDD
│   │   └── improve.md            # /improve - Safe refactoring
│   ├── settings.toml             # Behavior configuration
│   └── skills/                   # Loaded automatically by workflow engine
│       ├── project-planning/     # Feature specs & estimation
│       ├── architecture/         # System design & ADRs
│       ├── environment-setup/    # Docker configuration
│       ├── backend-laravel/      # Laravel patterns
│       ├── frontend-react/       # React + Tailwind patterns
│       ├── ui-ux-guidelines/     # Design system
│       ├── testing-tdd/          # TDD workflow
│       ├── documentation/        # Doc standards
│       ├── deployment/           # CI/CD
│       └── refactoring/          # Code quality
│
├── docs/
│   ├── templates/                # Document templates
│   ├── features/                 # Feature specs (auto-created)
│   ├── architecture/             # Architecture docs
│   └── decisions/                # ADRs
│
├── docker/
│   ├── development/              # Dev environment
│   ├── testing/                  # Test environment
│   └── production/               # Production environment
│
├── src/
│   ├── backend/                  # Laravel application
│   └── frontend/                 # React application
│
├── tests/
│   ├── unit/
│   ├── integration/
│   └── e2e/
│
└── .github/workflows/            # CI/CD pipelines
```

---

## How to Use

### Step 1: Setup New Project

1. **Extract** the `claude-code-boilerplate-autonomous.zip` to your new project folder:
   ```bash
   unzip claude-code-boilerplate-autonomous.zip
   mv claude-code-boilerplate-v2 my-new-project
   cd my-new-project
   ```

2. **Initialize Git**:
   ```bash
   git init
   git add .
   git commit -m "Initial commit from boilerplate"
   ```

### Step 2: Customize Project Settings

1. **Edit `CLAUDE.md`** - Update these sections:
   - Project name and description
   - Tech stack (if different from defaults)
   - Current sprint tasks

2. **Prepare the kickoff prompt** - Open `KICKOFF.md` and fill in:
   - Project name
   - Description
   - Core features
   - Technical requirements
   - MVP scope

### Step 3: Initialize with Claude Code

1. **Open your project** in Claude Code
2. **Paste the kickoff prompt** from `KICKOFF.md`
3. Claude will automatically set up the project structure

### Step 4: Start Building (Autonomous Mode)

Now just describe what you need in business terms:

```bash
# Example: Building user authentication
/do Users should be able to register with email and password, 
    verify their email, and login to access protected pages

# Claude automatically:
# 1. Creates docs/features/user-authentication.md
# 2. Designs API: POST /register, POST /verify, POST /login
# 3. Plans database: users table with email_verified_at
# 4. Writes tests for all endpoints
# 5. Implements Laravel backend
# 6. Implements React frontend
# 7. Updates documentation
```

### Step 5: Continue Development

Keep providing business requirements:

```bash
/do Add a user profile page where users can update their name and avatar

/do Create a team system where users can create teams and invite others

/fix Password reset emails are not being sent

/improve The authentication code is getting complex, refactor it
```

---

## How It Works

### The Autonomous Workflow Engine

The `CLAUDE.md` file contains a **Decision Engine** that processes your requests:

```
┌─────────────────────────────────────────────────────────────────┐
│                  AUTONOMOUS WORKFLOW ENGINE                     │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  INPUT: "Add user registration"                                 │
│                 ↓                                               │
│  CLASSIFY: NEW_FEATURE                                          │
│                 ↓                                               │
│  LOAD SKILLS: project-planning, architecture, testing-tdd,      │
│               backend-laravel, frontend-react, documentation    │
│                 ↓                                               │
│  EXECUTE PHASES:                                                │
│    📋 Create feature spec                                       │
│    🏗️ Design architecture                                       │
│    🧪 Write tests FIRST                                         │
│    💻 Implement code                                            │
│    ✅ Quality checks                                            │
│    📝 Update documentation                                      │
│                 ↓                                               │
│  OUTPUT: Complete feature + summary report                      │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Request Classification

Claude automatically determines the workflow based on your request:

| Your Request | Classification | Automatic Workflow |
|--------------|----------------|-------------------|
| "add", "create", "build" | NEW_FEATURE | Full spec → arch → TDD → implement → docs |
| "fix", "bug", "broken" | BUG_FIX | Reproduce → fix → verify → docs |
| "refactor", "improve", "clean" | REFACTOR | Safety net → refactor → verify |
| "setup", "configure" | INFRASTRUCTURE | Environment skill |
| "deploy", "release" | DEPLOYMENT | Deployment skill |

### Enforced Rules (Non-Negotiable)

| Rule | What Happens |
|------|--------------|
| **TDD is mandatory** | Cannot write implementation without tests first |
| **File size limit: 300 lines** | Auto-splits files that exceed limit |
| **Documentation required** | Every task updates relevant docs |
| **Quality gates** | Must pass all checks before "complete" |

---

## Quick Reference

### Main Commands

```bash
/do [any business request]     # Main orchestrator - figures out the workflow
/build [feature description]   # Build complete feature
/fix [bug description]         # Fix with TDD approach
/improve [target]              # Safe refactoring
```

### Example Workflows

**Building a Complete Feature:**
```bash
/do Create a blog system where users can write posts, 
    add tags, and readers can comment
```
Claude will create: models, migrations, services, controllers, React components, tests, and documentation.

**Fixing a Bug:**
```bash
/fix Comments are not showing the correct timestamp
```
Claude will: write a test reproducing the bug, fix it, verify, document.

**Improving Code:**
```bash
/improve The PostService class has grown to 500 lines
```
Claude will: verify tests pass, split the class, keep tests green, report changes.

### Configuration

Edit `.claude/settings.toml` to customize behavior:

```toml
[workflow]
mode = autonomous           # autonomous | assisted
enforce_tdd = true
max_file_lines = 300
min_coverage = 80

[automation]
auto_create_specs = true
auto_update_docs = true
auto_run_tests = true
```

---

## Tips for Best Results

### Writing Effective Requests

**Be Specific:**
```bash
# Good
/do Users can upload profile avatars up to 5MB, 
    images are resized to 200x200, stored in S3

# Less specific (still works, but Claude will make assumptions)
/do Add avatar uploads
```

**Include Acceptance Criteria:**
```bash
/do Add password reset:
    - User requests reset via email
    - Token expires in 1 hour
    - Old password not required
    - Log all reset attempts
```

**Reference Existing Patterns:**
```bash
/do Add team invitations similar to how user registration works
```

### Trust the Process

1. **Don't interrupt mid-workflow** - Let Claude complete all phases
2. **Review the output** - Autonomous doesn't mean unchecked
3. **Iterate with /improve** - Refactor after features are stable
4. **Check the reports** - Claude provides summaries at the end

### Docker Commands

```bash
# Start development environment
cd docker/development && docker-compose up -d

# View logs
docker-compose logs -f backend

# Stop environment
docker-compose down

# Rebuild after changes
docker-compose up -d --build
```

---

## Customization

### Modify Workflow Rules
Edit `CLAUDE.md` → "AUTONOMOUS WORKFLOW ENGINE" section

### Change Command Behavior
Edit files in `.claude/commands/`

### Update Skills
Edit files in `.claude/skills/[name]/SKILL.md`

### Adjust Automation
Edit `.claude/settings.toml`

### Add New Skills
Create new folder in `.claude/skills/` with `SKILL.md`

---

## Comparison: Manual vs Autonomous

| Aspect | Manual Mode | Autonomous Mode |
|--------|-------------|-----------------|
| Commands | `/plan` → `/architect` → `/test` → `/backend`... | `/do add user auth` |
| Skill Loading | Manual reminder to read | Automatic |
| Test Enforcement | Hope you remember | Cannot proceed without |
| File Size | Check manually | Auto-enforced |
| Documentation | Remember to update | Auto-updated |
| Workflow | You orchestrate | Claude orchestrates |

---

## Support

For issues or improvements:
1. Check skill files for domain knowledge
2. Review CLAUDE.md for workflow rules
3. Adjust settings.toml for behavior
4. Modify commands for different patterns

The boilerplate is designed to evolve with your workflow - customize freely!
