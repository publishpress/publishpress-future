#!/usr/bin/env bash

set -e
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR/.."
source ../.env
export WPCLI_IMAGE_NAME="${WPCLI_IMAGE_NAME}"
bash ./scripts/build-push-image.sh "${WPCLI_IMAGE_NAME}" ./docker/wp/wpcli "$@"
