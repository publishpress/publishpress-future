#!/usr/bin/env bash

# Script to build a plugin and create a zip file from source code.

# Enable pipefail so pipelines return the exit code of the first failing command
set -o pipefail

start_time=$(date +%s)

command=${1}
source_path=$(pwd)
dist_path="${source_path}/dist"
cols=$(terminal-cols.sh)

plugin_name=$(plugin-name.sh)
plugin_slug=$(plugin-slug.sh)
plugin_folder=$(plugin-folder.sh)
plugin_version=$(plugin-version.sh)
tmp_build_dir="${dist_path}/${plugin_folder}"
tmp_internal_vendor_dir="${tmp_build_dir}/lib"

# Utility scripts are in PATH, call them directly

# Function to display help text
show_help() {
    echo "Usage: build.sh [command]"
    echo "Commands:"
    echo "  dir          Build the plugin to the dist directory."
    echo "  zip          Build the plugin and create a zip file."
    echo "  clean        Clean the dist directory."
    echo "  version      Get the plugin version."
    echo ""
    echo "Options:"
    echo "  -h, --help   Show this help message."
    echo "  HIDE_HEADER  Set this environment variable to '1' to hide the header when running the script."
    echo "               HIDE_HEADER=1 build.sh build"
}

# Check if user wants to see help or no command is provided
if [[ $1 == "-h" || $1 == "--help" || -z "$1" ]]; then
    echo-header.sh
    echo ""
    show_help
    exit 0
fi

if [ "${HIDE_HEADER}" != "1" ]; then
    echo-header.sh
fi

show_elapsed_time() {
    show-time.sh ${start_time}
}

# Run a command with indented output (preserves colors)
# Usage: run_indented <exit_code> <command> [args...]
run_indented() {
    echo ""
    local exit_code=$1
    shift
    "$@" 2>&1 | while IFS= read -r line; do echo "│ $line"; done || exit $exit_code
    echo ""
}

command_dir() {
    echo-command-header.sh "Cleaning dist directory"
    clean-dist.sh ${tmp_build_dir}

    echo-command-header.sh "Building plugin to dist directory"

    echo-step.sh "Copying plugin files to the dist dir filtering the files listed on .rsync-filters-pre-build"
    mkdir -p "${tmp_build_dir}" || exit 999
    rsync -r -f 'merge .rsync-filters-pre-build' "${source_path}/" "${tmp_build_dir}" || exit 1000

    if [ -d "${tmp_internal_vendor_dir}" ]; then
        echo-step.sh "Installing dependencies on ${tmp_internal_vendor_dir}/vendor"
        echo ""
        run_indented 1002 composer install --no-dev --optimize-autoloader --classmap-authoritative --ansi --working-dir="${tmp_internal_vendor_dir}"
    fi

    echo-step.sh "Removing files listed on .rsync-filters-post-build"
    rsync -r -f 'merge .rsync-filters-post-build' "${tmp_build_dir}/" "${tmp_build_dir}-tmp" || exit 1004
    rm -rf "${tmp_build_dir}" || exit 1005

    echo-command-header.sh "Moving the temporary build directory to the final build directory"

    echo-step.sh "Moving to ${tmp_build_dir}"
    mv "${tmp_build_dir}-tmp" "${tmp_build_dir}" || exit 1006

    echo-command-header.sh "Verifying the build directory ${tmp_build_dir}"
    if [ ! -d "${tmp_build_dir}" ]; then
        echo-error.sh "Build directory ${tmp_build_dir} does not exist"
        exit 1007
    else
        echo-step.sh "Asserting that build directory ${tmp_build_dir} exists"
        echo-step.sh "Listing the build directory ${tmp_build_dir}"
        echo ""
        run_indented 1007 ls -lha "${tmp_build_dir}"
    fi
}

command_zip() {
    echo-command-header.sh "Packaging plugin directory into zip archive"

    zip_filename=$(plugin-zipfile.sh ${source_path})
    zip_path="${dist_path}/${zip_filename}"

    echo-step.sh "Removing old zip file on ${zip_path}, if exists"
    rm -f "${zip_path}" || exit 1
    pushd "${dist_path}" >/dev/null 2>&1 || exit 2

    echo-step.sh "Normalizing file permissions on ${plugin_folder}"
    find ./${plugin_folder} -type f -exec chmod 644 {} \;
    find ./${plugin_folder} -type d -exec chmod 755 {} \;

    echo-step.sh "Creating the zip file on ${zip_path} with normalized permissions"
    zip -qr "${zip_path}" ./${plugin_folder} || exit 3
    popd >/dev/null 2>&1 || exit 4

    echo-command-header.sh "Verifying the zip file ${zip_path}"
    if [ ! -f "${zip_path}" ]; then
        echo-error.sh "Zip file ${zip_path} does not exist"
        exit 1008
    else
        echo-step.sh "Asserting that zip file ${zip_path} exists"
        echo-step.sh "Listing the zip file ${zip_path}"
        echo ""
        run_indented 1008 ls -lha "${zip_path}"
    fi
}

case "${command}" in
"dir")
    set-git-config.sh ${source_path}
    command_dir

    echo-separator.sh
    show_elapsed_time
    echo ""

    echo "🎉" " Executed successfully!"
    echo ""
    exit 0
    ;;
"zip")
    set-git-config.sh ${source_path}
    command_dir
    command_zip

    echo-separator.sh
    show_elapsed_time
    echo ""

    echo "🎉" " Executed successfully!"
    echo ""
    exit 0
    ;;
"clean")
    echo-command-header.sh "Cleaning dist directory"
    clean-dist.sh ${tmp_build_dir}

    echo-separator.sh
    show_elapsed_time
    echo ""

    echo "🎉" " Executed successfully!"
    echo ""
    exit 0
    ;;
"version")
    echo-command-header.sh "Getting plugin version"
    echo "${plugin_version}" > version.txt

    echo-separator.sh
    show_elapsed_time
    echo ""

    echo "🎉" " Executed successfully!"
    echo ""
    exit 0
    ;;
*)
    echo-error.sh "invalid option ${command}"
    echo-separator.sh
    echo ""
    show_help
    show_elapsed_time
    echo ""
    echo "⚠️  Error: Command '${command}' failed or is invalid. Please check your input and try again."
    echo ""
    exit 1
    ;;
esac
