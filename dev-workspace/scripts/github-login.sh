#!/usr/bin/env bash

# Script to set Github token and login user using Github CLI.

# Function to display help text
print_help() {
    echo "Usage: $0"
    echo "Set the Github token and login the user in Github CLI."
    echo "Please provide your Github token when prompted."
}

# Check if user wants to see help
if [[ $1 == "-h" || $1 == "--help" ]]; then
    print_help
    exit 0
fi

# Ask for the Github token
read -rp "Please enter your Github token: " github_token

# Check if token is provided
if [[ -z "$github_token" ]]; then
    echo "Error: Github token cannot be empty."
    exit 1
fi

# Store the token in the file
gh_token_file="$PROJECT_PATH/dev-workspace/cache/gh-token.txt"
echo "$github_token" > "$gh_token_file"

# Display message after storing the token
echo "Github token has been saved in $gh_token_file."

# Login the user in the Github CLI
echo "Logging in the user using Github CLI..."
gh auth login --with-token <<< "$github_token"

# Display completion message
echo "User logged in successfully!"
