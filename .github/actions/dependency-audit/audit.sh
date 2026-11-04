#!/usr/bin/env bash
set +x
set -euo pipefail
umask 077

: "${DASHBOARD_URL:?dashboard_url is required}"
: "${ENTITY_SLUG:?entity_slug is required}"
: "${USERNAME:?username is required}"
: "${APP_PASSWORD:?app_password is required}"
: "${GITHUB_WORKSPACE:?GITHUB_WORKSPACE is required}"

COMPOSER_PROD_DIR=${COMPOSER_PROD_DIR:-}
case "$COMPOSER_PROD_DIR" in
  /*|*..*|*\\*|*//*|*/)
    echo 'composer_prod_dir must be a relative path without .., backslashes, or empty path segments.' >&2
    exit 1
    ;;
esac

ACTION_WORK_DIR=$(mktemp -d)
trap 'rm -rf "$ACTION_WORK_DIR"' EXIT

validate_json_file() {
  local path=$1
  jq empty "$path" >/dev/null
}

manifest_present() {
  local path=$1
  if [[ ! -e $path ]]; then
    printf false
    return
  fi
  validate_json_file "$path"
  printf true
}

lock_json() {
  local path=$1 output=$2
  if [[ ! -e $path ]]; then
    printf null > "$output"
    return
  fi
  jq 'if type == "object" then {packages: (.packages // []), "packages-dev": (."packages-dev" // [])} else error("Composer lock must be a JSON object") end' \
    "$path" > "$output"
}

npm_lock_json() {
  local path=$1 output=$2
  if [[ ! -e $path ]]; then
    printf null > "$output"
    return
  fi
  jq 'if type == "object" then . else error("npm lock must be a JSON object") end' "$path" > "$output"
}

collect_alerts() {
  local kind=$1 result=$2
  local owner_repo=${GITHUB_REPOSITORY:-}
  local url="https://api.github.com/repos/${owner_repo}/${kind}/alerts?state=open&per_page=100"
  local page=1 body="$ACTION_WORK_DIR/${kind}.body" headers="$ACTION_WORK_DIR/${kind}.headers"
  local combined="$ACTION_WORK_DIR/${kind}.combined"
  printf '[]' > "$combined"

  while :; do
    local status
    if ! status=$(curl --silent --output "$body" --dump-header "$headers" --write-out '%{http_code}' \
      --header 'Accept: application/vnd.github+json' \
      --header 'X-GitHub-Api-Version: 2022-11-28' \
      --header "Authorization: Bearer ${GITHUB_TOKEN:-}" \
      "$url"); then
      jq -n '{ok: false, alerts: []}' > "$result"
      return
    fi

    if [[ $status == 404 ]]; then
      jq -n '{ok: true, alerts: []}' > "$result"
      return
    fi
    if [[ $status != 200 ]] || ! jq -e 'type == "array"' "$body" >/dev/null 2>&1; then
      jq -n '{ok: false, alerts: []}' > "$result"
      return
    fi

    jq -s '.[0] + .[1]' "$combined" "$body" > "$combined.next"
    mv "$combined.next" "$combined"

    local next_url
    next_url=$(awk '{
      count = split($0, links, ",")
      for (i = 1; i <= count; i++) {
        if (tolower(links[i]) ~ /rel="next"/) {
          sub(/^[^<]*</, "", links[i])
          sub(/>.*/, "", links[i])
          print links[i]
          exit
        }
      }
    }' "$headers")
    if [[ -z $next_url ]]; then
      jq -n --slurpfile alerts "$combined" \
        '$alerts[0] | {ok: true, alerts: walk(if type == "object" then del(.secret, .raw, .match) else . end)}' > "$result"
      return
    fi
    if ((page == 5)); then
      jq -n '{ok: false, alerts: []}' > "$result"
      return
    fi
    url=$next_url
    ((page += 1))
  done
}

cd "$GITHUB_WORKSPACE"

root_manifest=$(manifest_present composer.json)
lock_json composer.lock "$ACTION_WORK_DIR/root-lock.json"

prod_manifest=false
printf null > "$ACTION_WORK_DIR/prod-lock.json"
if [[ -n $COMPOSER_PROD_DIR ]]; then
  prod_manifest=$(manifest_present "$COMPOSER_PROD_DIR/composer.json")
  lock_json "$COMPOSER_PROD_DIR/composer.lock" "$ACTION_WORK_DIR/prod-lock.json"
fi

npm_manifest=$(manifest_present package.json)
npm_lock_json package-lock.json "$ACTION_WORK_DIR/npm-lock.json"

collect_alerts dependabot "$ACTION_WORK_DIR/dependabot.json"
collect_alerts secret-scanning "$ACTION_WORK_DIR/secret-scanning.json"
collect_alerts code-scanning "$ACTION_WORK_DIR/code-scanning.json"

jq -n \
  --arg composer_prod_dir "$COMPOSER_PROD_DIR" \
  --argjson root_manifest "$root_manifest" \
  --slurpfile root_lock "$ACTION_WORK_DIR/root-lock.json" \
  --argjson prod_manifest "$prod_manifest" \
  --slurpfile prod_lock "$ACTION_WORK_DIR/prod-lock.json" \
  --argjson npm_manifest "$npm_manifest" \
  --slurpfile npm_lock "$ACTION_WORK_DIR/npm-lock.json" \
  --slurpfile dependabot "$ACTION_WORK_DIR/dependabot.json" \
  --slurpfile secret_scanning "$ACTION_WORK_DIR/secret-scanning.json" \
  --slurpfile code_scanning "$ACTION_WORK_DIR/code-scanning.json" \
  '{
    composer_prod_dir: $composer_prod_dir,
    composer: {
      root_manifest: $root_manifest,
      root_lock: $root_lock[0],
      prod_manifest: $prod_manifest,
      prod_lock: $prod_lock[0]
    },
    npm: {manifest: $npm_manifest, lock: $npm_lock[0]},
    alerts: {
      dependabot: $dependabot[0],
      "secret-scanning": $secret_scanning[0],
      "code-scanning": $code_scanning[0]
    }
  }' > "$ACTION_WORK_DIR/request.json"

dashboard_url=${DASHBOARD_URL%/}
status=$(curl --silent --output "$ACTION_WORK_DIR/dashboard-response" --write-out '%{http_code}' \
  --request POST \
  --user "$USERNAME:$APP_PASSWORD" \
  --header 'Content-Type: application/json' \
  --data-binary "@$ACTION_WORK_DIR/request.json" \
  "${dashboard_url}/wp-json/vps-audit/v1/dependencies/${ENTITY_SLUG}") || {
    echo 'Dashboard request failed.' >&2
    exit 1
  }

if [[ $status != 2?? ]]; then
  echo "Dashboard returned HTTP $status." >&2
  exit 1
fi
