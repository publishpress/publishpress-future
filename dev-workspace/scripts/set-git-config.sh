#!/usr/bin/env bash

# Script to set the Git config for a given directory
# Requires: echo-step.sh in PATH
# Requires: git

# Check if the directory is already in the safe.directory list
if git config --global --get-all safe.directory | grep -Fxq "$1"; then
    echo-step.sh "Git config for $1 already exists, skipping"
else
    echo-step.sh "Setting Git config for $1"
    echo ""
    git config --global --add safe.directory "$1"
fi
