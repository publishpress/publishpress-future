#!/usr/bin/env bash

ENV_FILE=""

show_help() {
    echo "Usage: $0 [command]"
    echo ""
    echo "If you are outside the dev-workspace terminal, this script will start"
    echo "the dev-workspace environment and execute your command inside it."
    echo "If you are already inside the dev-workspace terminal, your command"
    echo "will be executed directly within the current environment."
    echo ""
    echo "Available commands:"
    echo "  -h, --help: Show this help message"
    echo "  <command>: Run a command in the dev-workspace"
}

check_env_file() {
    if [[ -f ../.env ]]; then
        ENV_FILE="../.env"
    elif [[ -f ./.env ]]; then
        ENV_FILE="./.env"
    else
        echo "Error: .env file not found. Run 'cp .env.example .env' to create it."
        exit 1
    fi
}

set_env_file() {
    set -a
    source "$ENV_FILE"
    set +a
}

run_in_dev_workspace() {
    if [ -z "$INSIDE_DEV_CONTAINER" ]; then
        echo "Initializing dev-workspace environment. Please wait..."

        # If not in the `dev-workspace` directory, change to it
        if [[ ! $(pwd) =~ .*dev-workspace$ ]]; then
            cd dev-workspace
        fi

        bash ./scripts/terminal-service-run.sh "$@"
    else
        $@
    fi
}

if [ "$1" = "-h" ] || [ "$1" = "--help" ]; then
    show_help
    exit 0
fi

check_env_file
set_env_file
run_in_dev_workspace "$@"
