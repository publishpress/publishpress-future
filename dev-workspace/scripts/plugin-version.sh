#!/usr/bin/env bash

# Script to get the plugin version.

SOURCE_PATH=${1:-$(pwd)}
PLUGIN_SLUG=$(plugin-slug.sh ${SOURCE_PATH})
PLUGIN_FILE_PATH="${SOURCE_PATH}/${PLUGIN_SLUG}.php"

usage() {
    echo "Usage: plugin-version.sh [source_path]"
    echo "source_path: Path to the plugin source code. Default: Current directory."
}

# Display usage if help is requested
if [[ "$1" == "--help" || "$1" == "-h" ]]; then
    usage
    exit 0
fi

# Check if the main plugin file exists
if [ ! -f "${PLUGIN_FILE_PATH}" ]; then
    echo "Error: Main plugin file not found in ${SOURCE_PATH}"
    exit 1
fi

# Extract and output the plugin version
grep "* Version:" "${PLUGIN_FILE_PATH}" | sed -E 's/[^0-9.]*([0-9.]+(-[a-zA-Z]+\.[0-9]+)?).*/\1/'
