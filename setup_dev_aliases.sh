#!/bin/bash

# Determine the correct rc file
if [ -f ~/.zshrc ]; then
    RC_FILE=~/.zshrc
elif [ -f ~/.bashrc ]; then
    RC_FILE=~/.bashrc
else
    echo "No .zshrc or .bashrc found. Please create one and run this script again. touch ~/.zshrc or touch ~/.bashrc"
    exit 1
fi

# Setup aliases
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

# Setup Git hook
HOOKS_DIR=".git/hooks"
if [ -d "$HOOKS_DIR" ]; then
    if [ -f "hooks/pre-push" ]; then
        # Backup existing hook if it exists
        if [ -f "$HOOKS_DIR/pre-push" ]; then
            mv "$HOOKS_DIR/pre-push" "$HOOKS_DIR/pre-push.bak"
            echo "Backed up existing pre-push hook to pre-push.bak"
        fi
        
        # Copy the new hook
        cp hooks/pre-push "$HOOKS_DIR/pre-push"
        chmod +x "$HOOKS_DIR/pre-push"
        echo "Git pre-push hook installed successfully"
    else
        echo "hooks/pre-push not found in the current directory"
    fi
else
    echo "Not a git repository or .git/hooks directory not found"
fi

# Reload RC_FILE
source "$RC_FILE"

echo "Setup complete!"