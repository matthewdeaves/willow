#!/bin/bash

# Store the current branch name
current_branch=$(git rev-parse --abbrev-ref HEAD)

# Switch to gh-pages branch
git checkout gh-pages

# Remove old coverage report
rm -rf *

# Copy new coverage report
cp -r webroot/coverage/* .

# Add all files
git add .

# Commit changes
git commit -m "Update code coverage report"

# Push to GitHub
git push origin gh-pages

# Switch back to the original branch
git checkout $current_branch

echo "GitHub Pages updated successfully with the latest coverage report."
