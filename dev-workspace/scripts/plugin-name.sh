#!/usr/bin/env bash

# Script to get the plugin name from composer.json file.

# Set the SOURCE_PATH variable to the current directory or use the passed argument.
SOURCE_PATH=${1:-$(pwd)}

# Show the usage information.
usage() {
    echo "Usage: plugin-name.sh [source_path]"
    echo ""
    echo "source_path: The path to the source code of the plugin."
    echo "             Default: The current directory."
}

# Check if the usage information should be displayed.
if [ "$1" == "--help" ] || [ "$1" == "-h" ]; then
    usage
    exit 0
fi

# Check if the composer.json file exists in the source path.
if [ ! -f "${SOURCE_PATH}/composer.json" ]; then
    echo "The composer.json file does not exist in the source path."
    echo ""
    echo "Source path: ${SOURCE_PATH}"
    exit 1
fi

# Get the plugin name from the composer.json file.
parse-json.sh "${SOURCE_PATH}/composer.json" "extra.plugin-name"
