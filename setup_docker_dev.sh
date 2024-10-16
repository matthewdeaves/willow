#!/bin/sh

# Define the flag file in the project root
FIRST_RUN_FLAG="./config/.first_run_completed"

# Check if this is the first run
if [ ! -f "$FIRST_RUN_FLAG" ]; then
    echo "First time development container startup detected. Running initial setup..."

    # Composer install dependencies
    docker compose exec php composer install

    # Run migrations
    docker compose exec php bin/cake migrations migrate

    # Create default admin user
    docker compose exec php bin/cake create_user -u admin -p password -e admin@test.com -a 1

    # Import default data
    docker compose exec php bin/cake default_data_import --all

    # Create the flag file in the project root to indicate first run is completed
    touch "$FIRST_RUN_FLAG"

    echo "Initial setup completed."
else
    echo "Subsequent container startup detected. Skipping initial setup."
fi

# Clear cache (this will run every time)
docker compose exec php bin/cake cache clear_all

chmod 777 -R webroot/ tmp/ logs/
