#!/usr/bin/env bash

# Script to remove temporary build directories
# Requires: tmp_build_dir variable
# Requires: echo-step.sh in PATH

show_help() {
    echo "Usage: clean-dist.sh [tmp_build_dir]"
    echo ""
    echo "Removes the temporary build directory and its temporary files."
    echo "If tmp_build_dir is not provided, it will use the value of the tmp_build_dir variable."
    echo "If tmp_build_dir is set, it will remove the folder and its temporary files."
    echo "If tmp_build_dir is not set, it will exit with an error."
    echo ""
    echo "Example:"
    echo "clean-dist.sh /tmp/build-dir"
    echo "clean-dist.sh"
    echo "clean-dist.sh /tmp/build-dir"
}

if [ "$1" = "-h" ] || [ "$1" = "--help" ]; then
    show_help
    exit 0
fi

if [ -z "${tmp_build_dir}" ]; then
    tmp_build_dir="$1"
fi

if [ -z "${tmp_build_dir}" ]; then
    echo "Error: tmp_build_dir variable is empty or not set."
    show_help
    exit 1
fi

echo-step.sh "Removing the folder ${tmp_build_dir} if exists"
rm -rf "${tmp_build_dir}" "${tmp_build_dir}-tmp"
