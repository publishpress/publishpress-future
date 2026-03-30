#!/usr/bin/env bash

# Script to build a plugin and create a zip file from source code.

start_time=$(date +%s)

command=${1}
source_path=$(pwd)
dist_path="${source_path}/dist"
cols=$(( ${#TERM} ? $(tput cols) : 80 ))

plugin_name=$(plugin-name.sh)
plugin_slug=$(plugin-slug.sh)
plugin_folder=$(plugin-folder.sh)
plugin_version=$(plugin-version.sh)
tmp_build_dir="${dist_path}/${plugin_folder}"
tmp_internal_vendor_dir="${tmp_build_dir}/lib/"

# Utility scripts are in PATH, call them directly

# Function to display help text
print_help() {
    echo "Usage: $0 [command]"
    echo "Commands:"
    echo "  build-dir    Build the plugin to the dist directory."
    echo "  build           Build the plugin and create a zip file."
    echo "  clean        Clean the dist directory."
    echo "  version      Get the plugin version."
    echo ""
    echo "Options:"
    echo "  -h, --help   Show this help message."
    echo "  HIDE_HEADER  Set this environment variable to '1' to hide the header when running the script."
    echo "               HIDE_HEADER=1 plugin-build.sh build"
}

# Check if user wants to see help or no command is provided
if [[ $1 == "-h" || $1 == "--help" || -z "$1" ]]; then
    echo-header.sh
    echo ""
    print_help
    exit 0
fi

if [ "${HIDE_HEADER}" != "1" ]; then
    echo-header.sh
fi

show_elapsed_time() {
    show-time.sh ${start_time}
}

run_commands() {
    all_success=true

    for passed_command in "$@"; do
        echo-command-header.sh "Running command: ${passed_command}"

        # Execute the command
        eval "${passed_command}"

        # Check the exit status of the command
        if [ $? -ne 0 ]; then
            # If the exit status is not 0, set the flag to false
            all_success=false

            # Show the error message
            echo-error.sh "Command failed: ${passed_command}"

            # Break out of the loop (optional, if you want to stop executing further commands on failure)
            break
        fi

        echo-success.sh "Command successfully executed: ${passed_command}"
        echo-separator.sh
    done

    # Display a message based on whether all commands were successful or not
    if $all_success ; then
        # echo positive emoji
        echo "🎉" " Executed successfully!"
    fi
}

case "${command}" in
"build-dir")
    run_commands "clean-dist.sh" "build-to-dir"
    show_elapsed_time
    ;;
"build")
    run_commands "clean-dist.sh" "build-to-dir" "pack-built-dir.sh"
    show_elapsed_time
    ;;
"clean")
    run_commands "clean-dist.sh"
    ;;
"version")
    echo "${plugin_version}" > version.txt
    ;;
*)
    echo "invalid option ${command}"
    ;;
esac
