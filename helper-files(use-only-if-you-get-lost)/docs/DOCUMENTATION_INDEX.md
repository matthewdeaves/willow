# 📚 Willow CMS Documentation Index

Welcome to the Willow CMS documentation! This guide will help you find the right documentation for your needs.

---

## 🤖 AI Assistant Documentation

### For GitHub Copilot
- **[COPILOT.md](COPILOT.md)** - 🔥 **START HERE!** Comprehensive onboarding guide for GitHub Copilot
  - Critical rules and non-negotiables
  - Complete architecture overview
  - MVC patterns and best practices
  - Testing requirements and examples
  - Security guidelines
  - Common patterns and anti-patterns
  - Pre-commit checklist

- **[COPILOT_QUICK_REFERENCE.md](COPILOT_QUICK_REFERENCE.md)** - Quick command reference
  - Most used commands
  - Common patterns
  - Quick troubleshooting
  - Hot tips and tricks

- **[COPILOT_ONBOARDING_CHECKLIST.md](COPILOT_ONBOARDING_CHECKLIST.md)** - Step-by-step onboarding
  - Phase-by-phase checklist
  - Code exploration guide
  - Testing knowledge validation
  - First contribution guidelines

### For Claude AI
- **[CLAUDE.md](CLAUDE.md)** - Comprehensive guide for Claude Code assistant
  - Essential commands
  - Architecture deep dive
  - Testing strategy
  - Environment configuration

---

## 👨‍💻 Developer Documentation

### Getting Started
- **[README.md](README.md)** - Project overview and quick start
  - Key features
  - Installation instructions
  - Available services
  - Essential workflows

- **[DeveloperGuide.md](DeveloperGuide.md)** - 📖 Complete developer guide
  - Architecture deep dive
  - Development workflow
  - Testing & quality assurance
  - AI integration details
  - Internationalization
  - Configuration & environment

### Project Structure
- **[HELPER.md](HELPER.md)** - Complete directory structure reference
  - Full directory tree
  - Key directory purposes
  - Development workflow
  - File organization

- **[README_STRUCTURE.md](README_STRUCTURE.md)** - Project organization explanation
  - Directory structure rationale
  - Updated file locations
  - Migration notes

---

## 🐳 Docker & Environment

### Setup & Configuration
- **[DOCKER_ENV_README.md](DOCKER_ENV_README.md)** - Docker environment overview
- **[docker-compose-override-guide.md](docker-compose-override-guide.md)** - Customizing Docker setup
- **[docker-restart-guide.md](docker-restart-guide.md)** - Restarting services

### Operations
- **[ENVIRONMENT_RESTART_PROCEDURES.md](ENVIRONMENT_RESTART_PROCEDURES.md)** - Restart procedures
- **[SHUTDOWN_PROCEDURES.md](SHUTDOWN_PROCEDURES.md)** - Proper shutdown procedures
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Common issues and solutions

---

## 🧪 Testing & Quality

### Testing Documentation
- Comprehensive testing examples in [COPILOT.md](COPILOT.md) and [DeveloperGuide.md](DeveloperGuide.md)
- **[TEST_REFACTORING_SUMMARY.md](TEST_REFACTORING_SUMMARY.md)** - Test refactoring history

### Verification
- **[VERIFICATION_CHECKLIST.md](VERIFICATION_CHECKLIST.md)** - System verification checklist
- Coverage reports available at http://localhost:8080/coverage/

---

## 🤖 AI Features

### AI Implementation
- **[AI_IMPROVEMENTS_IMPLEMENTATION_PLAN.md](AI_IMPROVEMENTS_IMPLEMENTATION_PLAN.md)** - AI features roadmap
- **[AI_METRICS_IMPLEMENTATION_SUMMARY.md](AI_METRICS_IMPLEMENTATION_SUMMARY.md)** - AI metrics implementation
- **[AI_METRICS_STATUS_REPORT.md](AI_METRICS_STATUS_REPORT.md)** - Current AI status
- **[REALTIME_METRICS_IMPLEMENTATION.md](REALTIME_METRICS_IMPLEMENTATION.md)** - Real-time metrics

---

## 🔧 Maintenance & Operations

### Cleanup & Maintenance
- **[CLEANUP_PROCEDURES.md](CLEANUP_PROCEDURES.md)** - System cleanup procedures
- **[CLEANUP_SUMMARY.md](CLEANUP_SUMMARY.md)** - Cleanup operation summaries

