# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Essential Commands

### Development Environment

The project uses Docker for development. All commands should be run through Docker containers:

```bash
# Start development environment
./setup_dev_env.sh

# Access the container shell
docker compose exec -it willowcms /bin/sh
```

### Testing

```bash
# Run all tests
docker compose exec willowcms php vendor/bin/phpunit

# Run specific test
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Controller/UsersControllerTest.php

# Run tests with coverage (text)
docker compose exec willowcms php vendor/bin/phpunit --coverage-text

# Run tests with coverage (HTML)
docker compose exec willowcms php vendor/bin/phpunit --coverage-html webroot/coverage tests/TestCase/
```

### Code Quality

```bash
# PHP CodeSniffer - check for coding standard violations
docker compose exec willowcms vendor/bin/phpcs --standard=vendor/cakephp/cakephp-codesniffer/CakePHP src/ tests/

# PHP CodeSniffer - auto-fix violations
docker compose exec willowcms php vendor/bin/phpcbf

# PHPStan - static analysis (level 5)
docker compose exec willowcms php vendor/bin/phpstan analyse src/
```

### Database & Cache

```bash
# Run migrations
docker compose exec willowcms bin/cake migrations migrate

# Create migration diff
docker compose exec willowcms bin/cake bake migration_diff YourMigrationName

# Clear all cache
docker compose exec willowcms bin/cake cache clear_all
```

### Queue Workers

```bash
# Start queue worker (required for AI features, image processing)
docker compose exec willowcms bin/cake queue worker --verbose
```

### Baking (Code Generation)

```bash
# Bake model/controller/template with AdminTheme
docker compose exec willowcms bin/cake bake model Dogs --theme AdminTheme
docker compose exec willowcms bin/cake bake controller Dogs --theme AdminTheme
docker compose exec willowcms bin/cake bake template Dogs --theme AdminTheme
```

## High-Level Architecture

### Core Design Patterns

1. **MVC Architecture**: Built on CakePHP 5.x following strict MVC patterns
   - Models: `src/Model/Table/` and `src/Model/Entity/`
   - Views: Theme-based templates in `plugins/*/templates/`
   - Controllers: `src/Controller/` (frontend) and `src/Controller/Admin/` (backend)

2. **Plugin-Based Theming**: Frontend and admin interfaces are separate plugins
   - `plugins/DefaultTheme/` - Public-facing website theme
   - `plugins/AdminTheme/` - Administrative backend theme

3. **Behavior System**: Reusable model behaviors for common functionality
   - `ImageAssociableBehavior` - Handles image associations across models
   - `SlugBehavior` - Manages URL-friendly slugs with history tracking
   - `OrderableBehavior` - Provides drag-and-drop ordering
   - `CommentableBehavior` - Adds commenting functionality

4. **Queue-Based Processing**: Heavy operations are offloaded to background jobs
   - Image resizing and analysis (`ProcessImageJob`, `ImageAnalysisJob`)
   - AI content generation (`ArticleSeoUpdateJob`, `ArticleTagUpdateJob`)
   - Email sending (`SendEmailJob`)
   - Translation tasks (`TranslateArticleJob`, `TranslateI18nJob`)

5. **AI Integration Architecture**: Modular API service design
   - `AbstractApiService` - Base class for all API integrations
   - `AnthropicApiService` - Anthropic Claude API implementation
   - `GoogleApiService` - Google Translate API implementation
   - Specialized generators/analyzers for specific AI tasks

### Key Architectural Decisions

1. **Multi-Language First**: Built-in i18n support with TranslateBehavior on core models
   - Articles, Tags support automatic translation
   - AI-powered translation via Anthropic/Google APIs
   - Locale-aware routing and content delivery

2. **SEO-Centric Design**: Every content type has comprehensive SEO fields
   - Meta titles, descriptions, keywords
   - Social media-specific descriptions (Facebook, Twitter, LinkedIn, Instagram)
   - AI-powered SEO content generation

3. **Security Layers**:
   - IP blocking middleware (`IpBlockerMiddleware`)
   - Rate limiting middleware (`RateLimitMiddleware`)
   - CSRF protection enabled by default
   - Authentication using CakePHP's Authentication plugin

4. **Caching Strategy**: Multi-layer caching approach
   - Redis for queue management and cache storage
   - File-based cache fallback
   - View caching for anonymous users
   - Query caching for expensive operations

5. **Image Management**: Sophisticated image handling system
   - Multiple size generation on upload
   - AI-powered alt text and keyword generation
   - CDN-ready URL generation
   - Association tracking via pivot table

### Database Design Philosophy

- UUID primary keys for all tables (distributed-system ready)
- Soft deletes where appropriate (e.g., users)
- Audit fields on all tables (created, modified, created_by, modified_by)
- Normalized design with strategic denormalization for performance
- Slug history tracking for SEO-friendly URL changes

### Integration Points

1. **Anthropic Claude API**: Used for content analysis and generation
   - Comment moderation
   - Image analysis
   - SEO content generation
   - Article summarization
   - Tag generation

2. **Google Translate API**: Professional translation service
   - More accurate than general-purpose AI for translations
   - Batch translation support
   - Automatic locale detection

3. **Redis**: Cache and queue backend
   - Queue job storage and processing
   - Session storage in production
   - Cache storage for high-traffic sites

### Development Workflow Considerations

- Always run queue workers when testing AI features
- Use Docker environment for consistency
- Follow CakePHP 5.2 conventions strictly (naming, file locations)
- Test with multiple languages enabled when working on i18n features
- Check rate limits when testing API integrations