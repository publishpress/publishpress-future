#!/usr/bin/env bash

# If not in the `dev-workspace` directory, change to it
if [[ ! $(pwd) =~ .*dev-workspace$ ]]; then
  cd dev-workspace
fi

set -a
source /project/.env
set +a

SERVICE_NAME=$1
shift 1

# Check if wp-cli container is running, if not start it
if ! docker compose -f docker/compose.yaml ps | grep -q "_env_$SERVICE_NAME.*Up"; then
    echo "Starting $SERVICE_NAME container..."
    docker compose -f docker/compose.yaml up -d $SERVICE_NAME
fi

# Execute WP-CLI command and pass all arguments
echo "Running: wp $@"
docker compose -f docker/compose.yaml exec $SERVICE_NAME wp "$@"
