#!/usr/bin/env bash
# Browser check: opens every page in headless Chrome (school_test database),
# reports JavaScript errors / failed requests, then runs the interaction checks.
#   tests/browser/run.sh
set -euo pipefail
HERE="$(cd "$(dirname "$0")" && pwd)"
HOST_APP="${SM_HOST_APP:-$(cd "$HERE/../../../app" && pwd)}"
TMP="$(mktemp -d)"

DB_DATABASE=school_test php "$HERE/urls.php" > "$TMP/urls.json"

cd "$HOST_APP/public"
DB_DATABASE=school_test APP_URL=http://127.0.0.1:8123 php -S 127.0.0.1:8123 \
    ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php > "$TMP/server.log" 2>&1 &
SERVER=$!
trap 'kill $SERVER 2>/dev/null; rm -rf "$TMP"' EXIT
sleep 1

node --experimental-websocket "$HERE/browse.mjs" "$TMP/urls.json" "$HERE/checks.mjs" 2>&1 | grep -v ExperimentalWarning
