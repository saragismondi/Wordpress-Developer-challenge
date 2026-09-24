#!/usr/bin/env bash
# Shared helpers for the bin/ scripts. Sourced, never executed directly.

set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$REPO_ROOT"

# Creates .env on first run so docker compose always has the same values the
# scripts read.
if [ ! -f "$REPO_ROOT/.env" ]; then
	cp "$REPO_ROOT/.env.example" "$REPO_ROOT/.env"
	echo "Created .env from .env.example"
fi

# shellcheck disable=SC1091
set -a
. "$REPO_ROOT/.env"
set +a

WP_PORT="${WP_PORT:-8088}"
WP_URL="${WP_URL:-http://localhost:${WP_PORT}}"
WP_TITLE="${WP_TITLE:-AgroNews}"
WP_ADMIN_USER="${WP_ADMIN_USER:-admin}"
WP_ADMIN_PASSWORD="${WP_ADMIN_PASSWORD:-admin}"
WP_ADMIN_EMAIL="${WP_ADMIN_EMAIL:-admin@agronews.example}"
DB_NAME="${DB_NAME:-agronews}"
DB_USER="${DB_USER:-agronews}"
DB_PASSWORD="${DB_PASSWORD:-agronews}"
DB_TEST_NAME="${DB_TEST_NAME:-agronews_test}"

# Prints a step header.
say() {
	printf '\n\033[1;32m==>\033[0m %s\n' "$*"
}

# Prints a warning.
warn() {
	printf '\033[1;33m[!]\033[0m %s\n' "$*" >&2
}

# docker compose, pinned to this repository.
dc() {
	docker compose --project-directory "$REPO_ROOT" "$@"
}

# Runs WP-CLI inside the wpcli service.
wpcli() {
	dc run --rm -T wpcli wp "$@"
}

# Runs an arbitrary command inside the wpcli service (PHP 8.2, mysql client).
wpsh() {
	dc run --rm -T --entrypoint sh wpcli -c "$@"
}

# Blocks until the database accepts connections.
wait_for_db() {
	local attempt=1
	local max=60

	while [ "$attempt" -le "$max" ]; do
		if dc exec -T db healthcheck.sh --connect --innodb_initialized >/dev/null 2>&1; then
			return 0
		fi

		sleep 2
		attempt=$((attempt + 1))
	done

	echo "Database did not become ready after $((max * 2))s" >&2

	return 1
}

# Blocks until Apache answers on the published port.
wait_for_http() {
	local attempt=1
	local max=60

	while [ "$attempt" -le "$max" ]; do
		if curl -fsS -o /dev/null "${WP_URL}/wp-admin/install.php" 2>/dev/null; then
			return 0
		fi

		if curl -fsS -o /dev/null "${WP_URL}/" 2>/dev/null; then
			return 0
		fi

		sleep 2
		attempt=$((attempt + 1))
	done

	echo "WordPress did not answer on ${WP_URL} after $((max * 2))s" >&2

	return 1
}
