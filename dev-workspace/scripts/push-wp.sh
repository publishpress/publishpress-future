#!/usr/bin/env bash

set -e
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR/.."
source ../.env
export WP_IMAGE_NAME="${WP_IMAGE_NAME}"
bash ./scripts/build-push-image.sh "${WP_IMAGE_NAME}" ./docker/wp/wordpress "$@"
