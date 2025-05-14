#!/bin/bash

# Jenkins container is optional
USE_JENKINS=0
LOAD_I18N=0

# Parse command line arguments
while [[ "$#" -gt 0 ]]; do
    case $1 in
        -j|--jenkins) USE_JENKINS=1 ;;
        --i18n) LOAD_I18N=1 ;;
        *) echo "Unknown parameter: $1"; exit 1 ;;
    esac
    shift
done

# Detect the operating system
OS="$(uname)"

# Function to determine if sudo is needed for non-Docker commands
needs_sudo() {
    if [ "$OS" = "Linux" ]; then
        echo "sudo"
    else
        echo ""
    fi
}

# Create required directories
echo "Creating required directories..."
mkdir -p logs/nginx
chmod 777 logs/nginx # This chmod might still need sudo if run by a non-owner depending on parent dir permissions

# Function to check if Docker containers are running
check_docker_status() {
    if docker compose ps --services --filter "status=running" | grep -q "willowcms"; then
        return 0  # Container is running
    else
        return 1  # Container is not running
    fi
}

# Start Docker containers if they're not running
start_docker_containers() {
    echo "Starting Docker containers..."
    if [ "$USE_JENKINS" -eq 1 ]; then
        echo "Including Jenkins in startup..."
        docker compose up -d willowcms mysql phpmyadmin mailpit redis-commander jenkins
    else
        echo "Starting without Jenkins..."
        docker compose up -d willowcms mysql phpmyadmin mailpit redis-commander
    fi
}

# Function to wait for MySQL
wait_for_mysql() {
    echo "Waiting for MySQL to be ready..."
    docker compose exec willowcms bash -c 'curl -o wait-for-it.sh https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh && chmod +x wait-for-it.sh && ./wait-for-it.sh mysql:3306 -t 60 -- echo "MySQL is ready"'
}

# Main script execution starts here
echo "Checking Docker container status..."

if ! check_docker_status; then
    start_docker_containers
else
    echo "Docker containers are already running."
fi

# Wait for MySQL to be ready
wait_for_mysql

# Composer install dependencies
docker compose exec willowcms composer install --no-interaction

# Check if database has been setup (has a settings table)
docker compose exec willowcms bin/cake check_table_exists settings
tableExists=$?

if [ "$tableExists" -eq 0 ]; then
    echo "Subsequent container startup detected."
    read -p "Do you want to [W]ipe data, re[B]uild, [R]estart the development environment, run [M]igration or [C]ontinue? (w/b/r/c): " choice
    case ${choice:0:1} in
        w|W)
            echo "Wiping Docker containers..."
            docker compose down -v --remove-orphans
            start_docker_containers
            wait_for_mysql
            ;;
        b|B)
            echo "Rebuilding Docker containers..."
            docker compose down --remove-orphans
            docker compose build
            start_docker_containers
            wait_for_mysql
            ;;
        r|R)
            echo "Restarting Docker containers..."
            docker compose down --remove-orphans
            start_docker_containers
            wait_for_mysql
            ;;
        m|M)
            echo "Running migrations..."
            docker compose exec willowcms bin/cake migrations migrate
            ;;
        c|C|*)
            echo "Continuing with normal startup..."
            ;;
    esac
fi

# Check if database has been setup (has a settings table) - database may have been wiped
docker compose exec willowcms bin/cake check_table_exists settings
tableExists=$?

if [ "$tableExists" -eq 1 ]; then
    echo "Running initial setup..."

    # Its dev so just be fully open with permissions
    # This chmod might still need sudo depending on file/directory ownership
    $(needs_sudo) chmod -R 777 logs/ tmp/ webroot/

    # Run migrations
    docker compose exec willowcms bin/cake migrations migrate

    # Create default admin user
    docker compose exec willowcms bin/cake create_user -u admin -p password -e admin@test.com -a 1

    # Import default data
    docker compose exec willowcms bin/cake default_data_import aiprompts
    docker compose exec willowcms bin/cake default_data_import email_templates

    # Load internationalisations if flag is set
    if [ "$LOAD_I18N" -eq 1 ]; then
        echo "Loading internationalisation data..."
        docker compose exec willowcms bin/cake default_data_import internationalisations
    fi

    echo "Initial setup completed."
fi

# Clear cache (this will run every time)
docker compose exec willowcms bin/cake cache clear_all

echo "Development environment setup complete."
