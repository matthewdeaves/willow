# 🚀 AI Assistant Onboarding

Welcome AI assistants to the Willow CMS repository! This guide will help you get started quickly.

---

## 🤖 Choose Your Path

### For GitHub Copilot
**START HERE**: [`helper-files(use-only-if-you-get-lost)/docs/COPILOT.md`](helper-files(use-only-if-you-get-lost)/docs/COPILOT.md)

This comprehensive guide includes:
- 🚨 Critical rules and non-negotiables
- 🏗️ Complete MVC architecture with examples
- 🧪 Testing requirements and patterns
- 🔐 Security best practices
- 📝 Coding standards and conventions
- ✅ Pre-commit checklist

**Quick Reference**: [`helper-files(use-only-if-you-get-lost)/docs/COPILOT_QUICK_REFERENCE.md`](helper-files(use-only-if-you-get-lost)/docs/COPILOT_QUICK_REFERENCE.md)

**Onboarding Checklist**: [`helper-files(use-only-if-you-get-lost)/docs/COPILOT_ONBOARDING_CHECKLIST.md`](helper-files(use-only-if-you-get-lost)/docs/COPILOT_ONBOARDING_CHECKLIST.md)

### For Claude AI
**START HERE**: [`helper-files(use-only-if-you-get-lost)/docs/CLAUDE.md`](helper-files(use-only-if-you-get-lost)/docs/CLAUDE.md)

### For All AI Assistants

**Documentation Index**: [`helper-files(use-only-if-you-get-lost)/docs/DOCUMENTATION_INDEX.md`](helper-files(use-only-if-you-get-lost)/docs/DOCUMENTATION_INDEX.md)
- Complete documentation map
- Organized by use case
- Easy navigation

---

## ⚡ Quick Start

### 1. Read the Critical Rules
```bash
# Open and read thoroughly
cat helper-files(use-only-if-you-get-lost)/docs/COPILOT.md
```

**Non-Negotiables**:
- ✅ Follow CakePHP 5.x conventions strictly
- ✅ Test everything (run tests before AND after changes)
- ✅ Comment and document your code
- ✅ Keep MVC sacred (thin controllers, fat models, presentation-only views)
- ✅ Security first (validate input, escape output, check authorization)

### 2. Understand the Structure
```
willow/
├── cakephp/                      # Main CakePHP application
│   ├── src/
│   │   ├── Controller/           # Controllers (THIN)
│   │   ├── Model/                # Models (FAT - business logic)
│   │   └── ...
│   ├── plugins/
│   │   ├── AdminTheme/           # Admin backend
│   │   └── DefaultTheme/         # Public frontend
│   └── tests/                    # 292+ tests
└── helper-files(use-only-if-you-get-lost)/
    └── docs/                     # All documentation
```

### 3. Install Dev Environment
```bash
# Start development environment
./setup_dev_env.sh

# Install helpful aliases
./setup_dev_aliases.sh
```

### 4. Run Baseline Tests
```bash
# ALWAYS run before making changes
phpunit

# Check code standards
phpcs_sniff

# Static analysis
phpstan_analyse
```

---

## 📚 Essential Documentation

| Document | Purpose | When to Use |
|----------|---------|-------------|
| [COPILOT.md](helper-files(use-only-if-you-get-lost)/docs/COPILOT.md) | Complete guide | First read, reference |
| [COPILOT_QUICK_REFERENCE.md](helper-files(use-only-if-you-get-lost)/docs/COPILOT_QUICK_REFERENCE.md) | Quick commands | Daily development |
| [COPILOT_ONBOARDING_CHECKLIST.md](helper-files(use-only-if-you-get-lost)/docs/COPILOT_ONBOARDING_CHECKLIST.md) | Step-by-step | Initial onboarding |
| [DeveloperGuide.md](helper-files(use-only-if-you-get-lost)/docs/DeveloperGuide.md) | Comprehensive dev guide | Deep dives |
| [README.md](helper-files(use-only-if-you-get-lost)/docs/README.md) | Project overview | Understanding features |
| [DOCUMENTATION_INDEX.md](helper-files(use-only-if-you-get-lost)/docs/DOCUMENTATION_INDEX.md) | Doc navigation | Finding specific info |

---

## 🎯 The Rules

### What You MUST Do

