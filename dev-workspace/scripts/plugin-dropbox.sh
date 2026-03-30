#!/usr/bin/env bash

# Based on the CLI script: https://github.com/guillaumeisabelleevaluating/Dropbox-Uploader/

# Show usage
usage() {
    echo "Usage: plugin-dropbox.sh [command] [source_path]"
    echo ""
    echo "command: The command to execute."
    echo "         Default: The list command."
    echo "source_path: The path to the source code of the plugin."
    echo "             Default: The current directory."
    echo ""
    echo "Commands:"
    echo "  list: List the files in the Dropbox folder."
    echo "  upload: Upload the plugin ZIP file to the Dropbox folder."
    echo "  remove: Remove the plugin ZIP file from the Dropbox folder."
    echo "  share: Share the plugin ZIP file from the Dropbox folder."
    echo "  unlink: Unlink the Dropbox account."
    echo "  help: Show the usage information."
}

# Check if the usage information should be displayed.
if [ "$1" == "--help" ] || [ "$1" == "-h" ]; then
    usage
    exit 0
fi

SOURCE_PATH=${2:-$(pwd)}
COMMAND=${1:-list}
DROPBOX_FOLDER="/"
PLUGIN_ZIP_FILE=$(plugin-zipfile.sh)
PLUGIN_ZIP_FULL_PATH="${SOURCE_PATH}/dist/${PLUGIN_ZIP_FILE}"

# List the files in the Dropbox folder.
list_files() {
    echo "Listing the files in the Dropbox folder..."
    droxul list $DROPBOX_FOLDER
}

# Upload the plugin ZIP file to the Dropbox folder.
upload_file() {
    echo "Uploading the plugin ZIP file to the Dropbox folder..."
    droxul upload $PLUGIN_ZIP_FULL_PATH $DROPBOX_FOLDER/$PLUGIN_ZIP_FILE
}

# Remove the plugin ZIP file from the Dropbox folder.
remove_file() {
    echo "Removing the plugin ZIP file from the Dropbox folder..."
    droxul remove $DROPBOX_FOLDER/$PLUGIN_ZIP_FILE
}

# Share the plugin ZIP file from the Dropbox folder.
share_file() {
    echo "Sharing the plugin ZIP file from the Dropbox folder..."
    droxul share $DROPBOX_FOLDER/$PLUGIN_ZIP_FILE
}

# Unlink the Dropbox account.
unlink_account() {
    echo "Unlinking the Dropbox account..."
    droxul unlink
}

# Execute the command.
case $COMMAND in
    list)
        list_files
        ;;
    upload)
        upload_file
        ;;
    remove)
        remove_file
        ;;
    share)
        share_file
        ;;
    unlink)
        unlink_account
        ;;
    *)
        echo "Invalid command: $COMMAND"
        usage
        exit 1
        ;;
esac
