# Willow CMS Directory Structure Reference

## Project Overview

Willow CMS is a PHP-based content management system built with CakePHP framework, containerized with Docker.

## Complete Directory Tree Structure

```text
willow/
├── 📄 Configuration Files (Root Level)
│   ├── AI_IMPROVEMENTS_IMPLEMENTATION_PLAN.md    # AI enhancement roadmap
│   ├── CLAUDE.md                                 # Claude AI interaction guide
│   ├── composer.json                             # PHP dependencies definition
│   ├── composer.lock                             # Locked dependency versions
│   ├── dev_aliases.txt                           # Development command aliases
│   ├── DeveloperGuide.md                         # Developer documentation
│   ├── docker-compose.yml                        # Docker services configuration
│   ├── dump.rdb                                  # Redis database dump
│   ├── HELPER.md                                 # This reference file
│   ├── index.php                                 # Application entry point
│   ├── LICENSE                                   # Project license
│   ├── manage.sh                                 # Project management script
│   ├── phpcs.xml                                 # PHP Code Sniffer config
│   ├── phpstan.neon                              # PHPStan static analysis config
│   ├── phpunit.xml.dist                          # PHPUnit testing config
│   ├── psalm.xml                                 # Psalm static analysis config
│   ├── README.md                                 # Project documentation
│   ├── REFACTORING_PLAN.md                       # Code refactoring plan
│   ├── setup_dev_aliases.sh                      # Development alias setup
│   ├── setup_dev_env.sh                          # Development environment setup
│   └── wait-for-it.sh                           # Docker service wait utility
│
├── 🎨 assets/                                    # Static assets & branding
│   ├── favicon.ico                               # Website favicon
│   ├── icon-text.png                             # Logo with text
│   ├── icon-text.xcf                             # GIMP source file
│   ├── icon.png                                  # Main icon
│   ├── text.xcf                                  # Text design source
│   ├── willow-text.xcf                          # Willow text logo source
│   └── willow.png                               # Willow brand image
│
├── ⚙️ bin/                                       # Executable scripts
│   ├── bash_completion.sh                        # Bash auto-completion
│   ├── cake                                      # CakePHP console (Unix)
│   ├── cake.bat                                  # CakePHP console (Windows)
│   └── cake.php                                  # CakePHP console PHP script
│
├── 🔧 config/                                    # Application configuration
│   ├── app_local.example.php                     # Local config template
│   ├── app_local.php                             # Local environment config
│   ├── app.php                                   # Main application config
│   ├── bootstrap_cli.php                         # CLI bootstrap
│   ├── bootstrap.php                             # Application bootstrap
│   ├── log_config.php                            # Logging configuration
│   ├── paths.php                                 # Path definitions
│   ├── plugins.php                               # Plugin configuration
│   ├── routes.php                                # URL routing rules
│   ├── security.php                              # Security settings
│   ├── Migrations/                               # Database migration files
│   └── schema/                                   # Database schema definitions
│
├── 📊 default_data/                              # Default/seed data (JSON)
│   ├── aiprompts.json                            # AI prompt templates
│   ├── articles_tags.json                        # Article-tag relationships
│   ├── articles_translations.json                # Article translations
│   ├── articles.json                             # Default articles
│   ├── blocked_ips.json                          # IP blocking data
│   ├── comments.json                             # Default comments
│   ├── cookie_consents.json                      # Cookie consent records
│   ├── email_templates.json                      # Email template data
│   ├── image_galleries_images.json               # Gallery-image links
│   ├── image_galleries_translations.json         # Gallery translations
│   ├── image_galleries.json                      # Image galleries
│   ├── images.json                               # Image metadata
│   ├── internationalisations.json                # i18n strings
│   ├── models_images.json                        # Model-image relationships
│   ├── page_views.json                           # Page view analytics
│   ├── phinxlog.json                             # Migration logs
│   ├── settings.json                             # System settings
│   ├── slugs.json                                # URL slugs
│   ├── system_logs.json                          # System log entries
│   ├── tags_translations.json                    # Tag translations
│   ├── tags.json                                 # Content tags
│   ├── user_account_confirmations.json           # Account confirmations
│   └── users.json                                # Default users
│
├── 🐳 docker/                                    # Docker-related files
│   ├── docker-volume-exports/                    # Volume backup exports
│   ├── github/                                   # GitHub integration configs
│   ├── jenkins/                                  # Jenkins CI/CD configs
│   ├── mysql/                                    # MySQL Docker configs
│   └── willowcms/                                # Willow CMS Docker configs
│
├── 🔗 hooks/                                     # Git hooks
│   └── pre-push                                  # Pre-push validation script
│
├── 📝 logs/                                      # Application logs
│   ├── debug.log                                 # Debug information
│   ├── error.log                                 # Error logs
│   └── nginx/                                    # Nginx server logs
│
├── 🔌 plugins/                                   # CakePHP plugins
│   ├── AdminTheme/                               # Admin interface theme
│   └── DefaultTheme/                             # Default frontend theme
│
├── 💾 project_files_backups/                    # File backups
│   └── files_backup_20250701_172137.tar.gz      # Timestamped backup
│
├── 🗄️ project_mysql_backups/                    # Database backups
│
├── 🌍 resources/                                 # Resource files
│   └── locales/                                  # Internationalization files
│
├── 💻 src/                                       # Source code (CakePHP MVC)
│   ├── Application.php                           # Main application class
│   ├── Command/                                  # CLI commands
│   ├── Console/                                  # Console utilities
│   ├── Controller/                               # MVC Controllers
│   ├── Error/                                    # Error handling
│   ├── Http/                                     # HTTP layer
│   ├── Job/                                      # Background jobs
│   ├── Log/                                      # Custom logging
│   ├── Middleware/                               # HTTP middleware
│   ├── Model/                                    # MVC Models (Entity/Table)
│   ├── Service/                                  # Business logic services
│   ├── Utility/                                  # Helper utilities
│   └── View/                                     # View helpers
│
├── 🖼️ templates/                                 # View templates (Twig/PHP)
│   ├── cell/                                     # View cells
│   ├── element/                                  # Reusable elements
│   ├── email/                                    # Email templates
│   ├── Error/                                    # Error page templates
│   └── layout/                                   # Layout templates
│
├── 🧪 tests/                                     # Test suite
│   ├── bootstrap.php                             # Test bootstrap
│   ├── schema.sql                                # Test database schema
│   ├── Fixture/                                  # Test data fixtures
│   ├── TestCase/                                 # Test cases
│   └── Traits/                                   # Test helper traits
│
├── 📁 tmp/                                       # Temporary files
│   ├── debug_kit.sqlite                          # Debug kit database
│   ├── cache/                                    # Application cache
│   ├── sessions/                                 # Session storage
│   └── tests/                                    # Test temp files
│
├── 🛠️ tool_modules/                              # Management tool modules
│   ├── asset_management.sh                       # Asset handling tools
│   ├── common.sh                                 # Common utilities
│   ├── data_management.sh                        # Data management tools
│   ├── internationalization.sh                   # i18n tools
│   ├── service_checks.sh                         # Service health checks
│   ├── system.sh                                 # System operations
│   └── ui.sh                                     # UI management tools
│
├── 📦 vendor/                                    # Composer dependencies
│   ├── autoload.php                              # Composer autoloader
│   └── ...                                       # Third-party packages
│
└── 🌐 webroot/                                   # Public web directory
    ├── favicon.ico                               # Public favicon
    └── ...                                       # CSS, JS, images, uploads
```

## Key Directory Purposes

### 🏗️ **Core Architecture**

- **`src/`** - Main application code following CakePHP MVC pattern
- **`config/`** - All configuration files for app, database, routes
- **`templates/`** - View layer templates and layouts

### 🔧 **Development & Deployment**

- **`docker/`** - Containerization configs for different environments
- **`tool_modules/`** - Custom management scripts for various operations
- **`tests/`** - Comprehensive test suite with fixtures

### 📊 **Data & Content**

- **`default_data/`** - Seed data for initial system setup
- **`webroot/`** - Publicly accessible files (CSS, JS, uploads)
- **`plugins/`** - Modular functionality extensions

### 🔍 **Monitoring & Maintenance**

- **`logs/`** - Application and server logs
- **`project_*_backups/`** - Automated backup storage
- **`tmp/`** - Temporary files and cache

## Development Workflow

1. Use `manage.sh` for common project operations
2. Docker Compose manages the development environment
3. CakePHP console (`bin/cake`) for CLI operations
4. PHPUnit for testing, PHPStan/Psalm for static analysis

## Getting Started

Run `./setup_dev_env.sh` to initialize the development environment with all necessary dependencies and configurations.