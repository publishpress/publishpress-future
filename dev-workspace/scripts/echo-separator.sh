#!/usr/bin/env bash

# Script to display horizontal separator line
# Requires: repeat.sh in PATH and cols variable

show_help() {
    echo "Usage: echo-separator.sh"
    echo ""
    echo "Example:"
    echo "echo-separator.sh"
    echo ""
}

cols=$(terminal-cols.sh)

if [ "$1" = "-h" ] || [ "$1" = "--help" ]; then
    show_help
    exit 0
fi

echo ""
repeat.sh "-" "${cols}"
echo ""
echo ""
