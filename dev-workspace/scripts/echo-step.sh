#!/usr/bin/env bash

# Script to display step messages with arrow prefix

show_help() {
    echo "Usage: echo-step.sh <message>"
    echo ""
    echo "Example:"
    echo "echo-step.sh Building the plugin..."
    echo ""
}

if [ -z "$1" ]; then
    show_help
    exit 1
fi

if [ "$1" = "-h" ] || [ "$1" = "--help" ]; then
    show_help
    exit 0
fi

echo "▶ ${1}"
