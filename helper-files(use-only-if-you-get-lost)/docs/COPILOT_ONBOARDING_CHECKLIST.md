# ✅ GitHub Copilot Onboarding Checklist

**Welcome to Willow CMS!** This checklist will guide you through your first steps as an AI assistant working on this project.

---

## 📖 Phase 1: Read & Understand (CRITICAL - DO FIRST!)

- [ ] Read `/helper-files(use-only-if-you-get-lost)/docs/COPILOT.md` thoroughly
  - [ ] Understand the 🚨 Critical Rules section
  - [ ] Review MVC architecture patterns
  - [ ] Study security best practices
  - [ ] Note the "Things to NEVER Do" list

- [ ] Review `/helper-files(use-only-if-you-get-lost)/docs/README.md`
  - [ ] Understand project features
  - [ ] Learn about available services
  - [ ] Note the quick start process

- [ ] Read `/helper-files(use-only-if-you-get-lost)/docs/DeveloperGuide.md`
  - [ ] Study testing & quality assurance section
  - [ ] Review best practices
  - [ ] Understand the CI/CD pipeline

- [ ] Skim `/helper-files(use-only-if-you-get-lost)/docs/HELPER.md`
  - [ ] Familiarize yourself with directory structure
  - [ ] Understand file organization

---

## 🏗️ Phase 2: Explore the Codebase

### Core Application Structure

- [ ] Browse `cakephp/src/Controller/`
  - [ ] Study `AppController.php` (base controller)
  - [ ] Review a sample controller (e.g., `ArticlesController.php`)
  - [ ] Check admin controllers in `Admin/` directory
  - [ ] Note how controllers are kept THIN

- [ ] Browse `cakephp/src/Model/Table/`
  - [ ] Study at least 2-3 table classes
  - [ ] Note how behaviors are used
  - [ ] Observe association patterns
  - [ ] See where business logic lives (in models, not controllers!)

- [ ] Browse `cakephp/src/Model/Entity/`
  - [ ] Check entity accessibility settings
  - [ ] Note virtual properties usage
  - [ ] Understand data validation patterns

- [ ] Browse `cakephp/src/Model/Behavior/`
  - [ ] Review `ImageAssociableBehavior.php`
  - [ ] Review `SlugBehavior.php`
  - [ ] Understand reusable functionality patterns

### Plugin System

- [ ] Explore `cakephp/plugins/AdminTheme/`
  - [ ] Check `src/Controller/` for admin overrides
  - [ ] View `templates/` structure
  - [ ] Review `templates/layout/` for admin layouts
  - [ ] Browse `webroot/` for admin assets

- [ ] Explore `cakephp/plugins/DefaultTheme/`
  - [ ] Check frontend controllers
  - [ ] View public templates structure
  - [ ] Review layouts and elements

### Testing Infrastructure

- [ ] Browse `cakephp/tests/TestCase/`
  - [ ] Study `Controller/` test examples
  - [ ] Review `Model/Table/` test patterns
  - [ ] Check `Model/Behavior/` test structure
  - [ ] Note the IntegrationTestTrait usage

- [ ] Browse `cakephp/tests/Fixture/`
  - [ ] Understand test data structure
  - [ ] See how fixtures are defined

---

## 🎯 Phase 3: Understand Key Patterns

### MVC Pattern

- [ ] **Controllers (Thin)**
  - [ ] Controllers handle requests
  - [ ] Delegate to models for business logic
  - [ ] Use `allowMethod()` for request validation
  - [ ] Use `Authorization` for access control
  - [ ] Return responses or redirects

- [ ] **Models (Fat)**
  - [ ] Business logic lives here
  - [ ] Custom finder methods for queries
  - [ ] Validation rules in entities
  - [ ] Behaviors for reusable functionality
  - [ ] Associations defined in `initialize()`

- [ ] **Views (Presentation)**
  - [ ] Only presentation logic
  - [ ] Always escape output with `h()`
  - [ ] Plugin-based theming
  - [ ] No business logic allowed

### Common Behaviors

- [ ] Understand `Timestamp` behavior (auto created/modified)
- [ ] Understand `Sluggable` behavior (URL-friendly slugs)
- [ ] Understand `ImageAssociable` behavior (image management)
- [ ] Understand `Translate` behavior (i18n support)

### Queue System

- [ ] Understand when to use queues (AI processing, image manipulation)
- [ ] Know how to enqueue jobs
- [ ] Remember to run queue workers for AI features

---

## 🧪 Phase 4: Testing Knowledge

### Run Baseline Tests

- [ ] Set up development environment (`./setup_dev_env.sh`)
- [ ] Install dev aliases (`./setup_dev_aliases.sh`)
- [ ] Run full test suite: `phpunit`
  - [ ] Note how many tests pass (should be 292+)
  - [ ] Verify all tests are passing
  - [ ] If any fail, understand these are baseline issues

- [ ] Run code standards check: `phpcs_sniff`
  - [ ] Note any violations
  - [ ] Try auto-fix: `phpcs_fix`

