#!/usr/bin/env bash

# Script to get the plugin ZIP file name in the dist dir.

# Set the SOURCE_PATH variable to the current directory or use the passed argument.
SOURCE_PATH=${1:-$(pwd)}

# Show the usage information.
usage() {
    echo "Usage: plugin-zipfile.sh [source_path]"
    echo ""
    echo "source_path: The path to the source code of the plugin."
    echo "             Default: The current directory."
}

# Check if the usage information should be displayed.
if [[ "$1" == "--help" || "$1" == "-h" ]]; then
    usage
    exit 0
fi

# Output the ZIP file name
echo "$(plugin-name.sh)-$(plugin-version.sh).zip"
