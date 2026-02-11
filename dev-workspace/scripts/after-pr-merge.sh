#!/usr/bin/env bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print a message in green
print_success() {
    echo -e "${GREEN}$1${NC}"
}

# Function to print a message in yellow
print_warning() {
    echo -e "${YELLOW}$1${NC}"
}

print_normal() {
    echo -e "${NC}$1${NC}"
}

CURRENT_BRANCH=$(git branch --show-current)
print_normal "Current branch: ${CURRENT_BRANCH}"

# Check if there are any uncommitted changes
if [ -n "$(git status -s)" ]; then
    print_warning "You have uncommitted changes. Please commit or stash them first."
    exit 1
fi

if [ "${CURRENT_BRANCH}" == "development" ] || [ "${CURRENT_BRANCH}" == "main" ] || [ "${CURRENT_BRANCH}" == "master" ]; then
    print_warning "You are on a protected branch. You can't remove it."
    exit 1
fi

print_normal "Fetching latest changes..."
git fetch origin --prune

print_normal "Checking out development branch..."
git checkout development

print_normal "Pulling latest changes..."
git pull

print_normal "Deleting task remote branch ${CURRENT_BRANCH}..."
git push origin --delete ${CURRENT_BRANCH} || true
print_success "Task remote branch ${CURRENT_BRANCH} removed."

print_normal "Deleting task branch ${CURRENT_BRANCH}..."
git branch -D ${CURRENT_BRANCH} || true
print_success "Task branch ${CURRENT_BRANCH} removed."

print_normal "Done."
