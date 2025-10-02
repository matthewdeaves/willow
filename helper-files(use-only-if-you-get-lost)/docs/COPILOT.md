# COPILOT.md

This file provides comprehensive guidance to GitHub Copilot when working with the Willow CMS codebase.

**🎯 Mission**: Help maintain and enhance Willow CMS following CakePHP 5.x conventions with ZERO tolerance for pattern inconsistencies.

---

## 🚨 Critical Rules - READ FIRST

### Non-Negotiable Principles

1. **Follow CakePHP 5.x Standards Strictly**
   - All code MUST follow CakePHP naming conventions
   - Use existing patterns from the codebase as reference
   - Never introduce inconsistent patterns or styles

2. **Test Everything**
   - Run existing tests BEFORE making changes to understand baseline
   - Write tests for new features (TDD approach)
   - Maintain 80%+ code coverage
   - NEVER break existing tests without explicit approval

3. **Comment & Document**
   - Add comments for complex business logic
   - Match the commenting style of surrounding code
   - Update documentation when changing features
   - Document WHY, not just WHAT

4. **MVC is Sacred**
   - Controllers: Thin - handle requests, delegate to models
   - Models: Fat - contain all business logic
   - Views: Presentation only - no business logic

5. **Security First**
   - Always validate and sanitize input
   - Use CSRF protection (enabled by default)
   - Implement proper authorization checks
   - Never commit secrets or API keys

---

## 📁 Project Structure

```
willow/
├── cakephp/                           # Main CakePHP application
│   ├── src/                           # Application source code
│   │   ├── Controller/                # Controllers (thin)
│   │   │   ├── AppController.php      # Base controller
│   │   │   ├── Admin/                 # Admin controllers
│   │   │   └── [Feature]Controller.php
│   │   ├── Model/                     # Models (fat - business logic)
│   │   │   ├── Table/                 # Table classes (queries, validation)
│   │   │   ├── Entity/                # Entity classes (data representation)
│   │   │   └── Behavior/              # Reusable model behaviors
│   │   ├── Service/                   # Service layer
│   │   │   └── Api/                   # External API integrations
│   │   ├── Command/                   # CLI commands
│   │   ├── Job/                       # Queue jobs
│   │   └── View/                      # View helpers
│   ├── plugins/                       # Plugin-based theming
│   │   ├── AdminTheme/                # Admin backend (Bootstrap)
│   │   │   ├── src/Controller/        # Admin controllers
│   │   │   ├── templates/             # Admin templates
│   │   │   └── webroot/               # Admin assets
│   │   └── DefaultTheme/              # Public frontend
│   │       ├── src/Controller/        # Frontend controllers
│   │       ├── templates/             # Public templates
│   │       └── webroot/               # Public assets
│   ├── tests/                         # Comprehensive test suite
│   │   ├── TestCase/                  # Test classes (292+ tests)
│   │   └── Fixture/                   # Test data fixtures
│   ├── config/                        # Application configuration
│   │   ├── app.php                    # Main config
│   │   ├── routes.php                 # URL routing
│   │   ├── Migrations/                # Database migrations
│   │   └── .env                       # Environment variables (DO NOT COMMIT)
│   └── webroot/                       # Public web assets
├── docker/                            # Docker development environment
├── scripts/                           # Automation scripts
└── helper-files(use-only-if-you-get-lost)/  # Documentation
    └── docs/                          # Developer documentation
```

---

## 🏗️ Architecture Patterns

### MVC Implementation

#### Models (src/Model/)

**Table Classes** - Business logic and database operations:
```php
// Example: src/Model/Table/ArticlesTable.php
namespace App\Model\Table;

use Cake\ORM\Table;

class ArticlesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        
        // Table configuration
        $this->setTable('articles');
        $this->setPrimaryKey('id');
        $this->setDisplayField('title');
        
        // Behaviors (reusable functionality)
        $this->addBehavior('Timestamp');
        $this->addBehavior('Sluggable');
        $this->addBehavior('ImageAssociable');
        
        // Associations
        $this->belongsTo('Users');
        $this->belongsToMany('Tags');
        $this->hasMany('Comments');
    }
    
    // Custom finder methods
    public function findPublished($query)
    {
        return $query->where(['Articles.published' => true]);
    }
    
    // Business logic methods
    public function getPopularArticles(int $limit = 10)
    {
        return $this->find()
            ->contain(['User', 'Tags'])
            ->order(['Articles.view_count' => 'DESC'])
            ->limit($limit)
            ->toArray();
    }
}
```

