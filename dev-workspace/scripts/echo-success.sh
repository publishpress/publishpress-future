#!/usr/bin/env bash

# Script to display success messages with checkmark

show_help() {
    echo "Usage: echo-success.sh <message>"
    echo ""
    echo "Example:"
    echo "echo-success.sh Successfully built the plugin."
    echo ""
}

if [ "$1" = "-h" ] || [ "$1" = "--help" ]; then
    show_help
    exit 0
fi

if [ -z "$1" ]; then
    show_help
    exit 1
fi

echo "✅" " ${1}"
