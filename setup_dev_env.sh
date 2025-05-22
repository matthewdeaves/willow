#!/bin/bash

# SCRIPT BEHAVIOR
# Exit immediately if a command exits with a non-zero status.
# Treat unset variables as an error when substituting.
# Pipelines return the exit status of the last command to exit with a non-zero status,
# or zero if no command exited with a non-zero status.
set -e -u -o pipefail

# --- Configuration ---
# Jenkins container is optional
USE_JENKINS=0
# Internationalisation data loading is optional
LOAD_I18N=0

# Service name for the main application container
MAIN_APP_SERVICE="willowcms"
# Path to the wait-for-it.sh script (used inside the main app container)
WAIT_FOR_IT_SCRIPT_URL="https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh"
WAIT_FOR_IT_FILENAME="wait-for-it.sh"

# --- Argument Parsing (Using POSIX getopts) ---
# Note: This changes how flags are passed.
# Previous: -j or --jenkins, --i18n
# New: -j (for jenkins), -i (for i18n)
PROGNAME="${0##*/}"
usage() {
    echo "Usage: $PROGNAME [-j] [-i]"
    echo "  -j: Include Jenkins service"
    echo "  -i: Load internationalisation data"
    exit 1
}

while getopts "ji" opt; do
  case "$opt" in
    j) USE_JENKINS=1 ;;
    i) LOAD_I18N=1 ;;
    *) usage ;;
  esac
done
shift $((OPTIND - 1)) # Discard the options and their arguments.

if [ "$#" -gt 0 ]; then
    echo "Error: Unknown non-option arguments: $*" >&2
    usage
fi

# --- OS Detection ---
# Not strictly needed anymore since needs_sudo is removed, but kept for context.
# OS_TYPE="$(uname -s)"

# --- Helper Functions ---

# Function to check if the main Docker container is running
check_docker_status() {
    # Check if the main app service is running with an exact name match
    if docker compose ps --services --filter "status=running" | grep -q "^${MAIN_APP_SERVICE}$"; then
        return 0  # Container is running
    else
        return 1  # Container is not running
    fi
}

# Function to start Docker containers
start_docker_containers() {
    echo "Starting Docker containers..."
    local services="${MAIN_APP_SERVICE} mysql phpmyadmin mailpit redis-commander"
    if [ "$USE_JENKINS" -eq 1 ]; then
        echo "Including Jenkins in startup..."
        services="$services jenkins"
    else
        echo "Starting without Jenkins..."
    fi
    # SC2086: Double quote to prevent globbing and word splitting (services is intentionally unquoted here for splitting)
    # shellcheck disable=SC2086
    docker compose up -d $services
}

# Function to wait for MySQL to be ready
wait_for_mysql() {
    echo "Waiting for MySQL to be ready..."
    # Downloads wait-for-it.sh inside the container if it doesn't exist.
    # -f: fail silently on server errors. -s: silent mode. -S: show error on stderr. -L: follow redirects. -o: output to file.
    local wait_command_script
    wait_command_script=$(cat <<EOF
if [ ! -f "${WAIT_FOR_IT_FILENAME}" ]; then
    echo "Downloading ${WAIT_FOR_IT_FILENAME}..."
    if curl -fsSL -o "${WAIT_FOR_IT_FILENAME}" "${WAIT_FOR_IT_SCRIPT_URL}"; then
        chmod +x "${WAIT_FOR_IT_FILENAME}"
    else
        echo "Error: Failed to download ${WAIT_FOR_IT_FILENAME}. Cannot proceed with MySQL wait." >&2
        exit 1
    fi
elif [ ! -x "${WAIT_FOR_IT_FILENAME}" ]; then
    chmod +x "${WAIT_FOR_IT_FILENAME}"
fi
./"${WAIT_FOR_IT_FILENAME}" mysql:3306 -t 60 -- echo "MySQL is ready"
EOF
)
    docker compose exec "$MAIN_APP_SERVICE" bash -c "$wait_command_script"
}

# Function to start/restart Docker containers and wait for MySQL
start_and_wait_services() {
    start_docker_containers
    wait_for_mysql
}

# --- Main Script Execution ---

echo "Creating required directories..."
# The script creates logs/nginx. If logs/ doesn't allow user write, this might fail or need sudo.
# If logs/nginx is created by this user, the chmod below shouldn't need sudo.
# However, if logs/nginx pre-exists with other ownership, sudo would be needed for chmod.
mkdir -p logs/nginx
chmod 777 logs/nginx # Removed needs_sudo function call

