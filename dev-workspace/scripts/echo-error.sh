#!/usr/bin/env bash

# Script to display error messages with X mark

show_help() {
    echo "Usage: echo-error.sh <message>"
    echo ""
    echo "Example:"
    echo "echo-error.sh An error occurred while building the plugin."
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

echo "❌" " ${1}"
