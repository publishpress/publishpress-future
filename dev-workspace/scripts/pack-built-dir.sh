#!/usr/bin/env bash

# Script to create zip file from built directory
# Requires: source_path, dist_path, plugin_folder
# Requires: echo-step.sh, plugin-zipfile.sh in PATH

show_help() {
    echo "Usage: pack-built-dir.sh <source_path> <dist_path> <plugin_folder>"
    echo ""
    echo "Example:"
    echo "pack-built-dir.sh /path/to/plugin /path/to/dist /path/to/plugin-folder"
    echo ""
}

if [ "$1" = "-h" ] || [ "$1" = "--help" ]; then
    show_help
    exit 0
fi

if [ -z "${source_path}" ]; then
    source_path="$1"
fi

if [ -z "${dist_path}" ]; then
    dist_path="$2"
fi

if [ -z "${plugin_folder}" ]; then
    plugin_folder="$3"
fi

if [ -z "${source_path}" ] || [ -z "${dist_path}" ] || [ -z "${plugin_folder}" ]; then
    show_help
    exit 1
fi

# Get zip filename using plugin-zipfile.sh script
zip_filename=$(plugin-zipfile.sh ${source_path})
zip_path="${dist_path}/${zip_filename}"

echo-step.sh "Removing old zip file, if exists"
rm -f "${zip_path}" || exit 1
pushd "${dist_path}" >/dev/null 2>&1 || exit 2

# Normalize permissions before zipping
echo-step.sh "Normalizing file permissions"
find ./${plugin_folder} -type f -exec chmod 644 {} \;
find ./${plugin_folder} -type d -exec chmod 755 {} \;

echo-step.sh "Creating the zip file on dist/${zip_filename} with normalized permissions"
zip -qr "${zip_path}" ./${plugin_folder} || exit 3
popd >/dev/null 2>&1 || exit 4