**Entity Classes** - Data representation and validation:
```php
// Example: src/Model/Entity/Article.php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Article extends Entity
{
    // Accessible fields for mass assignment
    protected $_accessible = [
        'title' => true,
        'body' => true,
        'published' => true,
        'user_id' => true,
        'tags' => true,
    ];
    
    // Hidden fields (won't appear in JSON/arrays)
    protected $_hidden = [
        'internal_notes',
    ];
    
    // Virtual properties
    protected function _getFullTitle(): string
    {
        return strtoupper($this->title);
    }
}
```

#### Controllers (src/Controller/)

**Keep controllers THIN** - delegate to models:
```php
// Example: src/Controller/ArticlesController.php
namespace App\Controller;

class ArticlesController extends AppController
{
    // Proper request method validation
    public function add()
    {
        $this->request->allowMethod(['get', 'post']);
        $article = $this->Articles->newEmptyEntity();
        
        if ($this->request->is('post')) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());
            
            // Authorization check (if using Authorization plugin)
            $this->Authorization->authorize($article);
            
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Article saved successfully.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Unable to save article.'));
        }
        
        $this->set(compact('article'));
    }
    
    public function view($slug = null)
    {
        // Use eager loading to prevent N+1 queries
        $article = $this->Articles
            ->find()
            ->where(['Articles.slug' => $slug])
            ->contain(['User', 'Tags', 'Comments'])
            ->firstOrFail();
        
        $this->set(compact('article'));
    }
}
```

#### Views (plugins/*/templates/)

**Theme-based templates** - presentation only:
```php
// Example: plugins/DefaultTheme/templates/Articles/view.php
<article class="article-detail">
    <h1><?= h($article->title) ?></h1>
    
    <div class="meta">
        <span>By <?= h($article->user->name) ?></span>
        <time datetime="<?= $article->created->format('c') ?>">
            <?= $article->created->format('F j, Y') ?>
        </time>
    </div>
    
    <div class="content">
        <?= $article->body ?>
    </div>
    
    <div class="tags">
        <?php foreach ($article->tags as $tag): ?>
            <span class="badge"><?= h($tag->name) ?></span>
        <?php endforeach; ?>
    </div>
</article>
```

### Behavior System

Reusable model functionality lives in `src/Model/Behavior/`:

```php
// Example: Using ImageAssociableBehavior
$this->Articles->addBehavior('ImageAssociable');

// Example: Using SlugBehavior
$this->Articles->addBehavior('Sluggable', [
    'field' => 'title',
    'slug' => 'slug',
]);
```

### Queue-Based Processing

For long-running tasks (AI processing, image manipulation):

```php
// Example: Enqueuing a job
use App\Job\GenerateAltTextJob;

$this->getTableLocator()->get('Queue')->createJob(
    GenerateAltTextJob::class,
    ['image_id' => $image->id]
);
```

**ALWAYS run queue workers** when testing AI features:
```bash
cake_queue_worker_verbose
```

---

## 🧪 Testing Requirements

### Test Structure

Located in `cakephp/tests/TestCase/`:
```
tests/
├── TestCase/
│   ├── Controller/              # Controller tests
│   │   ├── ArticlesControllerTest.php
│   │   └── Admin/
│   ├── Model/
│   │   ├── Table/               # Table class tests
│   │   ├── Entity/              # Entity tests
│   │   └── Behavior/            # Behavior tests
│   ├── Command/                 # CLI command tests
│   └── Service/                 # Service tests
└── Fixture/                     # Test data
```

### Writing Tests

**Always follow TDD approach**:

```php
// Example: tests/TestCase/Model/Table/ArticlesTableTest.php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ArticlesTable;
use Cake\TestSuite\TestCase;

class ArticlesTableTest extends TestCase
{
    protected $fixtures = [
        'app.Articles',
        'app.Users',
        'app.Tags',
    ];
    
    protected $Articles;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->Articles = $this->getTableLocator()->get('Articles');
    }
    
    public function testFindPublished(): void
    {
        $result = $this->Articles->find('published')->toArray();
        $this->assertNotEmpty($result);
        
        foreach ($result as $article) {
            $this->assertTrue($article->published);
        }
    }
    
    public function testGetPopularArticles(): void
    {
        $result = $this->Articles->getPopularArticles(5);
        $this->assertCount(5, $result);
        
        // Verify ordering by view count
        $viewCounts = array_column($result, 'view_count');
        $this->assertEquals($viewCounts, array_reverse(sort($viewCounts)));
    }
}
```

