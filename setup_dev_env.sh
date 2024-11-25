#!/bin/bash

# Detect the operating system
OS="$(uname)"

# Function to determine if sudo is needed
needs_sudo() {
    if [ "$OS" = "Linux" ]; then
        echo "sudo"
    else
        echo ""
    fi
}

# Function to check if Docker containers are running
check_docker_status() {
    if $(needs_sudo) docker compose ps --services --filter "status=running" | grep -q "willowcms"; then
        return 0  # Container is running
    else
        return 1  # Container is not running
    fi
}

# Start Docker containers if they're not running
start_docker_containers() {
    echo "Starting Docker containers..."
    $(needs_sudo) docker compose up -d
}

# Function to wait for MySQL
wait_for_mysql() {
    echo "Waiting for MySQL to be ready..."
    $(needs_sudo) docker compose exec willowcms bash -c 'curl -o wait-for-it.sh https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh && chmod +x wait-for-it.sh && ./wait-for-it.sh mysql:3306 -t 60 -- echo "MySQL is ready"'
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
$(needs_sudo) docker compose exec willowcms composer install --no-interaction

# Check if database has been setup (has a settings table)
$(needs_sudo) docker compose exec willowcms bin/cake check_table_exists settings
tableExists=$?

if [ "$tableExists" -eq 0 ]; then
    echo "Subsequent container startup detected."
    read -p "Do you want to [W]ipe data, re[B]uild, [R]estart the development environment or [C]ontinue? (w/b/r/c): " choice
    case ${choice:0:1} in
        w|W)
            echo "Wiping Docker containers..."
            $(needs_sudo) docker compose down -v
            start_docker_containers
            wait_for_mysql
            ;;
        b|B)
            echo "Rebuilding Docker containers..."
            $(needs_sudo) docker compose down
            $(needs_sudo) docker compose build
            start_docker_containers
            wait_for_mysql
            ;;
        r|R)
            echo "Restarting Docker containers..."
            $(needs_sudo) docker compose down
            start_docker_containers
            wait_for_mysql
            ;;
        c|C|*)
            echo "Continuing with normal startup..."
            ;;
    esac
fi

# Check if database has been setup (has a settings table) - database may have been wiped
$(needs_sudo) docker compose exec willowcms bin/cake check_table_exists settings
tableExists=$?

if [ "$tableExists" -eq 1 ]; then
    echo "Running initial setup..."

    # Its dev so just be fully open with permissions
    $(needs_sudo) chmod -R 777 logs/ tmp/ webroot/

    # Run migrations
    $(needs_sudo) docker compose exec willowcms bin/cake migrations migrate

    # Create default admin user
    $(needs_sudo) docker compose exec willowcms bin/cake create_user -u admin -p password -e admin@test.com -a 1

    # Import default data
    $(needs_sudo) docker compose exec willowcms bin/cake default_data_import --all

    echo "Initial setup completed."
fi

# Clear cache (this will run every time)
$(needs_sudo) docker compose exec willowcms bin/cake cache clear_all

echo "Development environment setup complete."