- [ ] Run static analysis: `phpstan_analyse`
  - [ ] Note any issues (level 5)

### Test Different Components

- [ ] Run controller tests: `phpunit tests/TestCase/Controller/`
- [ ] Run model tests: `phpunit tests/TestCase/Model/Table/`
- [ ] Run behavior tests: `phpunit tests/TestCase/Model/Behavior/`
- [ ] Generate coverage report: `phpunit_cov_html`
  - [ ] Access at http://localhost:8080/coverage/
  - [ ] Note current coverage percentage

---

## 🔧 Phase 5: Try Basic Operations

### Database Operations

- [ ] Check current migrations: `cake_shell migrations status`
- [ ] Try creating a test migration: `cake_shell migrations create TestMigration`
- [ ] Clear cache: `cake_shell cache clear_all`

### Code Generation

- [ ] Try baking a model: `cake_shell bake model --help`
- [ ] Review bake templates in `plugins/AdminTheme/src/Command/Bake/`
- [ ] Understand the `--theme AdminTheme` pattern

### Queue Worker

- [ ] Start queue worker: `cake_queue_worker_verbose`
- [ ] Observe how jobs are processed
- [ ] Stop the worker (Ctrl+C)

---

## 🎓 Phase 6: Ritual Chant (Say it out loud!)

**"I will comment, not just code. I will document, not just improvise."**

- [ ] I understand that controllers must be THIN
- [ ] I understand that models must be FAT (contain business logic)
- [ ] I understand that views must be PRESENTATION ONLY
- [ ] I will ALWAYS run tests before making changes
- [ ] I will NEVER commit secrets or `.env` files
- [ ] I will FOLLOW existing code patterns
- [ ] I will WRITE tests for new features
- [ ] I will CHECK code standards before committing
- [ ] I will ESCAPE all output in templates
- [ ] I will VALIDATE all input in controllers
- [ ] I will USE eager loading to prevent N+1 queries
- [ ] I will RESPECT CakePHP 5.x conventions

---

## 🚀 Phase 7: Ready to Contribute!

### Pre-Work Checklist

- [ ] Development environment is running
- [ ] Dev aliases are installed
- [ ] All baseline tests pass
- [ ] Code standards check passes
- [ ] Documentation has been read
- [ ] Key patterns are understood
- [ ] Test infrastructure is familiar

### First Contribution Guidelines

When making your first change:

1. **Understand the Request**
   - [ ] Read the issue or request carefully
   - [ ] Ask clarifying questions if needed
   - [ ] Identify which components need changes

2. **Check Existing Code**
   - [ ] Find similar existing code
   - [ ] Follow the same patterns
   - [ ] Match the coding style

3. **Write Tests First (TDD)**
   - [ ] Write failing test
   - [ ] Implement minimal code to pass
   - [ ] Refactor if needed

4. **Make Minimal Changes**
   - [ ] Change only what's necessary
   - [ ] Don't refactor unrelated code
   - [ ] Keep changes focused

5. **Test Continuously**
   - [ ] Run tests after each change
   - [ ] Check code standards
   - [ ] Verify static analysis

6. **Final Validation**
   - [ ] Full test suite passes
   - [ ] Code standards pass
   - [ ] Static analysis passes
   - [ ] Documentation updated if needed

---

## 📝 Quick Reference Card

**Keep this handy:**

```bash
# Most used commands
phpunit                        # Run tests
phpcs_sniff                    # Check standards
phpcs_fix                      # Fix standards
phpstan_analyse               # Static analysis
cake_shell [command]          # CakePHP console
cake_queue_worker_verbose     # Queue worker (for AI)

# Documentation locations
/helper-files(use-only-if-you-get-lost)/docs/COPILOT.md
/helper-files(use-only-if-you-get-lost)/docs/COPILOT_QUICK_REFERENCE.md
/helper-files(use-only-if-you-get-lost)/docs/DeveloperGuide.md
/helper-files(use-only-if-you-get-lost)/docs/README.md

# Key directories
cakephp/src/Controller/       # Controllers (thin)
cakephp/src/Model/Table/      # Models (fat)
cakephp/plugins/AdminTheme/   # Admin backend
cakephp/plugins/DefaultTheme/ # Public frontend
cakephp/tests/TestCase/       # Tests

# Security rules
- Always: h() output, allowMethod(), Authorization
- Never: commit .env, put logic in controllers/views
```

---

## ✨ Additional Resources

- **CakePHP Book**: https://book.cakephp.org/5/en/
- **PHPUnit Manual**: https://phpunit.de/documentation.html
- **Project GitHub**: https://github.com/garzarobm/willow

---

## 🎉 Congratulations!

Once you've completed this checklist, you're ready to:
- ✅ Understand the codebase architecture
- ✅ Follow project conventions
- ✅ Write tests for new features
- ✅ Maintain code quality standards
- ✅ Contribute with confidence

**Remember**: When in doubt, follow existing patterns in the codebase!

---

<div align="center">
  <strong>🌿 Welcome to the Willow CMS team!</strong>
  
  Now go forth and code with consistency! 🚀
</div>
