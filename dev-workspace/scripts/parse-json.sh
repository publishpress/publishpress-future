#!/usr/bin/env bash

# Script to parse JSON files and extract property values using jq.

show_help() {
    echo "Usage: parse-json.sh <json_file_path> <property_path>"
    echo ""
    echo "Arguments:"
    echo "  json_file_path    Path to the JSON file to parse"
    echo "  property_path     Property path using dot notation (e.g., 'extra.plugin-name' or 'name')"
    echo ""
    echo "Options:"
    echo "  -h, --help        Display this help message"
}

# Check for help flag
if [[ "$1" == "-h" || "$1" == "--help" ]]; then
    show_help
    exit 0
fi

# Check if required arguments are provided
if [ $# -ne 2 ]; then
    echo "Usage: parse-json.sh <json_file_path> <property_path>"
    exit 1
fi

json_file="$1"
property_path="$2"

# Check if the JSON file exists
if [ ! -f "$json_file" ]; then
    echo "Error: JSON file not found: $json_file"
    exit 1
fi

# Convert dot notation to jq path format
# e.g., "extra.plugin-name" -> '.extra["plugin-name"]'
# We need to handle properties that may contain hyphens by using bracket notation
IFS='.' read -ra parts <<< "$property_path"
jq_path=""

for part in "${parts[@]}"; do
    # If part contains hyphen or special characters, use bracket notation
    if [[ "$part" =~ [^a-zA-Z0-9_] ]]; then
        jq_path="${jq_path}[\"${part}\"]"
    else
        # For simple identifiers, use dot notation
        if [ -z "$jq_path" ]; then
            jq_path=".${part}"
        else
            jq_path="${jq_path}.${part}"
        fi
    fi
done

# Extract the value using jq
result=$(jq -r "$jq_path // empty" "$json_file" 2>/dev/null)

# Check if jq command was successful and returned a value
if [ $? -ne 0 ]; then
    echo "Error: Failed to parse JSON file"
    exit 1
fi

if [ -z "$result" ]; then
    echo "Error: Property '$property_path' not found in the JSON file."
    exit 1
fi

# Output the result
echo "$result"
