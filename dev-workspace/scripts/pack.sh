#!/usr/bin/env bash

# Script to pack a plugin into a directory and create a zip file from it.

# Enable pipefail so pipelines return the exit code of the first failing command
set -o pipefail

start_time=$(date +%s)

include_debug=0
if [[ "${PACK_INCLUDE_DEBUG:-}" == "1" ]]; then
    include_debug=1
fi

pack_args=()
for a in "$@"; do
    case "$a" in
    --with-debug)
        include_debug=1
        ;;
    *)
        pack_args+=("$a")
        ;;
    esac
done
set -- "${pack_args[@]}"

command=${1:-}
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
    echo "Usage: pack.sh [command] [--with-debug]"
    echo "Commands:"
    echo "  dir          Pack the plugin to the dist directory."
    echo "  zip          Pack the plugin and create a zip file."
    echo "  clean        Clean the dist directory."
    echo "  version      Get the plugin version."
    echo ""
    echo "Options:"
    echo "  --with-debug Include debug-oriented assets in the pack (e.g. *.map, /assets/jsx)."
    echo "               Omit strip-debug rsync filter layers. Same effect as PACK_INCLUDE_DEBUG=1."
    echo "  -h, --help   Show this help message."
    echo "  HIDE_HEADER  Set this environment variable to '1' to hide the header when running the script."
    echo "               HIDE_HEADER=1 pack.sh build"
}

# Check if user wants to see help or no command is provided
if [[ ${command} == "-h" || ${command} == "--help" || -z "${command}" ]]; then
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

    pre_merge_count=0
    rsync_pre_filters=()
    add_pre_merge() {
        local f="$1"
        if [[ -f "$f" ]]; then
            rsync_pre_filters+=( -f "merge ${f}" )
            pre_merge_count=$((pre_merge_count + 1))
        fi
    }

    add_pre_merge "${source_path}/dev-workspace/.rsync-filters-pre-build.default"
    if [[ "${include_debug}" != "1" ]]; then
        add_pre_merge "${source_path}/dev-workspace/.rsync-filters-pre-build.strip-debug"
        add_pre_merge "${source_path}/.rsync-filters-pre-build.strip-debug"
    fi
    add_pre_merge "${source_path}/.rsync-filters-pre-build"

    if [[ "${pre_merge_count}" -eq 0 ]]; then
        echo-error.sh "No pre-build rsync filter files found. Add dev-workspace/.rsync-filters-pre-build.default and/or .rsync-filters-pre-build."
        exit 998
    fi

    if [[ "${include_debug}" == "1" ]]; then
        echo-step.sh "Packing with debug assets (strip-debug filters skipped)"
    else
        echo-step.sh "Packing in release style (debug assets excluded via strip-debug filters)"
    fi

    echo-step.sh "Copying plugin files to dist (layered pre-build rsync filters)"
    mkdir -p "${tmp_build_dir}" || exit 999
    rsync -r "${rsync_pre_filters[@]}" "${source_path}/" "${tmp_build_dir}" || exit 1000

    if [ -d "${tmp_internal_vendor_dir}" ]; then
        echo-step.sh "Installing dependencies on ${tmp_internal_vendor_dir}/vendor"
        echo ""
        run_indented 1002 composer install --no-dev --optimize-autoloader --classmap-authoritative --ansi --working-dir="${tmp_internal_vendor_dir}"
    fi

    post_merge_count=0
    rsync_post_filters=()
    add_post_merge() {
        local f="$1"
        if [[ -f "$f" ]]; then
            rsync_post_filters+=( -f "merge ${f}" )
            post_merge_count=$((post_merge_count + 1))
        fi
    }

    add_post_merge "${source_path}/dev-workspace/.rsync-filters-post-build.default"
    add_post_merge "${source_path}/.rsync-filters-post-build"

    if [[ "${post_merge_count}" -eq 0 ]]; then
        echo-error.sh "No post-build rsync filter files found. Add dev-workspace/.rsync-filters-post-build.default and/or .rsync-filters-post-build."
        exit 1003
    fi

    echo-step.sh "Removing files listed in layered post-build rsync filters"
    rsync -r "${rsync_post_filters[@]}" "${tmp_build_dir}/" "${tmp_build_dir}-tmp" || exit 1004
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
