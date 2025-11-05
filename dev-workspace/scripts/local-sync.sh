#!/usr/bin/env bash

# Load the .env file
source .env

SHOULD_WATCH="false"

if [[ $1 == "--watch" ]]; then
  SHOULD_WATCH="true"
fi

# Function to perform the sync
sync_files() {
  echo "[$(date '+%Y-%m-%d %H:%M:%S')] Syncing files..."
  rsync -avz --exclude-from=.rsync-filters-dev-sync ./ ${LOCAL_SYNC_TARGET_DIR}
  echo "[$(date '+%Y-%m-%d %H:%M:%S')] Sync complete."
}

# Sync the plugin with the dev site using rsync and excluding files/directories listed on .rsync-filters-dev-sync
if [[ $SHOULD_WATCH == "true" ]]; then
  # Detect OS
  OS="$(uname -s)"

  # Perform initial sync
  sync_files

  echo "Watching for file changes..."

  if [[ "$OS" == "Linux" ]]; then
    # Check if inotifywait is installed
    if ! command -v inotifywait &> /dev/null; then
      echo "Error: inotifywait is not installed. Install it with: sudo apt-get install inotify-tools"
      exit 1
    fi

    # Watch for file changes using inotifywait (Linux)
    # Exclude common directories that shouldn't trigger syncs
    while inotifywait -r -e modify,create,delete,move \
      --exclude '\.git' \
      --exclude 'node_modules' \
      --exclude 'vendor' \
      --exclude 'dist' \
      --exclude '\.rsync-filters-dev-sync' \
      . 2>/dev/null; do
      sync_files
    done

  elif [[ "$OS" == "Darwin" ]]; then
    # Check if fswatch is installed
    if ! command -v fswatch &> /dev/null; then
      echo "Error: fswatch is not installed. Install it with: brew install fswatch"
      exit 1
    fi

    # Watch for file changes using fswatch (macOS)
    # Exclude common directories that shouldn't trigger syncs
    fswatch -o -r \
      --exclude '\.git' \
      --exclude 'node_modules' \
      --exclude 'vendor' \
      --exclude 'dist' \
      --exclude '\.rsync-filters-dev-sync' \
      . | while read f; do
      sync_files
    done

  else
    echo "Error: Unsupported operating system: $OS"
    exit 1
  fi

else
  sync_files
fi
