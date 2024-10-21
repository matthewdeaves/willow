#!/bin/sh

# Define the flag file in the project root
FIRST_RUN_FLAG="./config/.first_run_completed"

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

# Its dev so just be fully open with permissions
$(needs_sudo) chmod -R 777 logs/ tmp/ webroot/

# Check if this is the first run
if [ ! -f "$FIRST_RUN_FLAG" ]; then
    echo "First time development container startup detected. Running initial setup..."

    # Composer install dependencies
    $(needs_sudo) docker compose exec willowcms composer install

    # Run migrations
    $(needs_sudo) docker compose exec willowcms bin/cake migrations migrate

    # Create default admin user
    $(needs_sudo) docker compose exec willowcms bin/cake create_user -u admin -p password -e admin@test.com -a 1

    # Import default data
    $(needs_sudo) docker compose exec willowcms bin/cake default_data_import --all

    # Create the flag file in the project root to indicate first run is completed
    touch "$FIRST_RUN_FLAG"

    echo "Initial setup completed."
else
    echo "Subsequent container startup detected. Skipping initial setup."
fi

# Clear cache (this will run every time)
$(needs_sudo) docker compose exec willowcms bin/cake cache clear_all