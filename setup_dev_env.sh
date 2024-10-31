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
    sleep 10  # Give containers some time to fully start
}

# Main script execution starts here
echo "Checking Docker container status..."

if ! check_docker_status; then
    start_docker_containers
else
    echo "Docker containers are already running."
fi

# Check if database has been setup (has a settings table)
$(needs_sudo) docker compose exec willowcms bin/cake check_table_exists settings
tableExists=$?

if [ "$tableExists" -eq 0 ]; then
    echo "Subsequent container startup detected."
    read -p "Do you want to [W]ipe data, re[B]uild or [R]estart the development environment? (w/b/r): " choice
    case ${choice:0:1} in
        w|W)
            echo "Wiping Docker containers..."
            $(needs_sudo) docker compose down -v
            start_docker_containers
            rm -rf webroot/files/*
            ;;
        b|B)
            echo "Rebuilding Docker containers..."
            $(needs_sudo) docker compose down
            $(needs_sudo) docker compose build
            start_docker_containers 
            ;;
        r|R)
            echo "Restarting Docker containers..."
            $(needs_sudo) docker compose down
            start_docker_containers
            ;;
        *)
            echo "Invalid option. Continuing with normal startup..."
            ;;
    esac
fi

# Check if database has been setup (has a settings table) - database may have been wiped
$(needs_sudo) docker compose exec willowcms bin/cake check_table_exists settings
tableExists=$?

if [ "$tableExists" -eq 1 ]; then
    echo "Running initial setup..."

    # Composer install dependencies
    $(needs_sudo) docker compose exec willowcms composer install --no-interaction

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

# Its dev so just be fully open with permissions
$(needs_sudo) chmod -R 777 logs/ tmp/ webroot/

echo "Development environment setup complete."