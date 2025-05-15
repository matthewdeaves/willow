#!/bin/bash

# Determine the correct rc file
if [ -f ~/.zshrc ]; then
    RC_FILE=~/.zshrc
    SHELL_NAME="zsh"
elif [ -f ~/.bashrc ]; then
    RC_FILE=~/.bashrc
    SHELL_NAME="bash"
else
    echo "No .zshrc or .bashrc found. Please create one and run this script again."
    echo "Example: touch ~/.bashrc"
    exit 1
fi

echo "Identified RC file: $RC_FILE (for $SHELL_NAME)"

# Define the absolute path to dev_aliases.txt
# This assumes setup_dev_aliases.sh is in the same directory as dev_aliases.txt
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
ALIASES_FILE="$SCRIPT_DIR/dev_aliases.txt" # Absolute path to dev_aliases.txt

# Ensure dev_aliases.txt itself is clean (optional check, good practice)
if [ -f "$ALIASES_FILE" ]; then
    # Basic check for DOS line endings (not foolproof but a common issue)
    if grep -q $'\r' "$ALIASES_FILE"; then
        echo "WARNING: dev_aliases.txt appears to have DOS (CRLF) line endings."
        echo "This can cause issues. Please convert it to Unix (LF) line endings."
        echo "You can use: dos2unix \"$ALIASES_FILE\""
    fi
    # Basic check for shebang (should not be present)
    if head -n 1 "$ALIASES_FILE" | grep -q '^#!'; then
        echo "WARNING: dev_aliases.txt appears to have a shebang (e.g., #!/bin/bash) on the first line."
        echo "This file is meant to be sourced and should not have a shebang. Please remove it."
    fi
else
    echo "ERROR: dev_aliases.txt not found at $ALIASES_FILE"
    exit 1
fi


# Check if aliases are already sourced or add them
ALIASES_MARKER="# CakePHP WillowCMS Development Aliases Sourced"
if ! grep -Fq "$ALIASES_MARKER" "$RC_FILE"; then
    echo "" >> "$RC_FILE"
    echo "$ALIASES_MARKER" >> "$RC_FILE"
    # Use the absolute path determined earlier
    echo "if [ -f \"$ALIASES_FILE\" ]; then" >> "$RC_FILE"
    echo "    . \"$ALIASES_FILE\"" >> "$RC_FILE"
    echo "fi" >> "$RC_FILE"
    echo "Alias sourcing added to $RC_FILE. Please run 'source $RC_FILE' or open a new terminal."
else
    echo "Alias sourcing already present in $RC_FILE."
fi

# Setup Git hook
# (Your git hook logic seems fine, keeping it as is)
HOOKS_DIR=".git/hooks"
if [ -d "$HOOKS_DIR" ]; then
    if [ -f "$SCRIPT_DIR/hooks/pre-push" ]; then # Assuming hooks is relative to script
        if [ -f "$HOOKS_DIR/pre-push" ] && ! cmp -s "$SCRIPT_DIR/hooks/pre-push" "$HOOKS_DIR/pre-push"; then
            mv "$HOOKS_DIR/pre-push" "$HOOKS_DIR/pre-push.bak.$(date +%s)"
            echo "Backed up existing pre-push hook."
        fi
        # Copy only if different or not existing
        if ! [ -f "$HOOKS_DIR/pre-push" ] || ! cmp -s "$SCRIPT_DIR/hooks/pre-push" "$HOOKS_DIR/pre-push"; then
            cp "$SCRIPT_DIR/hooks/pre-push" "$HOOKS_DIR/pre-push"
            chmod +x "$HOOKS_DIR/pre-push"
            echo "Git pre-push hook installed/updated successfully."
        else
            echo "Git pre-push hook is already up to date."
        fi
    else
        echo "WARNING: hooks/pre-push not found in $SCRIPT_DIR/hooks/"
    fi
else
    echo "WARNING: Not a git repository or .git/hooks directory not found. Skipping Git hook setup."
fi

echo ""
echo "Setup script finished."
echo "IMPORTANT: Please run 'source $RC_FILE' or open a new terminal session to apply changes."
# The "source '$RC_FILE'" at the end of the setup script *might* still trigger the syntax error
# IF dev_aliases.txt is not clean when the setup script runs for the first time.
# It's generally safer to instruct the user to source it manually after fixing dev_aliases.txt.