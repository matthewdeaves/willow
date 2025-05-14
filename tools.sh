#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
# Treat unset variables as an error when substituting.
# The return value of a pipeline is the status of the last command to exit with a non-zero status.
set -euo pipefail

# --- Configuration Constants ---
readonly APP_SERVICE_NAME="willowcms"
readonly DB_SERVICE_NAME="mysql"
readonly DB_USER_DEFAULT="root"
readonly DB_PASS_DEFAULT="password" # Be cautious with passwords directly in scripts for production
readonly DB_NAME_DEFAULT="cms"
readonly BACKUP_BASE_DIR="${HOME}/willowcms/backups" # Ensure this script is run by a user with write access here

# --- Functions ---

# Function to clear the screen and show the header
show_header() {
    clear
    echo "==================================="
    echo " WillowCMS Command Runner"
    echo "==================================="
    echo
}

# Function to display the menu
show_menu() {
    echo "Available Commands:"
    echo
    echo "Data Management:"
    echo "  1) Import Default Data (all)"
    echo "  2) Export Default Data (all)"
    echo "  3) Dump MySQL Database ('${DB_NAME_DEFAULT}')"
    echo "  4) Load Database from Backup ('${DB_NAME_DEFAULT}')"
    echo
    echo "Internationalization (i18n):"
    echo "  5) Extract i18n Messages"
    echo "  6) Load Default i18n Data"
    echo "  7) Translate i18n (via API if configured)"
    echo "  8) Generate PO Files from POT"
    echo
    echo "System & Debugging:"
    echo "  9) Clear Cache (WillowCMS)"
    echo "  10) Interactive shell on ${APP_SERVICE_NAME} container"
    echo "  0) Exit"
    echo
}

# Function to pause and wait for user input
pause() {
    echo
    read -r -p "Press [Enter] key to continue..."
}

