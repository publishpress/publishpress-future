#!/usr/bin/env bash

# Script to display elapsed runtime
# Requires start_time variable to be set

show_help() {
    echo "Usage: show-time.sh <start_time>"
    echo ""
    echo "Example:"
    echo "show-time.sh 1715904000"
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

start_time=${1}

if [ "${HIDE_HEADER}" != "1" ]; then
    end_time=$(date +%s)
    runtime_seconds=$((end_time - start_time))

    if [ "$runtime_seconds" -lt 1 ]; then
        echo "Runtime: less than 1 second"
    elif [ "$runtime_seconds" = 1 ]; then
        echo "Runtime: $runtime_seconds second"
    else
        echo "Runtime: $runtime_seconds seconds"
    fi
fi
