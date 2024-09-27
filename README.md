# Willow CMS - Easy-to-Use Content Management System Built with CakePHP 5.x

![Build Status](https://github.com/matthewdeaves/willow/workflows/CI/badge.svg)

This tool helps you manage a website without needing to be a tech expert. Here's what you can do:

## Features

- **Manage Users**
  - Create accounts
  - Update user information
  - Control admin access

- **User Registration and Activation**
  - Users can create accounts through a registration process
  - Activation emails are sent to verify user email addresses
  - Accounts are enabled only after email verification

- **Handle Images**
  - Upload and organize images
  - Automatic image resizing using a seperate queue consumer process

- **Organize with Tags**
  - Tag content for easy discovery

- **Create and Edit Pages and Articles**
  - Write and publish blog posts
  - Maintain a tree structure of site pages
  - Use a simple, intuitive editor

- **Manage Comments**
  - Allow comments on content
  - Moderate discussions

- **Track Site Activity**
  - Monitor site events
  - Track errors and access attempts
  - View page statistics

- **Enhance Security**
  - IP address blocking for unwanted visitors

- **Developer Friendly**
  - Excellent development tooling out of the box (only Docker required)
  - Useful CakePHP Command shells to create users, load default site data
  - Fully documented code

This CMS is designed to be user-friendly while offering powerful features for content management and website organization. Its command line toolkit enhances deployability and allows for efficient task management, making it an ideal choice for both small-scale blogs and larger, more complex web applications.

## Installation and setup with Docker

Docker is used to host everything you need for a development environment: Nginx, PHP, MySQL, RabbitMQ, PHPMyAdmin, MailHog and Jenkins. The only thing you need on your host machine is [Docker](https://www.docker.com) since you can run all required commands on the PHP container via `docker compose exec`. See Useful Shell Aliases section below to make this even easier.

```
#Clone the repo
git clone git@github.com:matthewdeaves/willow.git

#Change directory
cd willow/

#start the docker containers (-d is detached so they run in background)
docker compose up -d

#Install PHP dependencies with composer
docker compose exec php composer install

#create the database tables
docker compose exec php bin/cake migrations migrate

#create an admin user (--help for options/usage)
docker compose exec php bin/cake create_user -u admin -p password -e admin@test.com -a 1

#load default data (--help for options/usage)
docker compose exec php bin/cake load_default_data email_templates

#make sure the cache is cleared
docker compose exec php bin/cake cache clear_all

#run the PHPUnit Tests
docker compose exec php vendor/bin/phpunit

#run codesniffer
docker compose exec php vendor/bin/phpcs --standard=vendor/cakephp/cakephp-codesniffer/CakePHP src/ tests/

#run consumers for ProcessImage and SendEmail jobs (use a new terminal)
docker compose exec php bin/cake queue worker --verbose

#If you are on Ubuntu and like me have to run docker with sudo, set permissions of the webroot folder (very permissive for now). If you are on a Mac skip this.
sudo chmod 777 -R webroot/ tmp/ logs/
```

Visit `http://localhost:8080` to use the CMS and login with the default user

Visit `http://localhost:15673` to use the RabbitMQ interface

Visit `http://localhost:8082` to use PHPMyAdmin

Visit `http://localhost:8081` to use Jenkins

Visit `http://localhost:8025` to use MailHog

## Installation and setup without Docker

If you prefer to use the built in CakePHP webserver and your own locally installed PHP, MySQL and RabbitMQ you can. Just create and configure `config/app_local.php` to match your setup and follow the steps below to craete the database tables, admin user and start the image resize consumer like so:

```
#Clone the repo
git clone git@github.com:matthewdeaves/willow.git

#Change directory
cd willow/

#Install dependencies with composer
composer install

#create the database tables
bin/cake migrations migrate

#create an admin user
#run bin/cake create_user --help for options
bin/cake create_user -u admin -p password -e admin@test.com -a 1

#load default data
bin/cake load_default_data email_templates

#make sure the cache is cleared
bin/cake cache clear_all

#run the PHPUnit Tests
vendor/bin/phpunit

#run codesniffer
vendor/bin/phpcs --standard=vendor/cakephp/cakephp-codesniffer/CakePHP src/ tests/

#run consumers for ProcessImage and SendEmail jobs (use a new terminal)
bin/cake queue worker --verbose

#start the built in webserver (use a new terminal)
bin/cake server start
```

Visit `http://localhost:8765/` to use the CMS and login with the default user

## Dependencies

1. Image Magick is used to create resized versions of image uploads
2. RabbitMQ is used to offload image resizing functionality to a seperate consumer process
3. MySQL is used for the database storage

## Other bits

If you want to generate the images for Users and Images manually do `bin/cake resize_images` to force regenerate all of them or `bin/cake resize_images -s` to skip existing images. You can use these commands via `docker compose exec` to run them on the container.

Docker configures MySQL, RabbitMQ and PHPMyAdmin to run on ports that will not (hopefully) conflict with any local installation of those services. If you wish to access these services from your host machine (such as using a database tool) then use the folowing port mappings (host -> container):

 1. MySQl : 3310 -> 3306
 2. RabbitMQ : 5673 -> 5672
 3. RabbitMQ Web Admin : 15673:15672
 4. PHPMyAdmin : 8082 : 80
 5. Nginx : 8080 : 80

## Useful docker commands

Sometimes you will want to make sure everything is destroyed.

```
docker compose down --rmi all
docker compose down -v
docker system prune -a

#view logs for a specific container
docker compose logs mysql
docker compose logs php

#stop a specific container
docker compose stop mysql

#remove a container
docker compose rm -f mysql

#remove a specific volume
docker volume rm willow_mysql_data

#rebuild a container
docker compose up -d --no-deps --build mysql

#rebuild the php image with plain output and no cache
sudo docker compose build --no-cache --progress=plain php

```

## Useful shell aliases

To make it easier to run `bin/cake` commands or get an interactive shell on the PHP container I added the below to my .bashrc file. You can then use the cakeshell on the container with arguments like `cmscake bake template Articles` or `cmscake bake migration_snapshot Initial`. Just type `cmssh` to get an interactive shell on the PHP container.

```
# Use Cake shell on the container with args
cmscake() {
    sudo docker compose exec php bin/cake "$@"
}

cmsexec() {
    sudo docker compose exec php "$@"
}

alias cmssh='sudo docker compose exec -it php /bin/sh'
alias unittest='cmsexec php vendor/bin/phpunit --coverage-text'
alias cscheck='cmsexec php composer cs-check'
alias csfix='cmsexec php composer cs-fix'
alias sniff='cmsexec php vendor/bin/phpcs --standard=vendor/cakephp/cakephp-codesniffer/CakePHP src/ tests/'
alias snifffix='cmsexec php vendor/bin/phpcbf'
alias stan='cmsexec php vendor/bin/phpstan analyse src/'

# Set permissions
alias cperm='cmsexec chmod -R 777 tmp logs webroot'

#quick bashrc edits
alias nbash='nano ~/.bashrc'
alias sbash='source ~/.bashrc'

#Docker Aliases
alias dnames='sudo docker container ls -a --format "{{.Names}}"'
alias dprune='sudo docker system prune -a'
alias rebuild_jenkins='sudo docker compose stop jenkins && sudo docker compose rm -f jenkins && sudo docker volume rm -f willow_jenkins_home && sudo docker compose build jenkins --no-cache && sudo docker compose up -d jenkins'

#Useful to set  everything you you:you if running docker with sudo
mine() {
    local current_user=$(whoami)
    local current_group=$(id -gn)
    sudo chown -R "$current_user:$current_group" .
    echo "Ownership set to $current_user:$current_group for current directory and subdirectories."
}

```

### Code Checks

Run the following commands for code check
```
#run a check to review any errors/warnings that should be fixed
composer cs-check

#auto-fix anything that can be auto-fixed
composer cs-fix
```
### Jenkins Jobs
Jenkins come pre-configured with a job that will checkout the repo and run the tests and code checks on the main branch. The docker/jenkins folder has all the configuration for this.

### Documentation updates TODO
1. Detail on RabbitMQ queues and consumers
2. what is /home/matt/cakephpcms/config/log_config.php used for?

### Useful composer stuff
```
sudo docker compose exec php rm composer.lock
sudo docker compose exec php composer install
```

## Contribution Guidelines

I welcome contributions to this open-source project! If you are interested in contributing, please follow the guidelines below:

### How to Contribute

1. **Fork the Repository**: Start by forking the repository on GitHub. This will create a copy of the project under your own GitHub account.

2. **Clone Your Fork**: Clone your forked repository to your local machine to start making changes.

   ```bash
   git clone https://github.com/YOUR-USERNAME/willow.git
   ```

3. **Create a Branch**: Create a new branch for your changes. Use a descriptive name for your branch, such as `feature-new-feature` or `bugfix-issue-number`.

   ```bash
   git checkout -b feature-new-feature
   ```

4. **Make Changes**: Implement your changes, following the project's coding standards and guidelines.

5. **Commit Your Changes**: Commit your changes with clear and descriptive commit messages.

   ```bash
   git commit -m "Add new feature to improve functionality"
   ```

6. **Push to GitHub**: Push your changes to your forked repository on GitHub.

   ```bash
   git push origin feature-new-feature
   ```

7. **Submit a Pull Request**: Go to the original repository on GitHub and submit a pull request. Provide a clear description of your changes and why they should be merged.

### Contact

If you have any questions or need further assistance, feel free to contact me through GitHub. You can reach out by opening an issue or sending a message through my GitHub profile.

### License

This project is licensed under the [Creative Commons Attribution License](https://creativecommons.org/licenses/by/4.0/). You are free to use, modify, and distribute this project for any purpose, provided that you give appropriate credit to the original author.

Thank you for your interest in contributing to this project!