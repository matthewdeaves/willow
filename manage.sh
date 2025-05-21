#!/bin/bash

echo "DEBUG (manage.sh): Script using shell: $SHELL (effective shell is bash)"
echo "DEBUG (manage.sh): Path to bash: $(which bash)" # Should be /bin/bash or /usr/bin/bash
echo "DEBUG (manage.sh): Bash version: $(bash --version | head -n 1)"
echo "DEBUG (manage.sh): Path to grep: $(which grep)" # Critical: Is this GNU grep?
echo "DEBUG (manage.sh): Grep version: $(grep --version | head -n 1)" # Critical
echo "DEBUG (manage.sh): PATH variable in script: $PATH"
echo "--- End of initial script debug (manage.sh) ---"

# Load all modules
SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"
MODULE_DIR="${SCRIPT_DIR}/tool_modules"

# Check if module directory exists
if [ ! -d "$MODULE_DIR" ]; then
    echo "Error: Module directory not found at: $MODULE_DIR"
    exit 1
fi

# Source each module
source "${MODULE_DIR}/common.sh"
source "${MODULE_DIR}/ui.sh"
source "${MODULE_DIR}/service_checks.sh"
source "${MODULE_DIR}/data_management.sh"
source "${MODULE_DIR}/internationalization.sh"
source "${MODULE_DIR}/asset_management.sh"  # New module
source "${MODULE_DIR}/system.sh"

# Start the main function
main