### Planning & Refactoring
- **[REFACTORING_PLAN.md](REFACTORING_PLAN.md)** - General refactoring plans
- **[simple-products-REFACTORING-plan.md](simple-products-REFACTORING-plan.md)** - Products module refactoring
- **[ROUTE_OPTIMIZATION_RECOMMENDATIONS.md](ROUTE_OPTIMIZATION_RECOMMENDATIONS.md)** - Route optimization

---

## 📦 Release Information

- **[BETA-RELEASES-INFO.md](BETA-RELEASES-INFO.md)** - Beta release information and notes

---

## 🗺️ Documentation Map by Use Case

### "I'm new to the project"
1. Start: [README.md](README.md) - Get the big picture
2. Then: [COPILOT.md](COPILOT.md) or [CLAUDE.md](CLAUDE.md) - AI assistant guide
3. Next: [COPILOT_ONBOARDING_CHECKLIST.md](COPILOT_ONBOARDING_CHECKLIST.md) - Follow the checklist
4. Reference: [COPILOT_QUICK_REFERENCE.md](COPILOT_QUICK_REFERENCE.md) - Keep handy

### "I'm setting up the development environment"
1. Read: [README.md](README.md) - Quick start section
2. Reference: [DOCKER_ENV_README.md](DOCKER_ENV_README.md) - Docker details
3. If problems: [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
4. Advanced: [docker-compose-override-guide.md](docker-compose-override-guide.md)

### "I'm adding a new feature"
1. Review: [DeveloperGuide.md](DeveloperGuide.md) - Development workflow
2. Check: [COPILOT.md](COPILOT.md) - Patterns and best practices
3. Reference: [COPILOT_QUICK_REFERENCE.md](COPILOT_QUICK_REFERENCE.md) - Quick commands
4. Test: Follow testing guidelines in [DeveloperGuide.md](DeveloperGuide.md)

### "I'm debugging an issue"
1. Check: [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Common issues
2. Review logs: See debug tips in [COPILOT.md](COPILOT.md)
3. Environment: [ENVIRONMENT_RESTART_PROCEDURES.md](ENVIRONMENT_RESTART_PROCEDURES.md)

### "I'm working with AI features"
1. Overview: AI sections in [README.md](README.md)
2. Implementation: [AI_IMPROVEMENTS_IMPLEMENTATION_PLAN.md](AI_IMPROVEMENTS_IMPLEMENTATION_PLAN.md)
3. Status: [AI_METRICS_STATUS_REPORT.md](AI_METRICS_STATUS_REPORT.md)
4. Remember: Always run queue workers!

### "I need a quick command reference"
1. Go to: [COPILOT_QUICK_REFERENCE.md](COPILOT_QUICK_REFERENCE.md)
2. Or: `dev_aliases.txt` in the project root

---

## 📝 Documentation Maintenance

### For Contributors

When adding new documentation:
1. Place it in `/helper-files(use-only-if-you-get-lost)/docs/`
2. Update this index (DOCUMENTATION_INDEX.md)
3. Use clear, descriptive filenames
4. Include a summary at the top of the document
5. Cross-reference related documents

### Documentation Standards

- Use Markdown format
- Include a clear title and description
- Add a table of contents for long documents
- Use emojis for visual clarity (like this guide!)
- Include code examples where relevant
- Keep it up-to-date with code changes

---

## 🔗 External Resources

- **CakePHP Book**: https://book.cakephp.org/5/en/
- **Anthropic API Docs**: https://docs.anthropic.com/
- **Docker Documentation**: https://docs.docker.com/
- **PHPUnit Manual**: https://phpunit.de/documentation.html
- **Bootstrap Docs**: https://getbootstrap.com/docs/

---

## 💡 Pro Tips

1. **Bookmark this index** - It's your navigation hub
2. **Start with COPILOT.md** - Most comprehensive AI guide
3. **Use Quick Reference** - For daily commands
4. **Check Troubleshooting first** - Before asking for help
5. **Follow the patterns** - Consistency is key in this project

---

## 📞 Getting Help

If you can't find what you need:
1. Search the documentation (Ctrl+F is your friend)
2. Check the [Troubleshooting Guide](TROUBLESHOOTING.md)
3. Review relevant code examples in the codebase
4. Consult the CakePHP Book for framework questions
5. Open a GitHub issue with your question

---

<div align="center">
  <strong>🌿 Happy coding with Willow CMS!</strong>
  
  Last Updated: 2024
</div>
