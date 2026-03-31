#!/usr/bin/env bash

if [ -n "$INSIDE_DEV_CONTAINER" ]; then
    RED='\033[0;31m'
    YELLOW='\033[1;33m'
    NC='\033[0m' # No Color

    echo -e "${RED}You are inside the dev-workspace terminal.${NC}"
    echo -e "${YELLOW}⚠️  This command is not meant to be run inside the dev-workspace terminal. Please run it outside the dev-workspace terminal.${NC}"
    exit 1
fi
