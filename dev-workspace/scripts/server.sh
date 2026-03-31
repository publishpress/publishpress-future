#!/usr/bin/env bash

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/env-init.sh"
cd "$REPO_ROOT/dev-workspace"

if ! docker info &>/dev/null; then
  echo -e "\033[0;31mError: Docker is not running. Please start Docker and try again.\033[0m"
  exit 1
fi

if [[ $# -eq 0 ]] || [[ $1 == "-h" ]]; then
  echo "Usage: $0 [up|stop|down|clenaup|refresh|info]"
  exit 1
fi

PROFILE="${2:-dev}"

COMPOSE_FILE=docker/compose.yaml
CACHE_BASE_PATH=$REPO_ROOT/dev-workspace-cache
WP_CACHE=$CACHE_BASE_PATH/wp_${PROFILE}
DB_CACHE=$CACHE_BASE_PATH/db_${PROFILE}

remove_port_from_domain() {
  echo $1 | sed -E 's/:.*//'
}

if [[ ${PROFILE} == "test" ]]; then
  WP_DOMAIN=$(remove_port_from_domain $WP_TESTS_DOMAIN)
  WP_DB_URL=$WP_TESTS_DB_URL
fi

if [[ ${PROFILE} == "dev" ]]; then
  WP_DOMAIN=$(remove_port_from_domain $WP_DEV_DOMAIN)
  WP_DB_URL=$WP_DEV_DB_URL
fi

MAILHOG_CACHE=$CACHE_BASE_PATH/mailhog

WP_DB_USER=$(echo $WP_DB_URL | sed -E 's/mysql:\/\/([^:]+):.*/\1/')
WP_DB_PASS=$(echo $WP_DB_URL | sed -E 's/mysql:\/\/.*:(.*)@.*/\1/')
WP_DB_HOST=$(echo $WP_DB_URL | sed -E 's/mysql:\/\/.*@([^:]+):.*/\1/')
WP_DB_NAME=$(echo $WP_DB_URL | sed -E 's/mysql:\/\/.*@.*\/([^\/]+)$/\1/')


service_up() {
  echo "Starting..."
  docker compose -f $COMPOSE_FILE --profile ${PROFILE} up -d
}

service_stop() {
  echo "Stopping..."
  docker compose -f $COMPOSE_FILE --profile ${PROFILE} stop
}

service_down() {
  echo "Shutting down..."
  docker compose -f $COMPOSE_FILE --profile ${PROFILE} down
}

service_cleanup() {
  echo "Cleaning up..."
  docker compose -f $COMPOSE_FILE --profile ${PROFILE} down
}

get_wp_port() {
  docker compose -f $COMPOSE_FILE port $1 80 | cut -d: -f2
}

get_db_port() {
  docker compose -f $COMPOSE_FILE port $1 3306 | cut -d: -f2
}

get_mailhog_port_8025() {
  docker compose -f $COMPOSE_FILE port mailhog 8025 | cut -d: -f2
}

get_mailhog_port_1025() {
  docker compose -f $COMPOSE_FILE port mailhog 1025 | cut -d: -f2
}

get_container_id() {
  docker compose -f $COMPOSE_FILE ps -q $1
}

service_info() {
  local BOLD='\033[1m'
  local DIM='\033[2m'
  local CYAN='\033[0;36m'
  local GREEN='\033[0;32m'
  local YELLOW='\033[0;33m'
  local NC='\033[0m'

  WP_PORT=$(get_wp_port wp_${PROFILE})
  DB_PORT=$(get_db_port db_${PROFILE})
  MAILHOG_PORT_8025=$(get_mailhog_port_8025)
  MAILHOG_PORT_1025=$(get_mailhog_port_1025)

  echo ""
  echo -e "${BOLD}  WordPress ${DIM}(${PROFILE})${NC}"
  echo -e "  ──────────────────────────────────────────"
  echo -e "  Site URL       ${CYAN}http://$WP_DOMAIN:$WP_PORT${NC}"
  echo -e "  Admin URL      ${CYAN}http://$WP_DOMAIN:$WP_PORT/wp-admin${NC}"
  echo -e "  Login          ${GREEN}$WP_ADMIN_USER${NC} / ${GREEN}$WP_ADMIN_PASSWORD${NC}"
  echo -e "  Root dir       ${DIM}$WP_CACHE${NC}"
  echo -e "  Container      ${DIM}$(get_container_id wp_${PROFILE})${NC}"
  echo ""
  echo -e "${BOLD}  Database${NC}"
  echo -e "  ──────────────────────────────────────────"
  echo -e "  Host           ${CYAN}$WP_DB_HOST:$DB_PORT${NC}"
  echo -e "  Name           $WP_DB_NAME"
  echo -e "  User           ${GREEN}$WP_DB_USER${NC}"
  echo -e "  Password       ${GREEN}$WP_DB_PASS${NC}"
  echo -e "  URL            ${DIM}mysql://$WP_DB_USER:$WP_DB_PASS@$WP_DB_HOST:$DB_PORT/$WP_DB_NAME${NC}"
  echo -e "  Data dir       ${DIM}$DB_CACHE${NC}"
  echo -e "  Container      ${DIM}$(get_container_id db_${PROFILE})${NC}"
  echo ""
  echo -e "${BOLD}  Mail ${DIM}(MailHog)${NC}"
  echo -e "  ──────────────────────────────────────────"
  echo -e "  Web UI         ${CYAN}http://$WP_DOMAIN:$MAILHOG_PORT_8025${NC}"
  echo -e "  SMTP           ${YELLOW}smtp://$WP_DOMAIN:$MAILHOG_PORT_1025${NC}"
  echo -e "  Container      ${DIM}$(get_container_id mailhog)${NC}"
  echo ""
}

if [[ $1 == "up" ]]; then
  bash ./scripts/services-init-cache.sh
  mkdir -p "$MAILHOG_CACHE/maildir"

  service_up
fi

if [[ $1 == "stop" ]]; then
  service_stop
fi

if [[ $1 == "down" ]]; then
  service_down
fi

if [[ $1 == "cleanup" ]]; then
  service_down
  rm -rf "$WP_CACHE" "$DB_CACHE" "$MAILHOG_CACHE"
fi

if [[ $1 == "refresh" ]]; then
  service_cleanup
  service_up
fi

if [[ $1 == "info" ]]; then
  service_info
fi

if [[ $1 == "restart" ]]; then
  service_stop
  service_up
fi
