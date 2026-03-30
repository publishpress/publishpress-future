#!/usr/bin/env bash

# Script to display warning messages with warning sign

show_help() {
    echo "Usage: echo-warning.sh <message>"
    echo ""
    echo "Example:"
    echo "echo-warning.sh This is a warning message."
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

echo "⚠️" " ${1}"
