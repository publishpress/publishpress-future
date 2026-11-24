#!/usr/bin/env bash
# Run a Codeception suite, optionally in parallel via Codeception --shard.
#
# Usage: tests/bin/run-codecept-suite.sh <Suite> <WORKERS_VAR> [codecept args...]
# Example: tests/bin/run-codecept-suite.sh Unit UNIT_WORKERS
#
# Workers come from the environment, then .env, else 1 (serial). A specific
# test path, --filter/--grep/--group, or coverage flags force serial.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$ROOT"

if [ "$#" -lt 2 ]; then
    echo "Usage: $0 <Suite> <WORKERS_VAR> [codecept args...]" >&2
    exit 2
fi

SUITE="$1"
WORKERS_VAR="$2"
shift 2

read_env_var() {
    local var="$1"
    local line

    if [ -n "${!var:-}" ]; then
        printf '%s' "${!var}"
        return
    fi

    if [ ! -f "$ROOT/.env" ]; then
        return
    fi

    line="$(grep -E "^[[:space:]]*${var}=" "$ROOT/.env" | tail -n 1 || true)"
    if [ -z "$line" ]; then
        return
    fi

    line="${line#*=}"
    line="${line%\"}"
    line="${line#\"}"
    line="${line%\'}"
    line="${line#\'}"
    printf '%s' "$line"
}

positive_int() {
    local value="$1"
    if [[ "$value" =~ ^[1-9][0-9]*$ ]]; then
        printf '%s' "$value"
        return 0
    fi
    return 1
}

needs_serial() {
    local arg
    while [ "$#" -gt 0 ]; do
        arg="$1"
        shift
        case "$arg" in
            --filter|--grep|--group|--skip-group|-g|-x)
                return 0
                ;;
            --filter=*|--grep=*|--group=*|--skip-group=*|--coverage|--coverage-*)
                return 0
                ;;
            --coverage=*|--coverage-html|--coverage-html=*|--coverage-xml|--coverage-xml=*|--coverage-text|--coverage-text=*|--coverage-crap4j|--coverage-crap4j=*|--coverage-cobertura|--coverage-cobertura=*|--coverage-phpunit|--coverage-phpunit=*)
                return 0
                ;;
            -*)
                ;;
            *)
                return 0
                ;;
        esac
    done
    return 1
}

WORKERS="$(read_env_var "$WORKERS_VAR")"
if ! WORKERS="$(positive_int "${WORKERS:-}")"; then
    WORKERS=1
fi

if [ "$WORKERS" -gt 64 ]; then
    WORKERS=64
fi

FILE_COUNT="$(find "tests/codeception/${SUITE}" -name '*Test.php' 2>/dev/null | wc -l | tr -d ' ')"
if [ "${FILE_COUNT:-0}" -gt 0 ] && [ "$WORKERS" -gt "$FILE_COUNT" ]; then
    WORKERS="$FILE_COUNT"
fi

if [ "$WORKERS" -le 1 ] || needs_serial "$@"; then
    exec vendor/bin/codecept run "$SUITE" "$@"
fi

echo "▶ ${SUITE} tests: ${WORKERS} workers (${WORKERS_VAR}, Codeception --shard)"

vendor/bin/codecept build --silent

OUT_DIR="$(mktemp -d)"
trap 'rm -rf "$OUT_DIR"' EXIT

run_shard() {
    local index="$1"
    shift
    local out="$OUT_DIR/shard-${index}"
    local rc=0

    set +e
    vendor/bin/codecept run "$SUITE" \
        --no-rebuild \
        --shard "${index}/${WORKERS}" \
        -o "paths: output: tests/codeception/_output/${SUITE}-w${index}" \
        "$@" >"$out" 2>&1
    rc=$?
    set -e
    echo "$rc" >"${out}.rc"
}

for i in $(seq 1 "$WORKERS"); do
    run_shard "$i" "$@" &
done

wait || true

final=0
for i in $(seq 1 "$WORKERS"); do
    out="$OUT_DIR/shard-${i}"
    echo
    echo "===== ${SUITE} shard ${i}/${WORKERS} ====="
    if [ -f "$out" ]; then
        cat "$out"
    fi
    rc="$(cat "${out}.rc" 2>/dev/null || echo 1)"
    if [ "$rc" -ne 0 ] && [ "$final" -eq 0 ]; then
        final="$rc"
    fi
done

if [ "$final" -eq 0 ]; then
    echo
    echo "▶ ${SUITE} tests: all ${WORKERS} shards passed"
else
    echo
    echo "▶ ${SUITE} tests: shard failure (exit ${final})" >&2
fi

exit "$final"
