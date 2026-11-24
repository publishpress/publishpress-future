#!/usr/bin/env bash
# Benchmarks cloning a WordPress test database inside the db_test container.
#
# Builds pw_template (fresh WordPress install, Future + Classic Editor active),
# then times three clone methods, sequentially and as a concurrent burst:
#   dump-pipe  mariadb-dump pw_template | mariadb <clone>
#   dump-file  mariadb <clone> < pre-made dump of pw_template
#   sql-copy   CREATE TABLE ... LIKE + INSERT ... SELECT, all server side
#
# Usage: tests/bin/bench-db-clone.sh [iterations=10] [burst=14]
# Leaves the existing "wordpress" database untouched. Drops pw_bench_* when done.

set -euo pipefail

ITERATIONS="${1:-10}"
BURST="${2:-14}"
BASE="${CONTAINER_NAME:-publishpress-future}"
DB_CONTAINER="${BASE}_env_db_test"
CLI_CONTAINER="$(docker ps --filter "name=${BASE}_env_wp_test_cli" --format '{{.Names}}' | head -n1)"
TEMPLATE="pw_template"
DUMP_FILE="/tmp/${TEMPLATE}.sql"

if [ -z "$CLI_CONTAINER" ]; then
    echo "wp-cli test container is not running." >&2
    exit 1
fi

sql() {
    docker exec -i "$DB_CONTAINER" mariadb -uroot -proot "$@"
}

wp_on() {
    local db="$1"
    shift
    docker exec \
        -e WORDPRESS_DB_NAME="$db" \
        -e WORDPRESS_DB_USER=root \
        -e WORDPRESS_DB_PASSWORD=root \
        "$CLI_CONTAINER" wp --path=/var/www/html "$@"
}

now_ms() {
    echo $(( $(date +%s%N) / 1000000 ))
}

clone_dump_pipe() {
    docker exec "$DB_CONTAINER" sh -c \
        "mariadb -uroot -proot -e 'DROP DATABASE IF EXISTS \`$1\`; CREATE DATABASE \`$1\`' \
        && mariadb-dump -uroot -proot --single-transaction --skip-lock-tables $TEMPLATE | mariadb -uroot -proot $1"
}

clone_dump_file() {
    docker exec "$DB_CONTAINER" sh -c \
        "mariadb -uroot -proot -e 'DROP DATABASE IF EXISTS \`$1\`; CREATE DATABASE \`$1\`' \
        && mariadb -uroot -proot $1 < $DUMP_FILE"
}

clone_sql_copy() {
    {
        echo "DROP DATABASE IF EXISTS \`$1\`; CREATE DATABASE \`$1\`;"
        for table in $TABLES; do
            echo "CREATE TABLE \`$1\`.\`$table\` LIKE \`$TEMPLATE\`.\`$table\`;"
            echo "INSERT INTO \`$1\`.\`$table\` SELECT * FROM \`$TEMPLATE\`.\`$table\`;"
        done
    } | sql
}

verify_clone() {
    local count
    count="$(sql -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$1'")"
    if [ "$count" != "$TABLE_COUNT" ]; then
        echo "  ✗ $1 has $count tables, expected $TABLE_COUNT" >&2
        return 1
    fi
}

stats() {
    sort -n | awk '{ a[NR] = $1; s += $1 } END {
        printf "min %5d  median %5d  avg %5d  max %5d ms\n", a[1], a[int((NR + 1) / 2)], s / NR, a[NR]
    }'
}

echo "== Building $TEMPLATE"
start=$(now_ms)
sql -e "DROP DATABASE IF EXISTS $TEMPLATE; CREATE DATABASE $TEMPLATE;"
wp_on "$TEMPLATE" core install --url=http://localhost:8011 --title=Template \
    --admin_user=admin --admin_password=admin --admin_email=test@example.com --skip-email >/dev/null
wp_on "$TEMPLATE" plugin activate post-expirator classic-editor >/dev/null
wp_on "$TEMPLATE" option update admin_email_lifespan 2060442225 >/dev/null
echo "   built in $(( $(now_ms) - start )) ms"

TABLES="$(sql -N -e "SELECT table_name FROM information_schema.tables WHERE table_schema='$TEMPLATE'")"
TABLE_COUNT="$(echo "$TABLES" | wc -l)"
SIZE_KB="$(sql -N -e "SELECT ROUND(SUM(data_length + index_length) / 1024) FROM information_schema.tables WHERE table_schema='$TEMPLATE'")"
echo "   $TABLE_COUNT tables, ~${SIZE_KB} KB"

start=$(now_ms)
docker exec "$DB_CONTAINER" sh -c "mariadb-dump -uroot -proot --single-transaction --skip-lock-tables $TEMPLATE > $DUMP_FILE"
echo "   dump file written in $(( $(now_ms) - start )) ms ($(docker exec "$DB_CONTAINER" du -k "$DUMP_FILE" | cut -f1) KB)"

for method in dump_pipe dump_file sql_copy; do
    echo
    echo "== $method"

    times=()
    for i in $(seq 1 "$ITERATIONS"); do
        start=$(now_ms)
        "clone_$method" "pw_bench_seq"
        times+=($(( $(now_ms) - start )))
        verify_clone "pw_bench_seq"
    done
    printf "   sequential x%-3s %s" "$ITERATIONS" "$(printf '%s\n' "${times[@]}" | stats)"
    echo

    start=$(now_ms)
    pids=()
    for i in $(seq -w 1 "$BURST"); do
        "clone_$method" "pw_bench_$i" &
        pids+=($!)
    done
    for pid in "${pids[@]}"; do
        wait "$pid"
    done
    echo "   burst of $BURST concurrent clones: $(( $(now_ms) - start )) ms wall"
    for i in $(seq -w 1 "$BURST"); do
        verify_clone "pw_bench_$i"
    done
done

echo
echo "== Sanity check: WordPress boots on a clone"
wp_on pw_bench_01 core is-installed && echo "   core installed"
echo "   active plugins: $(wp_on pw_bench_01 plugin list --status=active --field=name | tr '\n' ' ')"

echo
echo "== Cleanup"
drop="DROP DATABASE IF EXISTS pw_bench_seq;"
for i in $(seq -w 1 "$BURST"); do
    drop="$drop DROP DATABASE IF EXISTS pw_bench_$i;"
done
sql -e "$drop"
docker exec "$DB_CONTAINER" rm -f "$DUMP_FILE"
echo "   dropped pw_bench_* (kept $TEMPLATE)"
