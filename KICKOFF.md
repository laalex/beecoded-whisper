# Project Kickoff Prompt (Autonomous Mode)

Use this prompt to initialize a new project. After this, you can simply describe business requirements and Claude Code will handle the rest.

---

## 🚀 PROJECT INITIALIZATION

I'm starting a new project. Please set it up in **autonomous mode** where you handle the full development workflow automatically.

### Project Overview

**Project Name**: [PROJECT_NAME]
**Description**: [What does this application do?]
**Target Users**: [Who will use this?]

### Core Features

1. [Feature 1]: [Brief description]
2. [Feature 2]: [Brief description]
3. [Feature 3]: [Brief description]

### Technical Requirements

- **Authentication**: [JWT / OAuth / Session / None]
- **Database**: [PostgreSQL / MySQL]
- **External APIs**: [List any integrations]
- **Real-time**: [WebSockets needed? Yes/No]
- **File Storage**: [Local / S3 / None]

### MVP Scope

What must be in the first release:
- [Must-have 1]
- [Must-have 2]
- [Must-have 3]

---

## SETUP TASKS

Please complete these initial setup tasks:

1. **Update CLAUDE.md** with project details
2. **Set up Docker** development environment
3. **Initialize** Laravel backend structure
4. **Initialize** React frontend structure
5. **Create feature specs** for each core feature

---

## AUTONOMOUS MODE ACTIVATED

After setup, I'll give you business requirements like:
- "Users should be able to register and login"
- "Add a dashboard showing user statistics"
- "Implement team invitations via email"

And you will automatically:
1. Create/update feature specifications
2. Design architecture and APIs
3. Write tests first (TDD)
4. Implement backend and frontend
5. Run quality checks
6. Update all documentation

**Use `/do [request]` for any task** - it will orchestrate the complete workflow.

---

## Example Commands After Setup

```
/do Users should be able to register with email and password

/do Add a dashboard that shows user activity from the last 7 days

/do Create an admin panel to manage users

/fix Login is returning 500 error when email has special characters

/improve Refactor the UserService class, it's getting too large

/build Complete invoice management with CRUD, PDF generation, and email sending
```

---

*Delete the instructions above and fill in your project details, then use this as your first prompt.*