### Running Tests

```bash
# Run all tests (baseline check)
phpunit

# Run specific test file
phpunit tests/TestCase/Controller/ArticlesControllerTest.php

# Run with filter
phpunit --filter testAdd tests/TestCase/Controller/ArticlesControllerTest.php

# Coverage report (HTML)
phpunit_cov_html
# Access at http://localhost:8080/coverage/

# Coverage report (text)
phpunit_cov
```

**ALWAYS run tests before making changes** to understand the baseline!

---

## 🛠️ Essential Commands

### Development Environment

```bash
# Start development environment
./setup_dev_env.sh

# Access container shell
docker compose exec -it willowcms /bin/sh

# Interactive management tool
./manage.sh
```

### Install Development Aliases

**Strongly recommended** - makes development much easier:
```bash
./setup_dev_aliases.sh
```

Key aliases:
- `phpunit` - Run tests
- `phpcs_sniff` - Check code standards
- `phpcs_fix` - Auto-fix violations
- `phpstan_analyse` - Static analysis
- `cake_shell` - Run CakePHP console
- `cake_queue_worker_verbose` - Start queue worker

### Code Quality

```bash
# Check code standards (CakePHP conventions)
phpcs_sniff

# Auto-fix code standard violations
phpcs_fix

# Static analysis (PHPStan level 5)
phpstan_analyse

# Composer scripts
docker compose exec willowcms composer cs-check
docker compose exec willowcms composer cs-fix
docker compose exec willowcms composer stan
```

### Database Operations

```bash
# Run migrations
docker compose exec willowcms bin/cake migrations migrate

# Create migration
docker compose exec willowcms bin/cake migrations create CreateDogsTable

# Migration diff (after schema changes)
docker compose exec willowcms bin/cake migrations migrate --dry-run

# Rollback
docker compose exec willowcms bin/cake migrations rollback
```

### Code Generation (Bake)

**ALWAYS use AdminTheme** for admin controllers:

```bash
# Bake model
docker compose exec willowcms bin/cake bake model Dogs

# Bake controller (AdminTheme)
docker compose exec willowcms bin/cake bake controller Dogs --theme AdminTheme

# Bake template (AdminTheme)
docker compose exec willowcms bin/cake bake template Dogs --theme AdminTheme

# Bake all (model, controller, templates)
docker compose exec willowcms bin/cake bake all Dogs --theme AdminTheme
```

### Queue Workers

**Required for AI features**:
```bash
# Start queue worker (verbose)
docker compose exec willowcms bin/cake queue worker --verbose

# Or with alias
cake_queue_worker_verbose
```

### Cache Management

```bash
# Clear all cache
docker compose exec willowcms bin/cake cache clear_all

# Clear specific cache
docker compose exec willowcms bin/cake cache clear _cake_core_
docker compose exec willowcms bin/cake cache clear _cake_model_
```

---

## 📝 Coding Standards

### Naming Conventions

Follow CakePHP 5.x conventions exactly:

- **Classes**: PascalCase (`ArticlesTable`, `ArticlesController`)
- **Methods**: camelCase (`findPublished()`, `getPopularArticles()`)
- **Variables**: camelCase (`$article`, `$userId`)
- **Database tables**: snake_case, plural (`articles`, `user_profiles`)
- **Database columns**: snake_case (`created_at`, `user_id`)
- **Controllers**: Plural (`ArticlesController`, not `ArticleController`)
- **Models**: Plural Tables, Singular Entities (`ArticlesTable`, `Article`)

### Code Style Examples

**Good - Follows CakePHP conventions:**
```php
// Controller action
public function view($id = null)
{
    $article = $this->Articles->get($id, contain: ['User', 'Tags']);
    $this->Authorization->authorize($article);
    $this->set(compact('article'));
}

// Table method
public function findPublished($query, array $options)
{
    return $query->where([
        'Articles.published' => true,
        'Articles.publish_date <=' => new DateTime(),
    ]);
}

// Entity virtual property
protected function _getFullName(): string
{
    return $this->first_name . ' ' . $this->last_name;
}
```

