#!/usr/bin/env bash

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/env-init.sh"

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

run_in_dev_workspace() {
    if [ -z "$INSIDE_DEV_CONTAINER" ]; then
        echo "Initializing dev-workspace environment. Please wait..."
        cd "$REPO_ROOT/dev-workspace"
        bash ./scripts/terminal-service-run.sh "$@"
    else
        export PATH="$DEV_SCRIPTS_DIR:$PATH"
        "$@"
    fi
}

if [ "$1" = "-h" ] || [ "$1" = "--help" ]; then
    show_help
    exit 0
fi

run_in_dev_workspace "$@"
