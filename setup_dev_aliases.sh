#!/bin/bash

# Determine the correct rc file
if [ -f ~/.zshrc ]; then
    RC_FILE=~/.zshrc
elif [ -f ~/.bashrc ]; then
    RC_FILE=~/.bashrc
else
    echo "No .zshrc or .bashrc found. Please create one and run this script again."
    exit 1
fi

# Check if dev_aliases.txt exists
if [ -f dev_aliases.txt ]; then
    # Append the contents to RC_FILE if not already present
    if ! grep -q "# CakePHP Development Aliases" "$RC_FILE"; then
        echo "" >> "$RC_FILE"
        echo "# CakePHP Development Aliases" >> "$RC_FILE"
        echo "if [ -f $(pwd)/dev_aliases.txt ]; then" >> "$RC_FILE"
        echo "    . $(pwd)/dev_aliases.txt" >> "$RC_FILE"
        echo "fi" >> "$RC_FILE"
        echo "Aliases added to $RC_FILE"
    else
        echo "Aliases already present in $RC_FILE"
    fi
else
    echo "dev_aliases.txt not found in the current directory"
fi

# Reload RC_FILE
source "$RC_FILE"