**Bad - Don't do this:**
```php
// Wrong: business logic in controller
public function view($id = null)
{
    $article = $this->Articles->find()
        ->where(['id' => $id])
        ->first();
    
    // DON'T: Complex logic belongs in model
    if ($article->status == 'published' && $article->date <= date('Y-m-d')) {
        // ...
    }
}

// Wrong: not following conventions
public function GetPublishedArticles() // Should be getPublishedArticles
{
    return $this->find()->where(['status' => 'published']); // Hardcoded status
}
```

### Security Best Practices

```php
// ALWAYS validate request methods
$this->request->allowMethod(['post', 'put']);

// ALWAYS escape output in templates
<?= h($article->title) ?>

// ALWAYS use authorization
$this->Authorization->authorize($article, 'edit');

// ALWAYS validate and sanitize input
$article = $this->Articles->patchEntity($article, $this->request->getData());

// NEVER trust user input
// BAD: 
$id = $this->request->getQuery('id');
$article = $this->Articles->find()->where(['id' => $id])->first();

// GOOD:
$id = (int)$this->request->getQuery('id');
$article = $this->Articles->get($id);
```

### Performance Best Practices

```php
// ALWAYS use eager loading to prevent N+1 queries
$articles = $this->Articles->find()
    ->contain(['User', 'Tags', 'Images'])
    ->where(['published' => true])
    ->all();

// Use caching for expensive operations
use Cake\Cache\Cache;

$result = Cache::remember('popular_articles', function() {
    return $this->Articles->getPopularArticles();
}, '1 hour');

// Use pagination for large datasets
$articles = $this->paginate($this->Articles->find('published'));
```

---

## 🔌 Plugin Structure

Willow uses a **plugin-based theming system**:

### AdminTheme Plugin

Admin backend with Bootstrap UI:
```
plugins/AdminTheme/
├── src/
│   ├── Controller/              # Admin controller overrides
│   └── Command/Bake/            # Custom bake templates
├── templates/
│   ├── layout/                  # Admin layouts
│   ├── element/                 # Reusable admin elements
│   └── [Model]/                 # Admin CRUD templates
└── webroot/
    ├── css/                     # Admin styles
    └── js/                      # Admin scripts
```

### DefaultTheme Plugin

Public-facing frontend:
```
plugins/DefaultTheme/
├── src/
│   └── Controller/              # Frontend controller overrides
├── templates/
│   ├── layout/                  # Frontend layouts
│   ├── element/                 # Reusable elements
│   └── [Model]/                 # Public templates
└── webroot/
    ├── css/                     # Public styles
    └── js/                      # Public scripts
```

### Creating Admin Controllers

```php
// ALWAYS use namespace for admin controllers
namespace App\Controller\Admin;

use App\Controller\AppController;

class ArticlesController extends AppController
{
    // Admin-specific logic
}
```

---

## 🌍 Internationalization (i18n)

Willow supports 25+ languages:

```php
// In code
__('Welcome message');
__n('One article', '{0} articles', $count);

// In templates
<?= __('Hello, {0}!', h($user->name)) ?>

// With domain
__d('admin', 'Dashboard');
```

Translation files: `cakephp/resources/locales/`

---

## 🚀 Development Workflow

### 1. Check Baseline

**ALWAYS start here:**
```bash
# Run all tests to check baseline
phpunit

# Check code standards
phpcs_sniff

# Static analysis
phpstan_analyse
```

### 2. Create Feature Branch

```bash
git checkout -b feature/my-new-feature
```

### 3. Follow TDD

1. Write failing test
2. Implement minimal code to pass
3. Refactor
4. Repeat

### 4. Code Generation

Use bake for consistency:
```bash
# Generate scaffolding
cake_shell bake all Dogs --theme AdminTheme
```

### 5. Test Continuously

```bash
# Run tests frequently
phpunit tests/TestCase/Controller/DogsControllerTest.php

# Check coverage
phpunit_cov_html
```

### 6. Code Quality

```bash
# Fix code standards
phpcs_fix

# Verify static analysis
phpstan_analyse
```

### 7. Final Validation

