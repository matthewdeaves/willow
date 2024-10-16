# Willow CMS - Easy-to-Use Content Management System Built with CakePHP 5.x

![Build Status](https://github.com/matthewdeaves/willow/workflows/CI/badge.svg)

## Docker Development Environment
Docker is used to host everything you need for a development environment: Nginx, PHP, MySQL, Redis, PHPMyAdmin, MailHog and Jenkins. The only thing you need on your host machine is [Docker](https://www.docker.com).

### Quick Start - Mac OS X / Ubuntu
Depending on your Ubuntu version you may need to run docker with `sudo`. Follow these steps:

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

#load default data
docker compose exec php bin/cake default_data_import --all

#make sure the cache is cleared
docker compose exec php bin/cake cache clear_all

#If you see any write permission errors when viewing pages in Willow CMS try setting the permissions wide open on these folders (or restart containers `docker compose down` then `docker compose up`).
sudo chmod 777 -R webroot/ tmp/ logs/

```

The development environment will be setup and you can visit [http://localhost:8080](http://localhost:8080) to start using Willow CMS.

Login to Willow CMS at [http://localhost:8080/admin](http://localhost:8080/admin) with `admin@test.com` & `password` to start using the CMS on the development environment.

### Anthropic API Integration
Willow integrate the Anthropic API for some nice features:

* Generation of image alternate text, keywords, nice file names and descriptions
* Automatic moderation of comments
* Generation of SEO related texts based on Article content

To use these feautures go to the settings page [http://localhost:8080/admin/settings](http://localhost:8080/admin/settings) to add your API key and enable the AI features.

### Queues and Consumers
Willow CMS uses queues and consumers to offload haevy duty tasks to background processes. This includes things like image processing/resizing and making calls to the Anthropic API. You can run a worker process like this:

```
docker compose exec php bin/cake queue worker --verbose
```

## Development Guide
If you build on Willow CMS you should update the Unit Tests and be sure to fix anything that PHP Code Sniffer picks up. You can run those tools like so:

```
#cd into the code folder
cd willow/

#run the PHPUnit Tests
docker compose exec php vendor/bin/phpunit

#run PHP CodeSniffer (PHPCS)
docker compose exec php vendor/bin/phpcs --standard=vendor/cakephp/cakephp-codesniffer/CakePHP src/ tests/

#run PHP Code Beautifier and Fixer or PHPCBF)
docker compose exec php vendor/bin/phpcbf

#run code style checks via composer
docker compose exec php composer cs-check

#run code style automatic fixes via composer
docker compose exec php composer cs-fix
```

### Useful Shell Aliases
If you build on Willow CMS you should make life easier with some shell aliases.
```
# Use Cake shell on the container with args
cmscake() {
    sudo docker compose exec php bin/cake "$@"
}

cmsexec() {
    sudo docker compose exec php "$@"
}

#run from within the Willow CMS code directory
alias cmssh='sudo docker compose exec -it php /bin/sh'
alias unittest='cmsexec php vendor/bin/phpunit --coverage-text'
alias cscheck='cmsexec php composer cs-check'
alias csfix='cmsexec php composer cs-fix'
alias sniff='cmsexec php vendor/bin/phpcs --standard=vendor/cakephp/cakephp-codesniffer/CakePHP src/ tests/'
alias snifffix='cmsexec php vendor/bin/phpcbf'
alias stan='cmsexec php vendor/bin/phpstan analyse src/'
alias runworkers='cmsexec bin/cake queue worker --verbose'

# Set permissions
alias setwcmspermissions='cmsexec chmod -R 777 tmp logs webroot'

#quick bashrc edits
alias nbash='nano ~/.bashrc'
alias sbash='source ~/.bashrc'

#Useful to set  everything you you:you if running docker with sudo
mine() {
    local current_user=$(whoami)
    local current_group=$(id -gn)
    sudo chown -R "$current_user:$current_group" .
    echo "Ownership set to $current_user:$current_group for current directory and subdirectories."
}
```

### Docker Containers

See docker-compose.yml and the docker folder for how the development environment containers are configured. 

#### Jenkins 
Jenkins come pre-configured with a job that will checkout the repo and run the tests and code checks on the main branch. The docker/jenkins folder has all the configuration for this. I'll be adding more pre-configured jobs as I move towards a more standard git-flow way of working. Use [http://localhost:8081](http://localhost:8081) to use Jenkins.

#### MySQL Server
User: root
Password: password

#### redis and redis Commander
Use [http://localhost:8084](http://localhost:8084) for redis Commander
User: root
Password: password

#### phpMyAdmin
Use [http://localhost:8082](http://localhost:8082) for phpMyAdmin, it is pre-configured to access the MySQL Server container.

#### MailHog
Use [http://localhost:8025](http://localhost:8025) for MailHog, it is pre-configured to receive all email sent by Willow CMS.