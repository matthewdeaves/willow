#!/bin/bash

# Function to clear the screen and show the header
show_header() {
    if command -v clear >/dev/null; then
        clear
    fi
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
    echo "  1) Import Default Data (WillowCMS)"
    echo "  2) Export Default Data (WillowCMS)"
    echo "  3) Dump MySQL Database (to host)"
    echo "  4) Load Database from Backup (from host)"
    echo
    echo "Internationalization (WillowCMS):"
    echo "  5) Extract i18n Messages"
    echo "  6) Load Default i18n"
    echo "  7) Translate i18n"
    echo "  8) Generate PO Files"
    echo
    echo "Asset Management (WillowCMS):"
    echo "  9) Backup Files Directory"
    echo "  10) Restore Files from Backup"
    echo
    echo "System:"
    echo "  11) Clear Cache (WillowCMS)"
    echo "  12) Interactive shell on Willow CMS container"
    echo "  13) Host System Update & Docker Cleanup"
    echo "  0) Exit"
    echo
}