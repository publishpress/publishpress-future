#!/usr/bin/env bash

# Script to get the plugin file name.

# Set the SOURCE_PATH variable to the current directory or use the passed argument.
SOURCE_PATH=${1:-$(pwd)}
PLUGIN_SLUG=$(plugin-slug.sh ${SOURCE_PATH})
PLUGIN_FILE_PATH="${SOURCE_PATH}/${PLUGIN_SLUG}.php"

# Show the usage information.
usage() {
    echo "Usage: plugin-file.sh [source_path]"
    echo ""
    echo "source_path: The path to the source code of the plugin."
    echo "             Default: The current directory."
}

# Check if the usage information should be displayed.
if [ "$1" == "--help" ] || [ "$1" == "-h" ]; then
    usage
    exit 0
fi

echo $PLUGIN_FILE_PATH
