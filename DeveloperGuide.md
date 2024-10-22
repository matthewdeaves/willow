# Table of Contents

1. [Getting Started with Willow CMS Code](#getting-started-with-willow-cms-code)
   - [Key Code Folders](#key-code-folders)
     - [Command Line Tools](#command-line-tools)
     - [Controllers](#controllers)
     - [Models](#models)
     - [Templates](#templates)
   - [Theming with Plugins](#theming-with-plugins)
   - [Unit Tests](#unit-tests)
   - [Running Unit Tests](#running-unit-tests)
     - [Testing Commands](#testing-commands)
     - [Code Coverage Reports](#code-coverage-reports)

2. [Anthropic API Integration Classes](#anthropic-api-integration-classes)
   - [AbstractApiService](#1-abstractapiservice)
   - [AnthropicApiService](#2-anthropicapiservice)
   - [CommentAnalyzer](#3-commentanalyzer)
   - [ImageAnalyzer](#4-imageanalyzer)
   - [SeoContentGenerator](#5-seocontentgenerator)
   - [ArticleTagsGenerator](#6-articletagsgenerator)
   - [TextSummaryGenerator](#7-textsummarygenerator)
   - [ClassesSummary](#classes-summary)

3. [Environment Configuration](#environment-configuration-with-configenvexample)
   - [Steps to Use config/.env.example](#steps-to-use-configenvexample)
   - [Configuration Options](#configuration-options)

## Getting Started with Willow CMS Code

To help developers dive into the Willow CMS codebase, this section provides an overview of key components and resources. For a comprehensive understanding of CakePHP, which Willow CMS is built upon, refer to the [CakePHP Book](https://book.cakephp.org/5/en/index.html).

### Key Code Folders

1. **Command Line Tools**: The `src/Command` directory contains several useful CakePHP command line tools that can assist in various development tasks. These include:
   - `CreateUserCommand.php` - For creating new users.
   - `DefaultDataExportCommand.php` - To export default data.
   - `DefaultDataImportCommand.php` - To import default data.
   - `ExportCodeCommand.php` - For exporting code, useful for working with AI agents.
   - `ResizeImagesCommand.php` - To resize images.
   - `TestRateLimitCommand.php` - For testing rate limits (PHPUnit tests test this too, just useful to have a seperate tool.).

   [src/Command](https://github.com/matthewdeaves/willow/tree/main/src/Command)

2. **Controllers**: The `src/Controller` directory houses the controllers for the front-end site. The `/admin` backend controllers are kept separate in `src/Controller/Admin`, ensuring a clear distinction between the front-end and back-end logic.

   [src/Controller (front end)](https://github.com/matthewdeaves/willow/tree/main/src/Controller)

   [src/Controller (admin back end)](https://github.com/matthewdeaves/willow/tree/main/src/Controller/Admin)

3. **Models**: The `src/Model` directory contains the application's data models, which are crucial for interacting with the database and managing data logic.

   [src/Model](https://github.com/matthewdeaves/willow/tree/main/src/Model)

   There are also some key Behavior classes used across these Models

   [src/Model/Behavior](https://github.com/matthewdeaves/willow/tree/main/src/Model/Behavior)

4. **Templates**: The `plugins/DefaultTheme/templates` directory holds the templates for the default theme. Willow CMS uses plugins to facilitate easy theming, with themes residing in `plugins/AdminTheme` and `plugins/DefaultTheme`.

   [src/Model](https://github.com/matthewdeaves/willow/tree/main/src/Model)

### Theming with Plugins

Willow CMS leverages CakePHP plugins to simplify the theming process. The front-end site and `/admin` backend utilize these plugins, making it easy to customize and extend the CMS's appearance and functionality. Themes are located in `plugins/AdminTheme` and `plugins/DefaultTheme`, allowing for a modular approach to design.

   [plugins/AdminTheme](https://github.com/matthewdeaves/willow/tree/main/plugins/AdminTheme)

   [plugins/DefaultTheme](https://github.com/matthewdeaves/willow/tree/main/plugins/DefaultTheme)

### Unit Tests

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

#### Running Unit Tests

Unit testing is a crucial part of ensuring the quality and reliability of the Willow CMS codebase. By running unit tests, developers can verify that individual parts of the application function as expected. This section provides guidance on how to execute these tests using both alias commands and their raw command equivalents.

#### Testing Commands

To streamline the process of running unit tests, several alias commands have been defined. These aliases simplify the execution of tests and the generation of code coverage reports. You can auto set them (zshrc/bashrc detection included) by running `./setup_dev_aliases.sh` from the project root. [setup_dev_aliases.sh](https://raw.githubusercontent.com/matthewdeaves/willow/refs/heads/main/setup_dev_aliases.sh) will add a statement to simply load the aliases from [dev_aliases.txt](https://raw.githubusercontent.com/matthewdeaves/willow/refs/heads/main/dev_aliases.txt) when you start a new shell.

Below are the available commands and their purposes:

1. **Running All Unit Tests**

   - **Alias Command**: 
     ```bash
     phpunit
     ```
   - **Raw Command**: 
     ```bash
     sudo docker compose exec php php vendor/bin/phpunit
     ```

   This command runs all the unit tests in the application, providing a comprehensive check of the codebase.

2. **Generating Code Coverage Report (Text Format)**

   - **Alias Command**: 
     ```bash
     phpunit_cov
     ```
   - **Raw Command**: 
     ```bash
     sudo docker compose exec php php vendor/bin/phpunit --coverage-text
     ```

   This command generates a code coverage report in text format, allowing developers to see which parts of the code are covered by tests.

3. **Generating Code Coverage Report (HTML Format)**

   - **Alias Command**: 
     ```bash
     phpunit_cov_html
     ```
   - **Raw Command**: 
     ```bash
     sudo docker compose exec php php vendor/bin/phpunit --coverage-html webroot/coverage tests/TestCase/
     ```

   This command generates a detailed HTML code coverage report, which can be viewed in a web browser for a more visual representation of test coverage. This is also published to GitHub Pages and can be viewed [here]().

#### Code Coverage Reports

- **Viewing HTML Coverage Reports**: After generating the HTML coverage report, you can view it by navigating to the `http://localhost:8080/coverage/index.html` directory in your web browser. The Willow CMS Admin backend also provides a link to the coverage report when running in debug mode. This provides a user-friendly interface to explore the coverage details. For example [http://localhost:8080/coverage/src/Controller/Admin/index.html](http://localhost:8080/coverage/src/Controller/Admin/index.html) will show you the test coverage details for all the admin backend controllers.

- **Customizing Test Runs**: If you need to run tests for specific components or filter tests, you can modify the commands accordingly. For example, you can specify a particular test case file or use PHPUnit's filtering options to run a subset of tests.

Running the Entire Test Case File:To run all the tests in the UsersControllerTest.php file, you can use:
bash

```
sudo docker compose exec php php vendor/bin/phpunit tests/TestCase/Controller/UsersControllerTest.php
```

This command will execute all the test methods defined in the UsersControllerTest.php file.
Filtering Specific Test Methods:If you want to run only a subset of tests within the UsersControllerTest.php file, you can use the --filter option. For example, to run only the test methods that start with testLogin, you can use:
bash

```
sudo docker compose exec php php vendor/bin/phpunit --filter testLogin tests/TestCase/Controller/UsersControllerTest.php
```

This command will run only the test methods whose names match the pattern testLogin.

### Anthropic API Integration Classes

Willow CMS leverages Anthropic's AI capabilities through a set of specialized classes. These classes are designed to facilitate various AI-driven features and can be extended or customized to build further integrations. Here's an overview of the key classes:

#### 1. AbstractApiService
**Location**: `src/Service/Api/AbstractApiService.php`

   [src/Service/Api/AbstractApiService.php](https://github.com/matthewdeaves/willow/blob/main/src/Service/Api/AbstractApiService.php)

The AbstractApiService serves as a base class for API interactions. It provides common functionality such as:
- Sending HTTP POST requests with JSON payloads
- Managing API authentication using API keys
- Handling API errors by logging and throwing exceptions
- Providing a method to parse API responses
- Configuring request headers including API versioning

This abstract class can be extended to create services for interacting with other APIs, ensuring consistency across different integrations.

#### 2. AnthropicApiService
**Location**: `src/Service/Api/AnthropicApiService.php`

   [src/Service/Api/AnthropicApiService.php](https://github.com/matthewdeaves/willow/blob/main/src/Service/Api/AbstractApiService.php)

This class is a concrete implementation of AbstractApiService, specifically tailored for interacting with the Anthropic API. It handles:
- Managing API endpoints by defining the base URL and version for the Anthropic API
- Formating requests specifically for the Anthropic API by utilizing the SettingsManager to retrieve the API key and initializing the service with necessary configurations
- Processing responses by parsing the HTTP response from the API to extract and decode the relevant data
- Handling various AI-related tasks through specialized services for SEO content generation, image analysis, and comment moderation

#### 3. CommentAnalyzer
**Location**: `src/Service/Api/Anthropic/CommentAnalyzer.php`

   [src/Service/Api/Anthropic/CommentAnalyzer.php](https://github.com/matthewdeaves/willow/blob/main/src/Service/Api/Anthropic/CommentAnalyzer.php)

This class is responsible for analyzing user comments using Anthropic's natural language processing capabilities. It is used for:
- Inappropriate comment content detection
- Flagging inappropriate comments so they do not appear

Developers can extend this class to implement more sophisticated comment moderation features or to extract valuable insights from user-generated content.

#### 4. ImageAnalyzer
**Location**: `src/Service/Api/Anthropic/ImageAnalyzer.php`

   [src/Service/Api/Anthropic/ImageAnalyzer.php](https://github.com/matthewdeaves/willow/blob/main/src/Service/Api/Anthropic/ImageAnalyzer.php)

The ImageAnalyzer class utilizes Anthropic's image processing capabilities to analyze uploaded images. Its functionalities include:
- Generating descriptive SEO meta data for alternate text, keywords and a nice file name

This class could be extended to implement advanced image-based features, such as automatic tagging or content-aware image filtering.

#### 5. SeoContentGenerator
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

#### 6. ArticleTagsGenerator
**Location**: `src/Service/Api/Anthropic/ArticleTagsGenerator.php`

   [src/Service/Api/Anthropic/ArticleTagsGenerator.php](https://github.com/matthewdeaves/willow/blob/main/src/Service/Api/Anthropic/ArticleTagsGenerator.php)

The ArticleTagsGenerator class utilizes Anthropic's natural language processing capabilities to generate relevant tags for articles. Its main functionalities include:
- Generating article tags based on the article's title and content
- Considering existing tags to avoid duplication

#### 7. TextSummaryGenerator
**Location**: `src/Service/Api/Anthropic/TextSummaryGenerator.php`

   [src/Service/Api/Anthropic/TextSummaryGenerator.php](https://github.com/matthewdeaves/willow/blob/main/src/Service/Api/Anthropic/TextSummaryGenerator.php)

The TextSummaryGenerator class leverages Anthropic's advanced natural language processing capabilities to create concise summaries of text. Its primary functionality includes:
- Generating summaries based on the provided text and context

##### Classes Summary
The Generator/Analyzer classes use the AipromptsTable to retrieve task-specific prompt data from the `aipromtps` table and serve as a foundation for developing specialized Anthropic API interactions or as a template for integrating other AI services.

### Environment Configuration with `config/.env.example` 

The `config/.env.example` file serves as a template for configuring Willow CMS using environment variables. This file is designed to help you manage configuration settings that vary across different environments, such as development, testing, and production. By leveraging environment variables, you can streamline the configuration process and ensure that your application behaves appropriately in each environment. Defaults are provided for the development environment and you can use the `.env` file out of the box with developmen, but it is not necessary since calls to `env()` in the codebase already supply default values for the development environment.

#### Steps to Use `config/.env.example`

1. **Copy the File**: Begin by copying the `config/.env.example` file to `config/.env`. This new file will be used to store your environment-specific configurations. Because Willow is built on the CakePHP framework everything is is setup to use this file immediately.

2. **Edit the `.env` File**: Customize the values in `config/.env` to suit your specific environment needs. This includes setting application configurations, database credentials, email settings, and more.

4. **Security Considerations**: It is important to note that having the `.env` file in a production environment is considered a **security risk** and can decrease the bootstrap performance of your application. Ensure that sensitive information is protected and that the `.env` file is not included in your version control system. The project `.gitignore` ignores `config.env` by default.

### Configuration Options

- **Application Configuration**: Set the application name, debug mode, encoding, locale, timezone, and security salt used for password hashing.

- **Database Configuration**: Define the database host, username, password, database name, and port. The `DATABASE_URL` variable provides a convenient way to configure the database connection string.

- **Email Configuration**: Configure the email host, port, timeout, and credentials. The `EMAIL_TRANSPORT_DEFAULT_URL` variable specifies the default email transport method.

- **Redis Configuration**: Set the Redis host, port, username, password, and database. Redis is used for caching and queue management, and the `REDIS_URL` variable simplifies the connection setup.

- **Cache Configuration**: Specify the cache duration and type. You can choose between Redis or File caching for the front-end site cache. [Read the book](https://book.cakephp.org/5/en/core-libraries/caching.html#) to learn more about cache types and configurations. See [config/app.php](https://github.com/matthewdeaves/willow/blob/ecbf5a0d9328cbb53faf91a7c98dcf1b04d6b4f1/config/app.php#L97) for cache configurations in Willow CMS.

- **Queue Configuration**: Define the default and test queue URLs using Redis. This setup is essential for managing background tasks and job queues.

- **Experimental Tests**: Toggle experimental tests on or off as needed.

- **Logging Configuration**: Uncomment and configure logging settings via environment variables to manage log levels and file paths.

By following these steps and guidelines, you can effectively use the `config/.env` file to manage Willow CMS configurations on a per-environment basis. For my own production instances of Willow CMS on AWS AppRunner see [https://github.com/matthewdeaves/willow_cms_production_deployment](https://github.com/matthewdeaves/willow_cms_production_deployment)