```bash
# Run full test suite
phpunit

# All quality checks
composer cs-check && composer stan
```

---

## 🎯 Common Patterns

### Search Functionality

Controllers handle search input, models implement logic:

```php
// In Controller
public function index()
{
    $query = $this->Articles->find('search', search: $this->request->getQuery());
    $articles = $this->paginate($query);
    $this->set(compact('articles'));
}

// In Table
public function findSearch($query, array $options)
{
    if (!empty($options['search']['keyword'])) {
        $query->where([
            'OR' => [
                'Articles.title LIKE' => '%' . $options['search']['keyword'] . '%',
                'Articles.body LIKE' => '%' . $options['search']['keyword'] . '%',
            ]
        ]);
    }
    return $query;
}
```

### Image Associations

Use the `ImageAssociable` behavior:

```php
// In Table
public function initialize(array $config): void
{
    parent::initialize($config);
    $this->addBehavior('ImageAssociable');
}

// Associate image
$this->Articles->associateImage($article->id, $image->id, 'gallery');

// Get associated images
$images = $this->Articles->getAssociatedImages($article->id, 'gallery');
```

### Slug Management

Use the `Sluggable` behavior:

```php
// In Table
public function initialize(array $config): void
{
    parent::initialize($config);
    $this->addBehavior('Sluggable', [
        'field' => 'title',
        'slug' => 'slug',
        'replacement' => '-',
        'unique' => true,
    ]);
}
```

### AI Integration

Use service classes in `src/Service/Api/`:

```php
use App\Service\Api\Anthropic\GenerateAltTextService;

$service = new GenerateAltTextService();
$altText = $service->generate($imageContent, $imageContext);
```

---

## 🐛 Debugging Tips

### Enable Debug Mode

In `config/.env`:
```
DEBUG=true
```

### Debug Output

```php
// Quick debug
debug($variable);

// Log debug info
$this->log($message, 'debug');

// Controller debug
$this->log($this->request->getData(), 'debug');
```

### Database Query Logging

```php
// Enable query logging in config
'Datasources' => [
    'default' => [
        'log' => true,
    ],
]
```

### Check Logs

```bash
# Application logs
tail -f cakephp/logs/debug.log
tail -f cakephp/logs/error.log

# Docker logs
docker compose logs -f willowcms
```

---

## 📚 Key Documentation References

- **CakePHP Book**: https://book.cakephp.org/5/en/index.html
- **Project README**: `/helper-files(use-only-if-you-get-lost)/docs/README.md`
- **Developer Guide**: `/helper-files(use-only-if-you-get-lost)/docs/DeveloperGuide.md`
- **CLAUDE.md**: `/helper-files(use-only-if-you-get-lost)/docs/CLAUDE.md`

---

## ⚠️ Things to NEVER Do

1. ❌ **Break existing tests** without explicit approval
2. ❌ **Commit secrets, API keys, or `.env` files**
3. ❌ **Introduce inconsistent patterns** (follow existing code style)
4. ❌ **Put business logic in controllers** (keep them thin)
5. ❌ **Put business logic in views** (presentation only)
6. ❌ **Skip input validation and sanitization**
7. ❌ **Use raw SQL** (use ORM unless absolutely necessary)
8. ❌ **Ignore code quality tools** (phpcs, phpstan)
9. ❌ **Make changes without understanding** (read the code first)
10. ❌ **Deploy without running the full test suite**

---

## ✅ Pre-Commit Checklist

Before committing ANY code:

- [ ] All tests pass (`phpunit`)
- [ ] Code standards check pass (`phpcs_sniff`)
- [ ] Static analysis passes (`phpstan_analyse`)
- [ ] New features have tests
- [ ] Documentation updated if needed
- [ ] No secrets or sensitive data in code
- [ ] Follows existing code patterns
- [ ] Comments added for complex logic
- [ ] No business logic in controllers or views
- [ ] Security best practices followed

---

## 🎉 Welcome Aboard!

You're now ready to contribute to Willow CMS! Remember:

- **Follow the patterns** - consistency is key
- **Test everything** - no excuses
- **Comment your code** - help future developers
- **Ask questions** - better to ask than break things
- **Be security-conscious** - user data is sacred

**Now go forth and code with confidence!** 🚀

---

<div align="center">
  <strong>🌿 Built with passion for the web development community</strong>
</div>
