#!/usr/bin/env bash

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/env-init.sh"

# If the legacy dir "cache" exists, move its content to $CACHE_PATH and remove it.
if [[ -d "cache" ]]; then
    mv cache/* $CACHE_PATH
    rm -rf cache
fi

# Create empty cache files if not exists.
[[ -d $CACHE_PATH ]] || mkdir -p $CACHE_PATH
[[ -d $CACHE_PATH/.npm/_cacache ]] || mkdir -p $CACHE_PATH/.npm/_cacache
[[ -d $CACHE_PATH/.npm/_logs ]] || mkdir -p $CACHE_PATH/.npm/_logs
[[ -d $CACHE_PATH/.composer/cache ]] || mkdir -p $CACHE_PATH/.composer/cache
[[ -d $CACHE_PATH/.oh-my-zsh/log ]] || mkdir -p $CACHE_PATH/.oh-my-zsh/log
[[ -d $CACHE_PATH/.git ]] || mkdir -p $CACHE_PATH/.git
# Docker bind-mount directories for test containers must be pre-created by the host user
# so containers (mariadb, wordpress) get correct ownership on first start.
[[ -d $CACHE_PATH/db_test ]] || mkdir -p $CACHE_PATH/db_test
[[ -d $CACHE_PATH/logs/db_test ]] || mkdir -p $CACHE_PATH/logs/db_test
# wp_test needs wp-content/mu-plugins to exist before the container starts so
# Docker can bind-mount individual plugin files into that directory.
[[ -d $CACHE_PATH/wp_test/wp-content/mu-plugins ]] || mkdir -p $CACHE_PATH/wp_test/wp-content/mu-plugins
[[ -f $CACHE_PATH/.zsh_history ]] || touch $CACHE_PATH/.zsh_history
[[ -f $CACHE_PATH/.bash_history ]] || touch $CACHE_PATH/.bash_history
[[ -f $CACHE_PATH/.composer/auth.json ]] || echo '{}' > $CACHE_PATH/.composer/auth.json
[[ -f $CACHE_PATH/.git/config ]] || touch $CACHE_PATH/.git/config
