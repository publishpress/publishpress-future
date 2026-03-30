#!/usr/bin/env bash

# If not in the `dev-workspace` directory, change to it
if [[ ! $(pwd) =~ .*dev-workspace$ ]]; then
  cd dev-workspace
fi

set -a
source ../.env
set +a

if [[ $# -eq 0 ]] || [[ $1 == "-h" ]]; then
  echo "Usage: $0 [up|stop|down|clenaup|refresh|info]"
  exit 1
fi

PROFILE="${2:-dev}"

COMPOSE_FILE=docker/compose.yaml
CACHE_BASE_PATH=$(pwd)/.cache
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
  WP_PORT=$(get_wp_port wp_${PROFILE})
  DB_PORT=$(get_db_port db_${PROFILE})
  MAILHOG_PORT_8025=$(get_mailhog_port_8025)
  MAILHOG_PORT_1025=$(get_mailhog_port_1025)

  echo "=============================================="
  echo "WordPress Development Information"
  echo "=============================================="
  echo "Site URL:       http://$WP_DOMAIN:$WP_PORT"
  echo "Admin URL:      http://$WP_DOMAIN:$WP_PORT/wp-admin"
  echo "Login:          $WP_ADMIN_USER / $WP_ADMIN_PASSWORD"
  echo "Root Directory: $WP_CACHE"
  echo "Container ID:   $(get_container_id wp_${PROFILE})"
  echo ""
  echo "DB Url:         mysql://$WP_DB_USER:$WP_DB_PASS@$WP_DB_HOST:$DB_PORT"
  echo "DB Name:        $WP_DB_NAME"
  echo "DB Host:        $WP_DB_HOST:$DB_PORT"
  echo "DB User:        $WP_DB_USER"
  echo "DB Pass:        $WP_DB_PASS"
  echo "Data Directory: $WP_DB_CACHE"
  echo "Container ID:   $(get_container_id db_${PROFILE})"
  echo ""
  echo "=============================================="
  echo "Mail Information"
  echo "=============================================="
  echo "Web Interface:  http://$WP_DOMAIN:$MAILHOG_PORT_8025"
  echo "SMTP Server:    smtp://$WP_DOMAIN:$MAILHOG_PORT_1025"
  echo "Container ID:   $(get_container_id mailhog)"
  echo ""
  echo "=============================================="
}

if [[ $1 == "up" ]]; then
  # Create the mailhog cache directory if it doesn't exist
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
