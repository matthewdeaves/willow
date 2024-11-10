# Table of Contents

1. [Getting Started with Willow CMS Code](#getting-started-with-willow-cms-code)
   - [Useful Shell Aliases](#useful-shell-aliases)
   - [Key Code Folders](#key-code-folders)
   - [Command Line Tools](#command-line-tools)
   - [Controllers](#controllers)
   - [Models](#models)
   - [Templates](#templates)
   - [Theming with Plugins](#theming-with-plugins)

2. [Feature Development and Database Migrations](#feature-development-and-database-migrations)
   - [Development Process](#development-process)
   - [Using Cake Bake](#using-cake-bake)
   - [Applying Changes to Production](#applying-changes-to-production)
   - [Best Practices](#best-practices)
   - [Coding Standards via PHP CodeSniffer](#coding-standards-via-php-codesniffer)

3. [Unit Tests](#unit-tests)
   - [Running Unit Tests](#running-unit-tests)
   - [Testing Commands](#testing-commands)
   - [Code Coverage Reports](#code-coverage-reports)
   - [GitHub Actions](#gitHub-actions)

4. [Anthropic API Integration Classes](#anthropic-api-integration-classes)
   - [AbstractApiService](#1-abstractapiservice)
   - [AnthropicApiService](#2-anthropicapiservice)
   - [CommentAnalyzer](#3-commentanalyzer)
   - [ImageAnalyzer](#4-imageanalyzer)
   - [SeoContentGenerator](#5-seocontentgenerator)
   - [ArticleTagsGenerator](#6-articletagsgenerator)
   - [TextSummaryGenerator](#7-textsummarygenerator)
   - [TranslationGenerator](#8-translationgenerator)
   - [ClassesSummary](#classes-summary)

5. [Google Cloud Translate API Integration](#google-cloud-translate-api-integration)
   - [GoogleApiService](#1-googleapiservice)

6. [Environment Configuration](#environment-configuration-with-configenvexample)
   - [Steps to Use config/.env.example](#steps-to-use-configenvexample)
   - [Configuration Options](#configuration-options)

7. [Docker Development Environment](#docker-development-environment)

## Getting Started with Willow CMS Code

To help developers dive into the Willow CMS codebase, this section provides an overview of key components and resources. For a comprehensive understanding of CakePHP, which Willow CMS is built upon, refer to the [CakePHP Book](https://book.cakephp.org/5/en/index.html).

### Useful Shell Aliases and Git Hooks
If you build on Willow CMS you should make life easier with some shell aliases. You can auto set them (zshrc/bashrc detection included) by running `./setup_dev_aliases.sh` from the project root. [setup_dev_aliases.sh](https://raw.githubusercontent.com/matthewdeaves/willow/refs/heads/main/setup_dev_aliases.sh) will add a statement to simply load the aliases from [dev_aliases.txt](https://raw.githubusercontent.com/matthewdeaves/willow/refs/heads/main/dev_aliases.txt) when you start a new shell. Or you can manually add the aliases:

1. Copy the contents of `dev_aliases.sh`
2. Open your `~/.bashrc` or `.zshrc` file
3. Paste the contents at the end of the file
4. Save and close the file
5. Run `source ~/.bashrc` or `source ~/.zshrc` or restart your terminal

#### Git Push Hook
The `./setup_dev_aliases.sh` will also setup a Git Hook for your development environment which runs the PHPUnit tests prior to allowing a push. See [.git/hooks/pre-push](https://github.com/matthewdeaves/willow/blob/main/hooks/pre-push).

### Key Code Folders

#### Command Line Tools

The `src/Command` directory contains several useful CakePHP command line tools that can assist in various development tasks. These include:
   - `CreateUserCommand.php` - For creating new users.
   - `DefaultDataExportCommand.php` - To export default data.
   - `DefaultDataImportCommand.php` - To import default data.
   - `ExportCodeCommand.php` - For exporting code, useful for working with AI agents.
   - `ResizeImagesCommand.php` - To resize images.
   - `TestRateLimitCommand.php` - For testing rate limits (PHPUnit tests test this too, just useful to have a seperate tool.).

   [src/Command](https://github.com/matthewdeaves/willow/tree/main/src/Command)

#### Controllers

The `src/Controller` directory houses the controllers for the front-end site. The `/admin` backend controllers are kept separate in `src/Controller/Admin`, ensuring a clear distinction between the front-end and back-end logic.

   [src/Controller (front end)](https://github.com/matthewdeaves/willow/tree/main/src/Controller)

   [src/Controller (admin back end)](https://github.com/matthewdeaves/willow/tree/main/src/Controller/Admin)

#### Models

The `src/Model` directory contains the application's data models, which are crucial for interacting with the database and managing data logic.

   [src/Model](https://github.com/matthewdeaves/willow/tree/main/src/Model)

   There are also some key Behavior classes used across these Models

   [src/Model/Behavior](https://github.com/matthewdeaves/willow/tree/main/src/Model/Behavior)

#### Templates

The `plugins/DefaultTheme/templates` directory holds the templates for the default theme. Willow CMS uses plugins to facilitate easy theming, with themes residing in `plugins/AdminTheme` and `plugins/DefaultTheme`.

   [src/Model](https://github.com/matthewdeaves/willow/tree/main/src/Model)

### Theming with Plugins

Willow CMS leverages CakePHP plugins to simplify the theming process. The front-end site and `/admin` backend utilize these plugins, making it easy to customize and extend the CMS's appearance and functionality. Themes are located in `plugins/AdminTheme` and `plugins/DefaultTheme`, allowing for a modular approach to design.

   [plugins/AdminTheme](https://github.com/matthewdeaves/willow/tree/main/plugins/AdminTheme)

   [plugins/DefaultTheme](https://github.com/matthewdeaves/willow/tree/main/plugins/DefaultTheme)

## Feature Development and Database Migrations

When developing new features for Willow CMS that require changes to the database schema, you can leverage CakePHP's Migrations feature to manage schema upgrades efficiently. Follow these steps to ensure a smooth transition from development to production:

### Development Process

1. **Develop Your Feature**: 
   - Build your feature in your development environment.
   - Use phpMyAdmin or your preferred database management tool to modify the existing database schema as needed.

2. **Testing**:
   - Write comprehensive tests for your new feature.
   - Ensure all tests pass and the feature works as intended.

3. **Generate Migration Diff**:
   - Run the following command to create a migration diff:
     ```
     # using developer alias
     cake_shell bake migration_diff NameOfYourFeatureMigration
     
     # raw command
     docker compose exec willowcms bin/cake bake migration_diff NameOfYourFeatureMigration
     ```
   - This command generates a migration file capturing the differences in your database schema.

4. **Review Migration File**:
   - Check the generated migration file in `config/Migrations/`.
   - Ensure it accurately reflects your intended schema changes.

5. **Version Control**:
   - Commit your code changes along with the new migration file.
   - Push your changes to a feature branch in your repository.

6. **Code Review and Merge**:
   - Have your changes reviewed by team members.
   - Once approved, merge the feature branch into the main branch.

7. **Release**:
   - Create a new release of Willow CMS that includes your changes.

### Applying Changes to Production

8. **Upgrade Willow CMS**:
   - On any production instance of WillowCMS, upgrade to the latest release.

9. **Run Migrations**:
   - Apply the schema changes by running:
     ```
     bin/cake migrations migrate
     ```
   - CakePHP will handle the schema upgrade process.

### Using Cake Bake

Willow CMS uses [Bootstrap](https://getbootstrap.com/) and has support for code generation using cake bake with custom bake templates as part of the [AdminTheme](https://github.com/matthewdeaves/willow/tree/main/plugins/AdminTheme). If you have created your database table and followed CakePHP conventions you are ready to use bake to generate your model, view and controller code for the admin area.

For example, if you create a table to stored records about dogs:

```
CREATE TABLE `dogs` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `breed` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age` tinyint(11) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `color` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vaccinated` tinyint(1) DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `owner_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adopted_date` date DEFAULT NULL,
  `last_checkup` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

You can then use the CakePHP bake command to generate your model view controller code by specifying to use the AdminTheme templates:

```
# Using developer aliases
cake_shell bake model Dogs --theme AdminTheme
cake_shell bake controler Dogs --theme AdminTheme
cake_shell bake template Dogs --theme AdminTheme

# or the raw commands

docker compose exec willowcms bin/cake bake model Dogs --theme AdminTheme
docker compose exec willowcms bin/cake bake controler Dogs --theme AdminTheme
docker compose exec willowcms bin/cake bake template Dogs --theme AdminTheme
```

### Best Practices

- Always backup your production database before applying migrations.
- Test migrations in a staging environment that mirrors your production setup.
- Keep migration files small and focused on specific changes for easier management and rollback if needed.
- Use descriptive names for your migration files to easily identify their purpose.
- Read the book about [migrations](https://book.cakephp.org/migrations/4/en/index.html).

By following this workflow, you ensure that all database changes are version-controlled, tested, and can be easily applied across different environments.

### Coding Standards via PHP CodeSniffer

I use PHP CodeSniffer (phpcs) to maintain consistent coding standards across the project. This tool helps developers adhere to the CakePHP coding style, ensuring readability and uniformity throughout the codebase. These tools are run within the Docker development environment, making it easy to maintain code quality regardless of your local setup. Below are the commands you can use to check your code for style violations and automatically fix many common issues:

   - **Alias Commands**: 
     ```bash
     # to sniff for errors
     phpcs_sniff

     #to auto-fix fixable errors
     phpcs_fix
     ```
   - **Raw Commands**: 
     ```bash
     # to sniff for errors
     docker compose exec willowcms vendor/bin/phpcs --standard=vendor/cakephp/cakephp-codesniffer/CakePHP src/ tests/

     #to auto-fix fixable errors
     docker compose exec willowcms php vendor/bin/phpcbf
     ```

## Unit Tests

Unit testing is an integral part of maintaining code quality in Willow CMS. Tests are located in the `tests/TestCase` directory, with fixtures in `tests/Fixture`. Some particularly useful tests to examine include:
- `tests/TestCase/Controller/ArticlesControllerTest.php` - Tests related to the Articles controller.
- `tests/TestCase/Controller/UsersControllerTest.php` - Tests related to the Users controller.

   [tests/TestCase/Controller/ArticlesControllerTest.php](https://github.com/matthewdeaves/willow/blob/main/tests/TestCase/Controller/ArticlesControllerTest.php)

   [tests/TestCase/Controller/UsersControllerTest.php](https://github.com/matthewdeaves/willow/blob/main/tests/TestCase/Controller/UsersControllerTest.php)

With fixtures:

- `tests/Fixture/ArticlesFixture.php`
- `tests/Fixture/UsersFixture.php`

   [tests/Fixture/ArticlesFixture.php](https://github.com/matthewdeaves/willow/blob/main/tests/Fixture/ArticlesFixture.php)

   [tests/Fixture/UsersFixture.php](https://github.com/matthewdeaves/willow/blob/main/tests/Fixture/UsersFixture.php)

These tests provide valuable insights into how different components of the CMS are expected to function and can serve as a guide for writing new tests and dealing with mocking Authentication to simulate being logged in as a particular user.

By exploring these directories and resources, developers can gain a deeper understanding of the Willow CMS architecture and begin contributing effectively. For further guidance, the [CakePHP Book](https://book.cakephp.org/5/en/index.html) is an excellent resource for learning more about the framework's capabilities and best practices.

### Running Unit Tests

Unit testing is a crucial part of ensuring the quality and reliability of the Willow CMS codebase. By running unit tests, developers can verify that individual parts of the application function as expected. This section provides guidance on how to execute these tests using both alias commands and their raw command equivalents.

### Testing Commands

To streamline the process of running unit tests, several alias commands have been defined. These aliases simplify the execution of tests and the generation of code coverage reports. You can auto set them (zshrc/bashrc detection included) by running `./setup_dev_aliases.sh` from the project root. [setup_dev_aliases.sh](https://raw.githubusercontent.com/matthewdeaves/willow/refs/heads/main/setup_dev_aliases.sh) will add a statement to simply load the aliases from [dev_aliases.txt](https://raw.githubusercontent.com/matthewdeaves/willow/refs/heads/main/dev_aliases.txt) when you start a new shell.

Below are the available commands and their purposes:

1. **Running All Unit Tests**

   - **Alias Command**: 
     ```bash
     phpunit
     ```
   - **Raw Command**: 
     ```bash
     sudo docker compose exec willowcms php vendor/bin/phpunit
     ```

   This command runs all the unit tests in the application, providing a comprehensive check of the codebase.

2. **Generating Code Coverage Report (Text Format)**

   - **Alias Command**: 
     ```bash
     phpunit_cov
     ```
   - **Raw Command**: 
     ```bash
     sudo docker compose exec willowcms php vendor/bin/phpunit --coverage-text
     ```

   This command generates a code coverage report in text format, allowing developers to see which parts of the code are covered by tests.

3. **Generating Code Coverage Report (HTML Format)**

   - **Alias Command**: 
     ```bash
     phpunit_cov_html
     ```
   - **Raw Command**: 
     ```bash
     sudo docker compose exec willowcms php vendor/bin/phpunit --coverage-html webroot/coverage tests/TestCase/
     ```

   This command generates a detailed HTML code coverage report, which can be viewed in a web browser for a more visual representation of test coverage. This is also published to GitHub Pages and can be viewed [here]().

### Code Coverage Reports

- **Viewing HTML Coverage Reports**: After generating the HTML coverage report, you can view it by navigating to the `http://localhost:8080/coverage/index.html` directory in your web browser. The Willow CMS Admin backend also provides a link to the coverage report when running in debug mode. This provides a user-friendly interface to explore the coverage details. For example [http://localhost:8080/coverage/src/Controller/Admin/index.html](http://localhost:8080/coverage/src/Controller/Admin/index.html) will show you the test coverage details for all the admin backend controllers.

- **Customizing Test Runs**: If you need to run tests for specific components or filter tests, you can modify the commands accordingly. For example, you can specify a particular test case file or use PHPUnit's filtering options to run a subset of tests.

Running the Entire Test Case File:To run all the tests in the UsersControllerTest.php file, you can use:
bash

```
sudo docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Controller/UsersControllerTest.php
```

This command will execute all the test methods defined in the UsersControllerTest.php file.
Filtering Specific Test Methods:If you want to run only a subset of tests within the UsersControllerTest.php file, you can use the --filter option. For example, to run only the test methods that start with testLogin, you can use:
bash

```
sudo docker compose exec willowcms php vendor/bin/phpunit --filter testLogin tests/TestCase/Controller/UsersControllerTest.php
```

This command will run only the test methods whose names match the pattern testLogin.

### GitHub Actions
GitHub Actions will execute PHPUnit tests and PHP CodeSniffer on the main/development/staging branches and pull requests. Configuration for this is in [ci.yml](https://github.com/matthewdeaves/willow/blob/main/.github/workflows/ci.yml). Willow CMS is tested against PHP 8.1, 8.2 and 8.3.

## Anthropic API Integration Classes

Willow CMS leverages Anthropic's AI capabilities through a set of specialized classes. These classes are designed to facilitate various AI-driven features and can be extended or customized to build further integrations. Here's an overview of the key classes:

### 1. AbstractApiService
**Location**: `src/Service/Api/AbstractApiService.php`

   [src/Service/Api/AbstractApiService.php](https://github.com/matthewdeaves/willow/blob/main/src/Service/Api/AbstractApiService.php)

The AbstractApiService serves as a base class for API interactions. It provides common functionality such as:
- Sending HTTP POST requests with JSON payloads
- Managing API authentication using API keys
- Handling API errors by logging and throwing exceptions
- Providing a method to parse API responses
- Configuring request headers including API versioning

This abstract class can be extended to create services for interacting with other APIs, ensuring consistency across different integrations.

### 2. AnthropicApiService
**Location**: `src/Service/Api/AnthropicApiService.php`

   [src/Service/Api/Anthropic/AnthropicApiService.php](https://github.com/matthewdeaves/willow/blob/main/src/Service/Api/Anthropic/AnthropicApiService.php)

This class is a concrete implementation of AbstractApiService, specifically tailored for interacting with the Anthropic API. It handles:
- Managing API endpoints by defining the base URL and version for the Anthropic API
- Formating requests specifically for the Anthropic API by utilizing the SettingsManager to retrieve the API key and initializing the service with necessary configurations
- Processing responses by parsing the HTTP response from the API to extract and decode the relevant data
- Handling various AI-related tasks through specialized services for SEO content generation, image analysis, and comment moderation

### 3. CommentAnalyzer
**Location**: `src/Service/Api/Anthropic/CommentAnalyzer.php`

   [src/Service/Api/Anthropic/CommentAnalyzer.php](https://github.com/matthewdeaves/willow/blob/main/src/Service/Api/Anthropic/CommentAnalyzer.php)

This class is responsible for analyzing user comments using Anthropic's natural language processing capabilities. It is used for:
- Inappropriate comment content detection
- Flagging inappropriate comments so they do not appear

Developers can extend this class to implement more sophisticated comment moderation features or to extract valuable insights from user-generated content.

### 4. ImageAnalyzer
**Location**: `src/Service/Api/Anthropic/ImageAnalyzer.php`

   [src/Service/Api/Anthropic/ImageAnalyzer.php](https://github.com/matthewdeaves/willow/blob/main/src/Service/Api/Anthropic/ImageAnalyzer.php)

The ImageAnalyzer class utilizes Anthropic's image processing capabilities to analyze uploaded images. Its functionalities include:
- Generating descriptive SEO meta data for alternate text, keywords and a nice file name

This class could be extended to implement advanced image-based features, such as automatic tagging or content-aware image filtering.

### 5. SeoContentGenerator
**Location**: `src/Service/Api/Anthropic/SeoContentGenerator.php`

   [src/Service/Api/Anthropic/SeoContentGenerator.php](https://github.com/matthewdeaves/willow/blob/main/src/Service/Api/Anthropic/SeoContentGenerator.php)

This class leverages Anthropic's language generation capabilities to create SEO-optimized content. It is used for generating Article and Page meta data for social media, including:
- meta_title
- meta_description
- meta_keywords
- facebook_description
- linkedin_description
- twitter_description
- instagram_description

This class could be extended to create more advanced SEO tools or as a template to automate other content creation processes.

### 6. ArticleTagsGenerator
**Location**: `src/Service/Api/Anthropic/ArticleTagsGenerator.php`

   [src/Service/Api/Anthropic/ArticleTagsGenerator.php](https://github.com/matthewdeaves/willow/blob/main/src/Service/Api/Anthropic/ArticleTagsGenerator.php)

The ArticleTagsGenerator class utilizes Anthropic's natural language processing capabilities to generate relevant tags for articles. Its main functionalities include:
- Generating article tags based on the article's title and content
- Considering existing tags to avoid duplication

### 7. TextSummaryGenerator
**Location**: `src/Service/Api/Anthropic/TextSummaryGenerator.php`

   [src/Service/Api/Anthropic/TextSummaryGenerator.php](https://github.com/matthewdeaves/willow/blob/main/src/Service/Api/Anthropic/TextSummaryGenerator.php)

The TextSummaryGenerator class leverages Anthropic's advanced natural language processing capabilities to create concise summaries of text. Its primary functionality includes:
- Generating summaries based on the provided text and context

### 8. TranslationGenerator
**Location**: `src/Service/Api/Anthropic/TranslationGenerator.php`

   [src/Service/Api/Anthropic/TranslationGenerator.php](https://github.com/matthewdeaves/willow/blob/main/src/Service/Api/Anthropic/TranslationGenerator.php)

The TranslationGenerator class leverages Anthropic's advanced natural language processing capabilities to create translation of text. Bare in mind that as a general purpose model, translations into other languages may not be as accurate as dedicated services such as Google Translate. Its primary functionality includes:
- Generating translations of an array of strings from a locale to a locale

### Classes Summary
The Generator/Analyzer classes use the AipromptsTable to retrieve task-specific prompt data from the `aipromtps` table and serve as a foundation for developing specialized Anthropic API interactions or as a template for integrating other AI services.

## Google Cloud Translate API Integration

Willow CMS leverages Googles Cloud Translate API through a specialized class. This classes are designed to facilitate various tranlsation features and can be extended or customized to build further integrations. Here's an overview of the key classes:

### 1. GoogleApiService
**Location**: `src/Service/Api/Google/GoogleApiService.php`

   [src/Service/Api/Google/GoogleApiService.php](https://github.com/matthewdeaves/willow/blob/main/src/Service/Api/Google/GoogleApiService.php)

The GoogleApiService provides one method to translate an array of strings from one locale to another.

## Environment Configuration with `config/.env.example` 

The `config/.env.example` file serves as a template for configuring Willow CMS using environment variables. This file is designed to help you manage configuration settings that vary across different environments, such as development, testing, and production. By leveraging environment variables, you can streamline the configuration process and ensure that your application behaves appropriately in each environment. Defaults are provided for the development environment and you can use the `.env` file out of the box with developmen, but it is not necessary since calls to `env()` in the codebase already supply default values for the development environment.

### Steps to Use `config/.env.example`

1. **Copy the File**: Begin by copying the `config/.env.example` file to `config/.env`. This new file will be used to store your environment-specific configurations. Because Willow is built on the CakePHP framework everything is is setup to use this file immediately.

2. **Edit the `.env` File**: Customize the values in `config/.env` to suit your specific environment needs. This includes setting application configurations, database credentials, email settings, and more.

4. **Security Considerations**: It is important to note that having the `.env` file in a production environment is considered a **security risk** and can decrease the bootstrap performance of your application. Ensure that sensitive information is protected and that the `.env` file is not included in your version control system. The project `.gitignore` ignores `config.env` by default.

### Configuration Options

- **Application Configuration**: Set the application name, debug mode, encoding, locale, timezone, and security salt used for password hashing. See [section](https://github.com/matthewdeaves/willow/blob/853894a240441919dfa12273542cf4920e2fce30/config/.env.example#L17).

- **Database Configuration**: Define the database host, username, password, database name, and port. The `DATABASE_URL` variable provides a convenient way to configure the database connection string. See [section](https://github.com/matthewdeaves/willow/blob/853894a240441919dfa12273542cf4920e2fce30/config/.env.example#L26).

- **Email Configuration**: Configure the email host, port, timeout, and credentials. The `EMAIL_TRANSPORT_DEFAULT_URL` variable specifies the default email transport method. See [section](https://github.com/matthewdeaves/willow/blob/853894a240441919dfa12273542cf4920e2fce30/config/.env.example#L42).

- **Redis Configuration**: Set the Redis host, port, username, password, and database. Redis is used for caching and queue management, and the `REDIS_URL` variable simplifies the connection setup. See [section](https://github.com/matthewdeaves/willow/blob/853894a240441919dfa12273542cf4920e2fce30/config/.env.example#L52).

- **Cache Configuration**: Specify the cache duration and type. You can choose between Redis or File caching for the front-end site cache. [Read the book](https://book.cakephp.org/5/en/core-libraries/caching.html#) to learn more about cache types and configurations. See [config/app.php](https://github.com/matthewdeaves/willow/blob/ecbf5a0d9328cbb53faf91a7c98dcf1b04d6b4f1/config/app.php#L97) for cache configurations in Willow CMS.

- **Queue Configuration**: Define the default and test queue URLs using Redis. This setup is essential for managing background tasks and job queues. See [section](https://github.com/matthewdeaves/willow/blob/853894a240441919dfa12273542cf4920e2fce30/config/.env.example#L68).

- **Experimental Tests**: Toggle experimental tests on or off as needed. See [section](https://github.com/matthewdeaves/willow/blob/853894a240441919dfa12273542cf4920e2fce30/config/.env.example#L72).

By following these steps and guidelines, you can effectively use the `config/.env` file to manage Willow CMS configurations on a per-environment basis. For my own production instances of Willow CMS on AWS AppRunner see [https://github.com/matthewdeaves/willow_cms_production_deployment](https://github.com/matthewdeaves/willow_cms_production_deployment)

## Docker Development Environment
See [docker-compose.yml](https://raw.githubusercontent.com/matthewdeaves/willow/refs/heads/main/docker-compose.yml) and the [docker](https://github.com/matthewdeaves/willow/tree/main/docker) folder for how the docker images and containers are built.

#### Willow CMS
This is a combined nginx, redis and PHP-FPM container using Alpine Linux. The [docker/willowcms](https://github.com/matthewdeaves/willow/tree/main/docker/willowcms) folder has all the configuration that make it a little more open and therefore suitable for a development environment. In all other respects it is exactly the same as the production environment. Use [http://localhost:8080](http://localhost:8080) to use the development environment.

#### Jenkins 
Jenkins is pre-configured with a job that will checkout the repo and run the tests and code checks on the main branch. The [docker/jenkins](https://github.com/matthewdeaves/willow/tree/main/docker/jenkins) folder has all the configuration for this and more jobs will be added in future (front end tests for example). Use [http://localhost:8081](http://localhost:8081) to use Jenkins.

#### MySQL Server
Mostly configured via [docker-compose.yml](https://github.com/matthewdeaves/willow/blob/2a3dc5c9a3629b99797c586c938ed94a756b15fc/docker-compose.yml#L3) and loads an [init.sql](https://github.com/matthewdeaves/willow/blob/main/docker/mysql/init.sql) from [docker/mysql](https://github.com/matthewdeaves/willow/tree/main/docker/mysql). Default login is root:password

#### redis Commander
Configured via [docker-compose.yml](https://github.com/matthewdeaves/willow/blob/2a3dc5c9a3629b99797c586c938ed94a756b15fc/docker-compose.yml#L69). Use [http://localhost:8084](http://localhost:8084) for redis Commander interface. Default login is root:password

#### phpMyAdmin
Configured via [docker-compose.yml](https://github.com/matthewdeaves/willow/blob/2a3dc5c9a3629b99797c586c938ed94a756b15fc/docker-compose.yml#L37). Use [http://localhost:8082](http://localhost:8082) to access phpMyAdmin. It is pre-configured to access the MySQL Server container.

#### MailHog
Configured via [docker-compose.yml](https://github.com/matthewdeaves/willow/blob/2a3dc5c9a3629b99797c586c938ed94a756b15fc/docker-compose.yml#L63). Use [http://localhost:8025](http://localhost:8025) for MailHog. It will receive all email sent by Willow CMS on the development environment and give you a nice interface to view it.