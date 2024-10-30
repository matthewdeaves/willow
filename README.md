# Willow CMS - Easy-to-Use Content Management System Built with CakePHP 5.x

![Build Status](https://github.com/matthewdeaves/willow/workflows/CI/badge.svg)

## Table of Contents
1. [Docker Development Environment](#docker-development-environment)
   - [Quick Start - Mac OS X / Ubuntu](#quick-start---mac-os-x--ubuntu)
2. [Anthropic API Integration](#anthropic-api-integration)
3. [Queues and Consumers](#queues-and-consumers)
4. [Development Guide](#development-guide)
   - [Detailed Development Guide](https://github.com/matthewdeaves/willow/blob/main/DeveloperGuide.md)
5. [Production Environment](#production-environment)

## Docker Development Environment
Docker is used to host everything you need for a development environment: Nginx, PHP, MySQL, Redis, PHPMyAdmin, MailHog and Jenkins. The only thing you need on your host machine is [Docker](https://www.docker.com).

### Quick Start - Mac OS X / Ubuntu

Follow these steps:

```
#Clone the repo (or download a release from https://github.com/matthewdeaves/willow/releases)
git clone git@github.com:matthewdeaves/willow.git

#Change directory
cd willow/

#Run the setup script
./setup_dev_env.sh
```

On your first run, the development environment will be setup and you can visit [http://localhost:8080](http://localhost:8080) to use Willow CMS. On subsequent runs of `setup_dev_env.sh` you are given options to wipe the container volumes, rebuild docker containers or restart the docker containers.

Login to the Willow CMS admin area at [http://localhost:8080/admin](http://localhost:8080/admin) using `admin@test.com` & `password`.

This is all handled by `./setup_dev_env.sh` which is installing dependencies via [Composer](https://getcomposer.org/), running the database migration, creating a user and importing default data all on the docker development environment. [View the setup_dev_env.sh script](https://github.com/matthewdeaves/willow/blob/main/setup_dev_env.sh)

### Queues and Consumers
Willow CMS uses queues and consumers to offload heavy duty tasks to background processes. This includes things like image processing/resizing and making calls to the Anthropic API. On the development environment, queue workers are not started automatically. This means if you upload an image or perform a task that offloads a message to the queue for a worker to pick up (like article translation or SEO texts generation), you will need to start a queue worker. You can start a queue worker process like this:

- **Alias Command**: (see [Useful Shell Aliases](https://github.com/matthewdeaves/willow/blob/main/DeveloperGuide.md#useful-shell-aliases))
```
cake_queue_worker
```
- **Raw Command**: 
```
docker compose exec willowcms bin/cake queue worker --verbose
```
Leave the queue worker running in a terminal to see useful output as it picks up and runs [jobs](https://github.com/matthewdeaves/willow/tree/main/src/Job). Remember to save your Anthropic API key in the settings page.

### Anthropic and Google Translate API Integration
Willow integrates the [Anthropic API](https://console.anthropic.com/dashboard) and the [Google Translate API](https://cloud.google.com/translate) for some nice features:

* Automatic translation of articles/pages and tags into up to 25 languages
* Generation of SEO related texts for Article/Pages and Tags based on their content (also translated)
* Generation of image alternate text, keywords, nice file names, descriptions and keywords
* Auto Tagging of Articles/Pages (automatically create Tags based on content)
* Automatic moderation of comments
* A lot more to come...

To use these feautures go to the settings page [http://localhost:8080/admin/settings](http://localhost:8080/admin/settings) and
1. add your API keys
2. enable the AI setting
3. enable the AI jobs that you want to have run automatically
4. enable the languages/locales you want for translation
5. Make sure you have started a queue runner process in your development environment (see above)

## Development Guide

The Willow CMS [Developer Guide](https://github.com/matthewdeaves/willow/blob/main/DeveloperGuide.md) provides a comprehensive overview of the project structure, development processes, and best practices. This guide is essential for both new and experienced contributors to understand the codebase and maintain consistency across the project.

Key areas covered in the guide include:

1. **Getting Started**: Learn about useful shell aliases, key code folders, and the structure of controllers, models, and templates.

2. **Theming with Plugins**: Understand how to customize the CMS appearance using the plugin system.

3. **Feature Development and Database Migrations**: Follow best practices for developing new features, including database schema changes and applying them to production.

4. **Coding Standards**: Utilize PHP CodeSniffer to maintain consistent code style across the project.

5. **Unit Testing**: Learn how to write, run, and interpret unit tests, including generating code coverage reports.

6. **Anthropic API Integration**: Explore the classes used for AI-driven features, including comment analysis, image analysis, and SEO content generation.

7. **Environment Configuration**: Understand how to set up and manage different environments using the `config/.env` file.

8. **The Docker Development Environment**: Understand how the development environment is setup and can be modified.

For a deeper understanding of the underlying CakePHP framework, the [CakePHP Book](https://book.cakephp.org/5/en/index.html) is an invaluable resource.

### Production Environment
There is a separate GitHub Repository for a [production version of Willow CMS for AWS AppRunner](https://github.com/matthewdeaves/willow_cms_production_deployment) with its own guide.

### Thanks To

A huge thank you to the incredible CakePHP framework for making this work possible. CakePHP is an open-source web, rapid development framework that makes building web applications simpler, faster and require less code. CakePHP allows developers to focus on building robust applications without getting bogged down by configuration details. Its conventions over configuration approach, powerful code generation tools, and integrated testing framework are just a few of the features that make CakePHP a standout choice for developers. For more information, visit the [Official CakePHP website](https://cakephp.org) and explore the [CakePHP Cookbook](https://book.cakephp.org) for comprehensive guidance.

Additionally, a huge thank you to Simtheory.ai for providing access to a plethora of frontier models, enabling me to experiment with coding alongside multiple AI agents. This platform has been instrumental in pushing the boundaries of what is possible for just me to build in month to reach v1. For more details, check out [Simtheory.ai](https://simtheory.ai).