# Function to execute commands
execute_command() {
    local choice="$1"

    case "$choice" in
        1)
            echo "Running Default Data Import for all modules..."
            docker compose exec "${APP_SERVICE_NAME}" bin/cake default_data_import
            ;;
        2)
            echo "Running Default Data Export for all modules..."
            docker compose exec "${APP_SERVICE_NAME}" bin/cake default_data_export
            ;;
        3)
            echo "Dumping MySQL Database '${DB_NAME_DEFAULT}'..."
            local timestamp
            timestamp=$(date '+%Y%m%d_%H%M%S')
            local backup_file="${BACKUP_BASE_DIR}/${DB_NAME_DEFAULT}_backup_${timestamp}.sql"

            mkdir -p "${BACKUP_BASE_DIR}"
            echo "Backup will be saved to: ${backup_file}"

            # Using sh -c to correctly handle the password argument if it contains special characters for the outer shell.
            # mysqldump arguments: -u<user> -p<password> <database_name>
            if docker compose exec "${DB_SERVICE_NAME}" \
                sh -c "exec mysqldump -u'${DB_USER_DEFAULT}' -p'${DB_PASS_DEFAULT}' '${DB_NAME_DEFAULT}'" > "${backup_file}"; then
                echo "Database backup completed successfully!"
            else
                echo "Error: Database backup failed!"
                # Consider removing the potentially incomplete backup file: rm -f "${backup_file}"
                return 1 # Indicate failure
            fi
            ;;
        4)
            echo "Loading Database for '${DB_NAME_DEFAULT}' from Backup..."
            
            if [ ! -d "${BACKUP_BASE_DIR}" ] || [ -z "$(ls -A "${BACKUP_BASE_DIR}"/*.sql 2>/dev/null)" ]; then
                echo "No backup files found in ${BACKUP_BASE_DIR}"
                return 1
            fi
            
            echo "Available backups in ${BACKUP_BASE_DIR}:"
            local i=1
            local -a backup_files # Declare an array for bash
            # Store files in an array to handle spaces in filenames robustly, though .sql usually doesn't have them
            while IFS= read -r -d $'\0' file; do
                backup_files[i]="$file"
                echo "  $i) $(basename "$file")"
                i=$((i + 1))
            done < <(find "${BACKUP_BASE_DIR}" -maxdepth 1 -name '*.sql' -print0)

            if [ ${#backup_files[@]} -eq 0 ]; then
                echo "No .sql backup files found."
                return 1
            fi
            echo

            local selection
            read -r -p "Enter the number of the backup to restore (or 0 to cancel): " selection
            
            if ! [[ "$selection" =~ ^[0-9]+$ ]] || [ "$selection" -lt 0 ] || [ "$selection" -ge "$i" ]; then
                echo "Invalid selection."
                return 1
            fi
            
            if [ "$selection" -eq 0 ]; then
                echo "Operation cancelled."
                return 0
            fi
            
            local chosen_backup_file="${backup_files[selection]}"
            
            echo "Selected backup: $(basename "${chosen_backup_file}")"
            read -r -p "This will DROP the existing database '${DB_NAME_DEFAULT}' and restore from this backup. Are you sure? (y/N): " confirm
            
            if [[ "${confirm}" =~ ^[yY]$ ]]; then
                echo "Dropping and recreating database '${DB_NAME_DEFAULT}'..."
                docker compose exec "${DB_SERVICE_NAME}" \
                    sh -c "mysql -u'${DB_USER_DEFAULT}' -p'${DB_PASS_DEFAULT}' -e 'DROP DATABASE IF EXISTS \`${DB_NAME_DEFAULT}\`; CREATE DATABASE \`${DB_NAME_DEFAULT}\`;'"
                
                echo "Restoring backup from ${chosen_backup_file}..."
                if docker compose exec -T "${DB_SERVICE_NAME}" \
                    sh -c "exec mysql -u'${DB_USER_DEFAULT}' -p'${DB_PASS_DEFAULT}' '${DB_NAME_DEFAULT}'" < "${chosen_backup_file}"; then
                    echo "Database restored successfully!"
                    
                    echo "Clearing WillowCMS cache..."
                    docker compose exec "${APP_SERVICE_NAME}" bin/cake cache clear_all
                else
                    echo "Error: Database restore failed!"
                    return 1
                fi
            else
                echo "Database restore cancelled."
            fi
            ;;
        5)
            echo "Extracting i18n Messages (POT generation)..."
            docker compose exec "${APP_SERVICE_NAME}" bin/cake i18n extract \
                --paths /var/www/html/src,/var/www/html/plugins,/var/www/html/templates
            ;;
        6)
            echo "Loading Default i18n Data..."
            # Assuming this command loads default PO data or performs initial i18n setup
            docker compose exec "${APP_SERVICE_NAME}" bin/cake load_default_i18n # Adjusted command if it was a typo
            ;;
        7)
            echo "Running i18n Translation (e.g., via an API)..."
            docker compose exec "${APP_SERVICE_NAME}" bin/cake translate_i18n
            ;;
        8)
            echo "Generating PO Files (compile from POT or other sources)..."
            docker compose exec "${APP_SERVICE_NAME}" bin/cake generate_po_files
            ;;
        9)
            echo "Clearing WillowCMS Cache..."
            docker compose exec "${APP_SERVICE_NAME}" bin/cake cache clear_all
            ;;
        10)
            echo "Opening an interactive shell on '${APP_SERVICE_NAME}' container..."
            # Use /bin/bash if available and preferred, otherwise /bin/sh
            docker compose exec -it "${APP_SERVICE_NAME}" /bin/bash || docker compose exec -it "${APP_SERVICE_NAME}" /bin/sh
            ;;
        0)
            echo "Exiting..."
            exit 0
            ;;
        *)
            echo "Error: Invalid option '$choice'"
            return 1 # Indicate failure for invalid option
            ;;
    esac
}

# Function to check if essential Docker services are running
check_docker_services() {
    echo "Checking Docker service status..."
    if ! docker compose ps --services --filter "status=running" | grep -Fxq "${APP_SERVICE_NAME}"; then
        echo "Error: Docker service '${APP_SERVICE_NAME}' is not running."
        echo "Please start the development environment (e.g., using ./setup_dev_env.sh)."
        exit 1
    fi
    if ! docker compose ps --services --filter "status=running" | grep -Fxq "${DB_SERVICE_NAME}"; then
        echo "Error: Docker service '${DB_SERVICE_NAME}' is not running."
        echo "Please start the development environment."
        exit 1
    fi
    echo "Required Docker services are running."
}

# --- Main Program Loop ---
main() {
    check_docker_services # Check at the start

    local choice
    while true; do
        show_header
        show_menu
        read -r -p "Enter your choice [0-10]: " choice
        
        if ! [[ "$choice" =~ ^[0-9]+$ ]] || [ "$choice" -lt 0 ] || [ "$choice" -gt 10 ]; then
            echo
            echo "Error: Please enter a number between 0 and 10."
            pause
            continue # Go to the next iteration of the loop
        fi

        echo # Add a newline for better readability before command output
        if execute_command "$choice"; then
            # Command was successful or handled its own error messages
            if [ "$choice" -ne 0 ] && [ "$choice" -ne 10 ]; then # Don't pause after exit or interactive shell
                 echo "Command finished."
            fi
        else
            # Command returned an error status
            echo "The command encountered an error."
        fi
        
        if [ "$choice" -ne 0 ] && [ "$choice" -ne 10 ]; then
            pause
        fi
    done
}

# Start the program
main