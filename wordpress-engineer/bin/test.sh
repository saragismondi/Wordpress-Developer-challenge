#!/usr/bin/env bash
#
# Runs the PHPUnit suite inside the container: same PHP 8.2 and same MariaDB
# the site runs on. Installs the Composer dependencies and the WordPress test
# library the first time.
#
# Any extra argument is passed straight to PHPUnit:
#   bin/test.sh --filter test_featured_block_renders_five_stories

# shellcheck source=bin/lib.sh
. "$(dirname "${BASH_SOURCE[0]}")/lib.sh"

WP_VERSION="${WP_VERSION:-6.7.2}"

say "Starting the database"
dc up -d db
wait_for_db

if [ ! -f "$REPO_ROOT/vendor/autoload.php" ]; then
	say "Installing the Composer dependencies"
	docker run --rm \
		-u "$(id -u):$(id -g)" \
		-e COMPOSER_HOME=/tmp/composer \
		-v "$REPO_ROOT":/app \
		-w /app \
		composer:2 install --no-interaction --no-progress
fi

# Docker creates the named volume owned by root, while the container runs as
# uid 33 (www-data). This hands it over once, on first use.
dc run --rm -T --user 0:0 --entrypoint sh wpcli \
	-c 'mkdir -p /tmp/wp-tests && chown -R 33:33 /tmp/wp-tests' >/dev/null

say "Installing the WordPress test library (cached in a volume)"
wpsh "sh /repo/bin/install-wp-tests.sh '$DB_TEST_NAME' '$DB_USER' '$DB_PASSWORD' db '$WP_VERSION'"

say "Running PHPUnit"
dc run --rm -T \
	-e WP_TESTS_DIR=/tmp/wp-tests/wordpress-tests-lib \
	-e WP_TESTS_PHPUNIT_POLYFILLS_PATH=/repo/vendor/yoast/phpunit-polyfills \
	-w /repo \
	--entrypoint php \
	wpcli /repo/vendor/bin/phpunit "$@"
