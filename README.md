# Willow CMS - Easy-to-Use Content Management System Built with CakePHP 5.x

![Build Status](https://github.com/matthewdeaves/willow/workflows/CI/badge.svg)

## Table of Contents
1. [Docker Development Environment](#docker-development-environment)
   - [Quick Start - Mac OS X / Ubuntu](#quick-start---mac-os-x--ubuntu)
2. [Anthropic API Integration](#anthropic-api-integration)
3. [Queues and Consumers](#queues-and-consumers)
4. [Development Guide](#development-guide)
   - [Useful Shell Aliases](#useful-shell-aliases)
   - [Detailed Development Guide](https://github.com/matthewdeaves/willow/blob/main/DeveloperGuide.md)
5. [Docker Containers](#docker-containers)
   - [Jenkins](#jenkins)
   - [MySQL Server](#mysql-server)
   - [Redis and Redis Commander](#redis-and-redis-commander)
   - [phpMyAdmin](#phpmyadmin)
   - [MailHog](#mailhog)
6. [Production Environment](#production-environment)
7. [GitHub Actions](#github-actions)

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

This is all handled by `./setup_dev_env.sh` which is installing dependencies via [Composer](https://getcomposer.org/), running the database migration, creating a user and importing default data.

[setup_dev_env.sh](https://github.com/matthewdeaves/willow/blob/main/setup_dev_env.sh)

### Anthropic API Integration
Willow integrates the [Anthropic API](https://console.anthropic.com/dashboard) for some nice features:

* Generation of image alternate text, keywords, nice file names and descriptions
* Automatic moderation of comments
* Generation of SEO related texts based on Article content
* A lot more to come...

To use these feautures go to the settings page [http://localhost:8080/admin/settings](http://localhost:8080/admin/settings) to add your API key and enable the AI features.

### Queues and Consumers
Willow CMS uses queues and consumers to offload heavy duty tasks to background processes. This includes things like image processing/resizing and making calls to the Anthropic API. On the development environment, queue workers are not started automatically. This means if you upload an image or perform a task that offloads a message to the queue for a worker to pick, you will need to start a queue worker. You can start a queue worker process like this:

- **Alias Command**: 
```
cake_queue_worker
```
- **Raw Command**: 
```
docker compose exec php bin/cake queue worker --verbose
```
Leave the queue worker running in a terminal to see useful output as it picks up and runs [jobs](https://github.com/matthewdeaves/willow/tree/main/src/Job). Remember to save your Anthropic API key in the settings page.

## Development Guide
If you build on Willow CMS you should update the Unit Tests and be sure to fix anything that PHP Code Sniffer picks up. You can run those tools like so:

```
#cd into the code folder
cd willow/

#run the PHPUnit Tests
docker compose exec php vendor/bin/phpunit --coverage-text

#run PHP CodeSniffer (PHPCS)
docker compose exec php vendor/bin/phpcs --standard=vendor/cakephp/cakephp-codesniffer/CakePHP src/ tests/

#run PHP Code Beautifier and Fixer or PHPCBF
docker compose exec php vendor/bin/phpcbf

#run code style checks via composer
docker compose exec php composer cs-check

#run code style automatic fixes via composer
docker compose exec php composer cs-fix
```
You should checkout the detailed [Developer Guide](https://github.com/matthewdeaves/willow/blob/main/DeveloperGuide.md) for an overview of some key source code.

### Useful Shell Aliases
It's a lot to type out the commands above. If you build on Willow CMS you should make life easier with some shell aliases. You can auto set them (zshrc/bashrc detection included) by running `./setup_dev_aliases.sh` from the project root. [setup_dev_aliases.sh](https://raw.githubusercontent.com/matthewdeaves/willow/refs/heads/main/setup_dev_aliases.sh) will add a statement to simply load the aliases from [dev_aliases.txt](https://raw.githubusercontent.com/matthewdeaves/willow/refs/heads/main/dev_aliases.txt) when you start a new shell. Or you can manually add the aliases:

1. Copy the contents of `dev_aliases.sh`
2. Open your `~/.bashrc` or `.zshrc` file
3. Paste the contents at the end of the file
4. Save and close the file
5. Run `source ~/.bashrc` or `source ~/.zshrc` or restart your terminal

### Docker Containers
See [docker-compose.yml](https://raw.githubusercontent.com/matthewdeaves/willow/refs/heads/main/docker-compose.yml) and the [docker](https://github.com/matthewdeaves/willow/tree/main/docker) folder for how the docker images and containers are built.

#### Willow CMS
This is a combined nginx and PHP-FPM container using Alpine Linux. The [docker/willowcms](https://github.com/matthewdeaves/willow/tree/main/docker/willowcms) folder has all the configuration that make it a little more open and therefore suitable for a development environment. In all other respects it is exactly the same as the production environment. Use [http://localhost:8080](http://localhost:8080) to use the development environment.

#### Jenkins 
Jenkins is pre-configured with a job that will checkout the repo and run the tests and code checks on the main branch. The [docker/jenkins](https://github.com/matthewdeaves/willow/tree/main/docker/jenkins) folder has all the configuration for this and more jobs will be added in future (front end tests for example). Use [http://localhost:8081](http://localhost:8081) to use Jenkins.

#### MySQL Server
Mostly configured via [docker-compose.yml](https://github.com/matthewdeaves/willow/blob/2a3dc5c9a3629b99797c586c938ed94a756b15fc/docker-compose.yml#L3) and loads an [init.sql](https://github.com/matthewdeaves/willow/blob/main/docker/mysql/init.sql) from [docker/mysql](https://github.com/matthewdeaves/willow/tree/main/docker/mysql). Default login is root:password

#### redis and redis Commander
Configured via [docker-compose.yml](https://github.com/matthewdeaves/willow/blob/2a3dc5c9a3629b99797c586c938ed94a756b15fc/docker-compose.yml#L69). Use [http://localhost:8084](http://localhost:8084) for redis Commander interface. Default login is root:password

#### phpMyAdmin
Configured via [docker-compose.yml](https://github.com/matthewdeaves/willow/blob/2a3dc5c9a3629b99797c586c938ed94a756b15fc/docker-compose.yml#L37). Use [http://localhost:8082](http://localhost:8082) to access phpMyAdmin. It is pre-configured to access the MySQL Server container.

#### MailHog
Configured via [docker-compose.yml](https://github.com/matthewdeaves/willow/blob/2a3dc5c9a3629b99797c586c938ed94a756b15fc/docker-compose.yml#L63). Use [http://localhost:8025](http://localhost:8025) for MailHog. It will receive all email sent by Willow CMS on the development environment and give you a nice interface to view it.

### Production Environment
There is a separate GitHub Repository for a [production version of Willow CMS for AWS AppRunner](https://github.com/matthewdeaves/willow_cms_production_deployment) with its own guide.

### GitHub Actions
Willow CMS is setup to execute tests with GitHub Actions with [ci.yml](https://github.com/matthewdeaves/willow/blob/main/.github/workflows/ci.yml). Willow CMS is tested against PHP 8.1, 8.2 and 8.3.

### Thanks To

A huge thank you to the incredible CakePHP framework for making this work possible. CakePHP is an open-source web, rapid development framework that makes building web applications simpler, faster and require less code. CakePHP allows developers to focus on building robust applications without getting bogged down by configuration details. Its conventions over configuration approach, powerful code generation tools, and integrated testing framework are just a few of the features that make CakePHP a standout choice for developers. For more information, visit the [Official CakePHP website](https://cakephp.org) and explore the [CakePHP Cookbook](https://book.cakephp.org) for comprehensive guidance.

Additionally, a huge thank you to Simtheory.ai for providing access to a plethora of frontier models, enabling me to experiment with coding alongside multiple AI agents. This platform has been instrumental in pushing the boundaries of what is possible for just me to build in month to reach v1. For more details, check out [Simtheory.ai](https://simtheory.ai).