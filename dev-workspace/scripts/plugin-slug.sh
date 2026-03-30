#!/usr/bin/env bash

# Script to get the plugin slug from composer.json file.

SOURCE_PATH=${1:-$(pwd)}
COMPOSER_FILE="${SOURCE_PATH}/composer.json"

usage() {
    echo "Usage: plugin-slug.sh [source_path]"
    echo "source_path: Path to the plugin source code. Default: Current directory."
}

[[ "$1" == "--help" || "$1" == "-h" ]] && { usage; exit 0; }

[[ ! -f "$COMPOSER_FILE" ]] && { echo "Error: composer.json not found in ${SOURCE_PATH}"; exit 1; }

parse-json.sh "$COMPOSER_FILE" "extra.plugin-slug"