✅ **Follow CakePHP 5.x Conventions**
- PascalCase classes, camelCase methods, snake_case DB
- Plural controllers, plural table classes, singular entities
- Use existing patterns from the codebase

✅ **Test Everything**
- Run `phpunit` before making changes
- Write tests for new features (TDD approach)
- Maintain 80%+ test coverage
- Never break existing tests

✅ **Keep MVC Sacred**
- Controllers: THIN (handle requests, delegate to models)
- Models: FAT (all business logic here)
- Views: PRESENTATION ONLY (no business logic)

✅ **Security First**
- Validate input: `$this->request->allowMethod(['post'])`
- Escape output: `<?= h($data) ?>`
- Check authorization: `$this->Authorization->authorize($entity)`
- Never commit secrets or `.env` files

✅ **Use Code Quality Tools**
- `phpcs_sniff` - Check standards
- `phpcs_fix` - Auto-fix violations
- `phpstan_analyse` - Static analysis

### What You MUST NOT Do

❌ Break existing tests without approval  
❌ Commit secrets, API keys, or `.env` files  
❌ Introduce inconsistent patterns  
❌ Put business logic in controllers or views  
❌ Skip input validation and sanitization  
❌ Use raw SQL (use ORM)  
❌ Ignore code quality tools  
❌ Make changes without understanding existing code  

---

## 🔥 Most Used Commands

```bash
# Testing
phpunit                        # Run all tests
phpunit_cov_html              # Coverage report (http://localhost:8080/coverage/)

# Code Quality
phpcs_sniff                   # Check standards
phpcs_fix                     # Auto-fix
phpstan_analyse               # Static analysis

# Development
willowcms_shell               # Container shell
cake_queue_worker_verbose     # Queue worker (required for AI)
./manage.sh                   # Interactive management

# Database
cake_shell migrations migrate # Run migrations
cake_shell cache clear_all    # Clear cache

# Code Generation (use AdminTheme)
cake_shell bake all Dogs --theme AdminTheme
```

---

## 💡 Pro Tips

1. **Read COPILOT.md first** - Most comprehensive guide
2. **Always check baseline tests** - Run `phpunit` before changes
3. **Follow existing patterns** - Find similar code and match it
4. **Use TDD** - Write tests first, then implement
5. **Keep controllers thin** - Business logic → Models
6. **Use eager loading** - Prevent N+1 queries with `contain()`
7. **Run queue workers** - For AI features: `cake_queue_worker_verbose`
8. **Check documentation** - Use DOCUMENTATION_INDEX.md to navigate

---

## 🎓 The Ritual Chant

Before making any change, repeat:

**"I will comment, not just code. I will document, not just improvise."**

And remember:
- "I will test, not just hope."
- "I will follow patterns, not reinvent wheels."
- "I will secure, not just function."

---

## 🆘 Need Help?

1. Check [COPILOT_QUICK_REFERENCE.md](helper-files(use-only-if-you-get-lost)/docs/COPILOT_QUICK_REFERENCE.md)
2. Search [DOCUMENTATION_INDEX.md](helper-files(use-only-if-you-get-lost)/docs/DOCUMENTATION_INDEX.md)
3. Review [TROUBLESHOOTING.md](helper-files(use-only-if-you-get-lost)/docs/TROUBLESHOOTING.md)
4. Consult [CakePHP Book](https://book.cakephp.org/5/en/)

---

## ✅ Pre-Commit Checklist

Before committing ANY code:

- [ ] All tests pass (`phpunit`)
- [ ] Code standards pass (`phpcs_sniff`)
- [ ] Static analysis passes (`phpstan_analyse`)
- [ ] New features have tests
- [ ] Documentation updated if needed
- [ ] No secrets in code
- [ ] Follows existing patterns
- [ ] Comments added for complex logic
- [ ] Security practices followed

---

## 🎉 Welcome!

You're now ready to contribute to Willow CMS! 

**Remember**: Consistency is key. When in doubt, follow existing patterns.

**Let's build something amazing!** 🚀🌿

---

<div align="center">
  <strong>🌿 Willow CMS - AI-Powered Content Management</strong>
  
  [Live Demo](https://willowcms.app) • [Documentation](helper-files(use-only-if-you-get-lost)/docs/) • [GitHub](https://github.com/garzarobm/willow)
</div>