echo "Checking Docker container status..."
if ! check_docker_status; then
    start_and_wait_services
else
    echo "Docker containers are already running."
    # Even if containers are running, MySQL might not be ready (e.g., after a host reboot)
    wait_for_mysql
fi

echo "Installing/updating Composer dependencies..."
docker compose exec "$MAIN_APP_SERVICE" composer install --no-interaction --prefer-dist --optimize-autoloader

echo "Checking if database has been set up (looking for 'settings' table)..."
# docker compose exec exits with 0 if command succeeds, 1 if fails.
# We assume bin/cake check_table_exists settings exits 0 if table exists, non-zero otherwise.
if docker compose exec "$MAIN_APP_SERVICE" bin/cake check_table_exists settings; then
    TABLE_EXISTS_INITIAL=0 # True, table exists
else
    TABLE_EXISTS_INITIAL=1 # False, table does not exist / command failed
fi

if [ "$TABLE_EXISTS_INITIAL" -eq 0 ]; then
    echo "Subsequent container startup detected (database appears to be initialized)."
    # Recommended to use lowercase for read variable
    read -r -p "Do you want to [W]ipe data, re[B]uild, [R]estart, run [M]igrations or [C]ontinue? (w/b/r/m/c): " user_choice
    case "${user_choice:0:1}" in
        w|W)
            echo "Wiping Docker containers and volumes..."
            docker compose down -v --remove-orphans
            start_and_wait_services
            ;;
        b|B)
            echo "Rebuilding Docker containers..."
            docker compose down --remove-orphans
            docker compose rm -f # Ensure containers are removed before build
            docker compose build --no-cache # Consider --no-cache for a true rebuild
            start_and_wait_services
            ;;
        r|R)
            echo "Restarting Docker containers..."
            docker compose down --remove-orphans
            start_and_wait_services
            ;;
        m|M)
            echo "Running migrations..."
            docker compose exec "$MAIN_APP_SERVICE" bin/cake migrations migrate
            ;;
        c|C|*)
            echo "Continuing with normal startup..."
            ;;
    esac
fi

# Re-check if database has been set up, as it might have been wiped.
echo "Re-checking if database has been set up..."
if docker compose exec "$MAIN_APP_SERVICE" bin/cake check_table_exists settings; then
    TABLE_EXISTS_FINAL=0
else
    TABLE_EXISTS_FINAL=1
fi

if [ "$TABLE_EXISTS_FINAL" -ne 0 ]; then # If table still does not exist (or command failed)
    echo "Running initial application setup..."

    echo "Setting permissions for logs, tmp, webroot (dev environment)..."
    # These directories are expected to be at the project root.
    # If they are not owned by the user running script, sudo will be invoked on Linux.
    # Ensure these directories exist before running this script or handle their creation.
    # For `logs/`, it's partially handled by `mkdir -p logs/nginx` earlier.
    # Consider creating tmp/ and webroot/ explicitly if they might not exist.
    for dir in logs tmp webroot; do
        if [ ! -d "$dir" ]; then
            echo "Warning: Directory '$dir' does not exist. Skipping chmod for it."
        else
            chmod -R 777 "$dir/" # Removed needs_sudo function call
        fi
    done


    echo "Running database migrations..."
    docker compose exec "$MAIN_APP_SERVICE" bin/cake migrations migrate

    echo "Creating default admin user (admin@test.com / password)..."
    docker compose exec "$MAIN_APP_SERVICE" bin/cake create_user -u admin -p password -e admin@test.com -a 1

    echo "Importing default data (aiprompts, email_templates)..."
    
    docker compose exec "$MAIN_APP_SERVICE" bin/cake default_data_import aiprompts
    docker compose exec "$MAIN_APP_SERVICE" bin/cake default_data_import email_templates

    if [ "$LOAD_I18N" -eq 1 ]; then
        echo "Loading internationalisation data..."
        docker compose exec "$MAIN_APP_SERVICE" bin/cake default_data_import internationalisations
    fi

    echo "Initial setup completed."
fi

echo "Clearing application cache..."
docker compose exec "$MAIN_APP_SERVICE" bin/cake cache clear_all

echo "Development environment setup complete."