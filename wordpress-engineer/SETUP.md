# Setup

Everything runs locally with Docker — you do **not** need PHP, Composer or Node on
your machine. The stack is:

- `wordpress` — WordPress 6.7 on PHP 8.2 + Apache (published on port 8088)
- `db` — MariaDB 10.11
- `edge` — edge cache simulation
- `wpcli` — WP-CLI, used by the scripts (it is not a long-running service)

## Run it

```bash
make setup     # containers up, WordPress installed, theme and plugin active
make seed      # 12 sections, 40 topics, 4 users, 2000 stories with images
make bench     # measure the home page
```

The first `make seed` takes a couple of minutes: it draws a featured image for
every story with GD. Nothing is downloaded — the seeder works offline.

Then open the site:

- Portal: <http://localhost:8088>
- Admin: <http://localhost:8088/wp-admin> with `admin` / `admin`

## Measuring

`make bench` hits the home page 10 times after two warm-up requests and reports the
TTFB median and p95, the query count, the peak memory and the response size:

```
  TTFB median        1670.6 ms
  TTFB p95           1688.6 ms
  Queries            264
  Query time         41.52 ms
  Peak memory        6 MB
  Response size      61 KB
```

The query count and the memory come from a mu-plugin (`wp-content/mu-plugins/an-bench.php`)
that turns on `SAVEQUERIES` and returns the numbers as response headers, so you can
measure any URL without parsing HTML:

```bash
curl -s -o /dev/null -D - http://localhost:8088/ | grep -i '^x-an'
```

```
X-AN-Queries: 264
X-AN-Query-Time: 41.52
X-AN-Memory: 6
X-AN-Savequeries: 1
X-AN-Template: front-page.php
```

`bin/bench.sh` takes a URL and a number of runs, so you can measure other pages too:

```bash
./bin/bench.sh http://localhost:8088/category/granos/ 20
```

The helper is local-only: it is controlled by `AN_BENCH` in `.env` and exposes
internals, so it would never ship enabled to production.

## Other commands

```bash
make test      # PHPUnit, inside the container
make lint      # PHP_CodeSniffer: WordPress-Extra + PHPCompatibilityWP
make fix       # what phpcbf can fix on its own
make logs      # follow the Apache/PHP log
make shell     # a shell inside the WordPress container
make down      # stop the containers
make clean     # wipe everything, database and uploads included
```

`make test` downloads the WordPress test library the first time and caches it in a
Docker volume. `make lint` and `make test` install the Composer dependencies on
first use, also inside a container.

## What ships

```
wordpress-engineer/
├── README.md              # the challenge brief — read this first
├── SETUP.md               # this file
├── Makefile               # setup / seed / bench / test / lint / clean
├── docker-compose.yml     # wordpress + mariadb + wp-cli
├── .env.example           # ports and local credentials (copied to .env on first run)
├── composer.json          # phpcs, wpcs, phpunit, polyfills
├── phpcs.xml              # WordPress-Extra + PHPCompatibilityWP, testVersion 8.2-
├── phpunit.xml.dist       # test suite configuration
├── bin/
│   ├── setup.sh           # brings the stack up and installs WordPress
│   ├── seed.sh            # loads the demo content
│   ├── bench.sh           # measures the home page
│   ├── test.sh            # runs PHPUnit in the container
│   ├── lint.sh            # runs phpcs / phpcbf in a container
│   ├── install-wp-tests.sh
│   ├── lib.sh             # shared helpers for the scripts
│   └── seed/              # the seeder itself, in PHP, run through WP-CLI
├── docker/                # Apache, PHP and edge config, and the test database schema
└── wp-content/
    ├── themes/agronews/          # the portal theme, classic, no build step
    ├── plugins/agronews-home/    # home settings + the PHPUnit suite
    └── mu-plugins/an-bench.php   # the bench helper
```

WordPress core is **not** in the repository: it comes from the container image, and
only `wp-content` is mounted. The database and the uploads live in Docker volumes,
so `make clean` gives you a clean slate.

A `.github/` folder ships with a CI workflow and a pull request checklist. GitHub
only runs workflows from the repository root, so move `.github/` up one level if you
want the CI to run on your own repo.

## Environment

`bin/setup.sh` creates `.env` from `.env.example` on the first run. Change the port
there if 8088 is taken on your machine, then run `make clean && make setup`.

## Deliverable

Push your solution to the repository we shared with you, on `main` or on a branch,
and make sure `make setup && make seed` works from a clean checkout. Include your
technical write-up and your AI usage log.
