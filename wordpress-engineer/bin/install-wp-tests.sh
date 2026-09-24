#!/bin/sh
#
# Installs the WordPress test suite. Runs INSIDE the wpcli container, where
# there is no subversion, so both pieces are fetched as tarballs:
#
#   - WordPress core        -> $WP_CORE_DIR   (from wordpress.org)
#   - tests/phpunit library -> $WP_TESTS_DIR  (from wordpress-develop)
#
# Usage: install-wp-tests.sh <db-name> <db-user> <db-pass> <db-host> <version>
#
# Everything lands in a named volume, so it is downloaded once per machine.

set -eu

DB_NAME="${1:-agronews_test}"
DB_USER="${2:-agronews}"
DB_PASS="${3:-agronews}"
DB_HOST="${4:-db}"
WP_VERSION="${5:-6.7.2}"

BASE_DIR="${WP_TESTS_BASE:-/tmp/wp-tests}"
WP_CORE_DIR="${BASE_DIR}/wordpress"
WP_TESTS_DIR="${BASE_DIR}/wordpress-tests-lib"
TMP_DIR="${BASE_DIR}/tmp"

mkdir -p "$BASE_DIR" "$TMP_DIR"

if [ -f "${WP_CORE_DIR}/wp-settings.php" ]; then
	echo "WordPress core already installed in ${WP_CORE_DIR}"
else
	echo "Downloading WordPress ${WP_VERSION}"
	curl -fsSL -o "${TMP_DIR}/wordpress.tar.gz" "https://wordpress.org/wordpress-${WP_VERSION}.tar.gz"
	mkdir -p "$WP_CORE_DIR"
	tar --strip-components=1 -zxf "${TMP_DIR}/wordpress.tar.gz" -C "$WP_CORE_DIR"
	rm -f "${TMP_DIR}/wordpress.tar.gz"
fi

if [ -f "${WP_TESTS_DIR}/includes/bootstrap.php" ]; then
	echo "Test library already installed in ${WP_TESTS_DIR}"
else
	echo "Downloading the test library for ${WP_VERSION}"
	curl -fsSL -o "${TMP_DIR}/develop.tar.gz" \
		"https://github.com/WordPress/wordpress-develop/archive/refs/tags/${WP_VERSION}.tar.gz"

	rm -rf "${TMP_DIR}/develop"
	mkdir -p "${TMP_DIR}/develop"
	tar --strip-components=1 -zxf "${TMP_DIR}/develop.tar.gz" -C "${TMP_DIR}/develop"

	mkdir -p "$WP_TESTS_DIR"
	cp -r "${TMP_DIR}/develop/tests/phpunit/includes" "${WP_TESTS_DIR}/includes"
	cp -r "${TMP_DIR}/develop/tests/phpunit/data" "${WP_TESTS_DIR}/data"
	cp "${TMP_DIR}/develop/wp-tests-config-sample.php" "${WP_TESTS_DIR}/wp-tests-config.php"

	rm -rf "${TMP_DIR}/develop" "${TMP_DIR}/develop.tar.gz"
fi

echo "Writing ${WP_TESTS_DIR}/wp-tests-config.php"
sed -i \
	-e "s:dirname( __FILE__ ) . '/src/':'${WP_CORE_DIR}/':" \
	-e "s/youremptytestdbnamehere/${DB_NAME}/" \
	-e "s/yourusernamehere/${DB_USER}/" \
	-e "s/yourpasswordhere/${DB_PASS}/" \
	-e "s|localhost|${DB_HOST}|" \
	"${WP_TESTS_DIR}/wp-tests-config.php"

# The MariaDB client is called mariadb on newer images and mysql on older
# ones, and recent clients require TLS unless told otherwise, while the MySQL
# client in CI does not know --skip-ssl. Each call tries both.
MYSQL_BIN="mysql"
if command -v mariadb >/dev/null 2>&1; then
	MYSQL_BIN="mariadb"
fi

db_query() {
	"$MYSQL_BIN" --skip-ssl --host="$DB_HOST" --user="$DB_USER" --password="$DB_PASS" -e "$1" 2>/dev/null \
		|| "$MYSQL_BIN" --host="$DB_HOST" --user="$DB_USER" --password="$DB_PASS" -e "$1"
}

echo "Waiting for ${DB_HOST} to accept connections"
attempt=1
while [ "$attempt" -le 30 ]; do
	if db_query "SELECT 1;" >/dev/null 2>&1; then
		break
	fi

	sleep 2
	attempt=$((attempt + 1))
done

# Best effort: the suite installs its own tables on every run, so a database
# that already exists is fine. This only guarantees it is there.
echo "Making sure the ${DB_NAME} schema exists"
db_query "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" \
	>/dev/null 2>&1 || echo "Could not create ${DB_NAME}; assuming it already exists."

echo "Test suite ready in ${WP_TESTS_DIR}"
