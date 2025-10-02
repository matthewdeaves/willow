# 🚀 GitHub Copilot Quick Reference

> Quick command reference for daily Willow CMS development

## 🏃 Most Used Commands

### Testing
```bash
phpunit                                      # Run all tests
phpunit tests/TestCase/Controller/          # Test controllers
phpunit --filter testAdd                     # Run specific test
phpunit_cov_html                            # Coverage report (http://localhost:8080/coverage/)
```

### Code Quality
```bash
phpcs_sniff                                 # Check code standards
phpcs_fix                                   # Auto-fix violations
phpstan_analyse                             # Static analysis
```

### Development
```bash
./setup_dev_env.sh                          # Start environment
willowcms_shell                             # Container shell
cake_queue_worker_verbose                   # Start queue worker (required for AI)
./manage.sh                                 # Interactive management tool
```

### Database
```bash
cake_shell migrations migrate               # Run migrations
cake_shell migrations create CreateTable    # Create migration
cake_shell cache clear_all                  # Clear cache
```

### Code Generation
```bash
cake_shell bake model Dogs                              # Generate model
cake_shell bake controller Dogs --theme AdminTheme      # Generate admin controller
cake_shell bake template Dogs --theme AdminTheme        # Generate admin templates
cake_shell bake all Dogs --theme AdminTheme             # Generate everything
```

---

## 📂 Key Directories

```
cakephp/
├── src/
│   ├── Controller/          # Controllers (thin)
│   │   └── Admin/          # Admin controllers
│   ├── Model/
│   │   ├── Table/          # Table classes (business logic)
│   │   ├── Entity/         # Entity classes
│   │   └── Behavior/       # Reusable behaviors
│   ├── Service/Api/        # External API services
│   └── Command/            # CLI commands
├── plugins/
│   ├── AdminTheme/         # Admin backend
│   └── DefaultTheme/       # Public frontend
├── tests/
│   ├── TestCase/           # Test classes
│   └── Fixture/            # Test data
└── config/
    ├── routes.php          # URL routing
    ├── Migrations/         # Database migrations
    └── .env               # Environment variables (DON'T COMMIT)
```

---

## 🎯 Common Patterns

### Controller Pattern
```php
namespace App\Controller;

class ArticlesController extends AppController
{
    public function view($slug = null)
    {
        $article = $this->Articles
            ->find()
            ->where(['slug' => $slug])
            ->contain(['User', 'Tags'])  // Eager load
            ->firstOrFail();
        
        $this->set(compact('article'));
    }
    
    public function add()
    {
        $this->request->allowMethod(['get', 'post']);
        $article = $this->Articles->newEmptyEntity();
        
        if ($this->request->is('post')) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Saved!'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Error saving.'));
        }
        
        $this->set(compact('article'));
    }
}
```

### Table Pattern
```php
namespace App\Model\Table;

class ArticlesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        
        $this->addBehavior('Timestamp');
        $this->addBehavior('Sluggable');
        $this->addBehavior('ImageAssociable');
        
        $this->belongsTo('Users');
        $this->belongsToMany('Tags');
    }
    
    public function findPublished($query)
    {
        return $query->where(['published' => true]);
    }
}
```

### Test Pattern
```php
namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class ArticlesControllerTest extends TestCase
{
    use IntegrationTestTrait;
    
    protected $fixtures = ['app.Articles', 'app.Users'];
    
    public function testIndex(): void
    {
        $this->get('/articles');
        $this->assertResponseOk();
    }
    
    public function testAdd(): void
    {
        $data = ['title' => 'Test', 'body' => 'Content'];
        $this->post('/articles/add', $data);
        $this->assertRedirect(['action' => 'index']);
    }
}
```

---

## 🔐 Security Checklist

- ✅ Validate request methods: `$this->request->allowMethod(['post'])`
- ✅ Escape output: `<?= h($data) ?>`
- ✅ Use authorization: `$this->Authorization->authorize($entity)`
- ✅ Validate input: `$entity = $table->patchEntity($entity, $data)`
- ✅ CSRF protection enabled by default
- ❌ Never commit `.env` files or secrets

---

## 🎨 Naming Conventions

| Type | Convention | Example |
|------|-----------|---------|
| Classes | PascalCase | `ArticlesController`, `ArticlesTable` |
| Methods | camelCase | `findPublished()`, `getUser()` |
| Variables | camelCase | `$article`, `$userId` |
| Tables | snake_case, plural | `articles`, `user_profiles` |
| Columns | snake_case | `created_at`, `user_id` |
| Controllers | Plural | `ArticlesController` (not Article) |

---

## 🔥 Hot Tips

1. **Always run tests before changes**: `phpunit`
2. **Use aliases**: Run `./setup_dev_aliases.sh` once
3. **Keep controllers thin**: Business logic → Models
4. **Eager load relations**: Prevent N+1 queries with `contain()`
5. **Use behaviors**: DRY principle for common model functionality
6. **Test queue jobs**: Run `cake_queue_worker_verbose` for AI features
7. **Follow existing patterns**: Read similar code in the project
8. **Check coverage**: `phpunit_cov_html` → http://localhost:8080/coverage/

---

## 📞 Need Help?

1. **Documentation**: `/helper-files(use-only-if-you-get-lost)/docs/`
   - `COPILOT.md` - Full onboarding guide (you are here)
   - `DeveloperGuide.md` - Comprehensive development guide
   - `README.md` - Project overview
   - `CLAUDE.md` - Additional reference

2. **CakePHP Docs**: https://book.cakephp.org/5/en/

3. **Project Structure**: `/helper-files(use-only-if-you-get-lost)/docs/HELPER.md`

---

## ⚡ Emergency Commands

```bash
# Something's broken?
docker compose down && docker compose up -d        # Restart services
cake_shell cache clear_all                         # Clear all cache
./scripts/health-check.sh                          # Check system health

# Tests failing?
phpunit --stop-on-failure                          # Stop at first failure
phpunit --filter testProblem                       # Run specific test

# Code quality issues?
phpcs_fix                                          # Auto-fix most issues
composer cs-fix                                    # Alternative fix command
```

---

<div align="center">
  <strong>Happy Coding! 🌿</strong>
</div>
