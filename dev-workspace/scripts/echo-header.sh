#!/usr/bin/env bash

# Script to display the formatted plugin builder header
# Requires: echo-box-line.sh, workspace-version.sh, plugin-name.sh, plugin-version.sh, plugin-slug.sh, plugin-folder.sh in PATH

show_help() {
    echo "Usage: echo-header.sh"
    echo ""
    echo "Example:"
    echo "echo-header.sh"
    echo ""
}

if [ "$1" = "-h" ] || [ "$1" = "--help" ]; then
    show_help
    exit 0
fi

echo "PUBLISHPRESS PLUGIN BUILDER - Dev-workspace ${DEV_WORKSPACE_VERSION}"
echo-separator.sh
echo "      __"
echo "    .' o)=-     Plugin name: $(plugin-name.sh)"
echo "    /.-.'       Plugin slug: $(plugin-slug.sh)"
echo "  ///  |\\       Plugin folder: $(plugin-folder.sh)"
echo "   ||  |'       Plugin version: $(plugin-version.sh)"
echo " _,:|_/_        "
echo ""
