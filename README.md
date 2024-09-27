# Willow CMS

# Easy-to-Use Content Management System Built with CakePHP 5.x

This tool helps you create and manage a blogging website without needing to be a tech expert. Here's what you can do:

## Features

- **Manage Users**
  - Create accounts
  - Update user information
  - Control admin access

- **Handle Images**
  - Upload and organize images
  - Automatic image resizing

- **Organize with Tags**
  - Tag content for easy discovery

- **Create and Edit Articles**
  - Write and publish blog posts
  - Use a simple, intuitive editor

- **Manage Comments**
  - Allow user comments on content
  - Moderate discussions

- **Track Site Activity**
  - Monitor site events
  - Track errors and access attempts
  - View page statistics

- **Enhance Security**
  - IP address blocking for unwanted visitors

- **Support Nested Website Pages**
  - Create hierarchical page structures
  - Organize content in multiple levels
  - Use for site navigation

- **Robust Command Line Tools**
  - Streamline deployment processes
  - Create users and consumers efficiently
  - Offload tasks for improved performance via RabbitMQ
  - Seamlessly integrate with application configuration

This CMS is designed to be user-friendly while offering powerful features for content management and website organization. Its command line toolkit enhances deployability and allows for efficient task management, making it an ideal choice for both small-scale blogs and larger, more complex web applications.

## Installation and setup with Docker

Docker is used to host everything you need for a development environment: Nginx, PHP, MySQL, RabbitMQ and PHPMyAdmin. The only thing you need on your host machine is [Docker](https://www.docker.com) and [Composer](https://getcomposer.org)

```
#Clone the repo
git clone git@github.com:matthewdeaves/willow.git

#Change directory
cd cakephpcms

#Install dependencies with composer
composer install

#start the docker containers
docker compose up

#create the database tables
docker compose exec php bin/cake migrations migrate

#create an admin user
#run docker compose exec php bin/cake create_user --help for options
docker compose exec php bin/cake create_user admin password admin@test.com true

#create the default settings
#run docker compose exec php bin/cake load_default_settings --help for options
docker compose exec php bin/cake load_default_settings

#run the resize image consumer
docker compose exec php bin/cake run_resize_image_consumer

#run the site creation/deletion consumers
docker compose exec php bin/cake start_delete_site_consumer
docker compose exec php bin/cake start_new_site_consumer

#If you are on Ubuntu and like me have to run docker with sudo, set permissions of the webroot folder (very permissive for now). If you are on a Mac skip this.
chmod -R 777 webroot
chmod -R 777 tmp
```

Visit `http://localhost:8080` to use the CMS and login with the default user

Visit `http://localhost:15673` to use the RabbitMQ interface

Visit `http://localhost:8082` to use PHPMyAdmin

## Installation and setup without Docker

If you prefer to use the built in CakePHP webserver and your own locally installed PHP, MySQL and RabbitMQ you can. Just create and configure `config/app_local.php` to match your setup and follow the steps below to craete the database tables, admin user and start the image resize consumer like so:

```
#Clone the repo
git clone git@github.com:matthewdeaves/willow.git

#Change directory
cd cakephpcms/

#Install dependencies with composer
composer install

#create the database tables
bin/cake migrations migrate

#create an admin user
#run bin/cake create_user --help for options
bin/cake create_user admin password admin@test.com true

#create the default settings
#run bin/cake load_default_settings --help for options
bin/cake load_default_settings

#run the resize image consumer
bin/cake run_resize_image_consumer

#run the site creation/deletion consumers
bin/cake start_delete_site_consumer
bin/cake start_new_site_consumer

#start the built in webserver
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
```

## Useful shell aliases

To make it easier to run `bin/cake` commands or get an interactive shell on the PHP container I added the below to my .bashrc file. You can then use the cakeshell on the container with arguments like `cmscake bake template Articles` or `cmscake bake migration_snapshot Initial`. Just type `cmssh` to get an interactive shell on the PHP container.

```
# CMS Cake alias
cmscake() {
    sudo docker compose exec php bin/cake "$@"
}

#CMS Shell alias
alias cmssh='sudo docker compose exec -it php /bin/sh'

mine() {
    local current_user=$(whoami)
    local current_group=$(id -gn)
    sudo chown -R "$current_user:$current_group" .
    echo "Ownership set to $current_user:$current_group for current directory and subdirectories."
}

#Docker Aliases
alias dnames='sudo docker container ls -a --format "{{.Names}}"'
alias dprune='sudo docker system prune -a'
```

Running `bin/cake bake` commands to generate files can give issues if on your host machine you use sudo with docker. The following `mine` alias will set everything to match your user and group for the location in which you call it.

```
mine() {
    local current_user=$(whoami)
    local current_group=$(id -gn)
    sudo chown -R "$current_user:$current_group" .
    echo "Ownership set to $current_user:$current_group for current directory and subdirectories."
}
```

### Documentation updates TODO
1. Detail on RabbitMQ queues and consumers
2. what is /home/matt/cakephpcms/config/log_config.php used for?
