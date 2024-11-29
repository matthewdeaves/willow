#!/bin/bash

# Set strict error handling
set -euo pipefail

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

# Function to clear the screen and show the header
show_header() {
    clear
    echo "==================================="
    echo "WillowCMS Command Runner"
    echo "==================================="
    echo
}

# Function to display the menu
show_menu() {
    echo "Available Commands:"
    echo
    echo "Data Management:"
    echo "  1) Import Default Data"
    echo "  2) Export Default Data"
    echo
    echo "Internationalization:"
    echo "  3) Extract i18n Messages"
    echo "  4) Load Default i18n"
    echo "  5) Translate i18n"
    echo "  6) Generate PO Files"
    echo
    echo "System:"
    echo "  7) Clear Cache"
    echo "  8) Exit"
    echo
}

# Function to pause and wait for user input
pause() {
    echo
    read -p "Press [Enter] key to continue..." fackEnterKey
}

# Function to execute commands
execute_command() {
    case $1 in
        1)
            echo "Running Default Data Import..."
            $(needs_sudo) docker compose exec willowcms bin/cake default_data_import
            ;;
        2)
            echo "Running Default Data Export..."
            $(needs_sudo) docker compose exec willowcms bin/cake default_data_export
            ;;
        3)
            echo "Extracting i18n Messages..."
            $(needs_sudo) docker compose exec willowcms bin/cake i18n extract \
                --paths /var/www/html/src,/var/www/html/plugins,/var/www/html/templates
            ;;
        4)
            echo "Loading Default i18n..."
            $(needs_sudo) docker compose exec willowcms bin/cake load_default18n
            ;;
        5)
            echo "Running i18n Translation..."
            $(needs_sudo) docker compose exec willowcms bin/cake translate_i18n
            ;;
        6)
            echo "Generating PO Files..."
            $(needs_sudo) docker compose exec willowcms bin/cake generate_po_files
            ;;
        7)
            echo "Clearing Cache..."
            $(needs_sudo) docker compose exec willowcms bin/cake cache clear_all
            ;;
        8)
            echo "Exiting..."
            exit 0
            ;;
        *)
            echo "Error: Invalid option"
            ;;
    esac
}

# Function to check if Docker is running
check_docker() {
    if ! $(needs_sudo) docker compose ps --services --filter "status=running" | grep -q "willowcms"; then
        echo "Error: WillowCMS Docker container is not running"
        echo "Please start the containers first using ./setup_dev_env.sh"
        exit 1
    fi
}

# Main program loop
main() {
    local choice

    # Check if Docker is running first
    check_docker

    while true; do
        show_header
        show_menu
        read -p "Enter your choice [1-8]: " choice
        
        if [[ ! $choice =~ ^[1-8]$ ]]; then
            echo "Error: Please enter a number between 1 and 8"
            pause
            continue
        fi

        echo
        execute_command "$choice"
        
        if [ "$choice" != "8" ]; then
            pause
        fi
    done
}

# Start the program
main