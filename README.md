# Improving CodeRunner

A Docker-based Moodle environment for developing and testing an enhanced fork of the
**CodeRunner** question-type plugin (final-year dissertation project). It bundles the
plugin, its companion question behaviour, a containerised Moodle + Jobe sandbox, and the
scripts to build, run and test the whole stack.

## What the fork adds

On top of upstream [CodeRunner](https://github.com/trampgeek/moodle-qtype_coderunner):

- **In-plugin documentation** (`docs.php`) linked from the question-editing form, including a
  browsable list of example questions and walkthroughs.
- **One-click example import** (`import_example.php`) — import any documented example question
  straight into a course's question bank.
- **Layout switcher** on question-attempt pages — toggle stacked / side-by-side, drag the
  divider to resize, and collapse the question-info panel (choice persisted per question).
- **Per-test-case delete button** in the author form.

## Repository layout

```
deps/
  moodle-qtype_coderunner/                      the enhanced CodeRunner plugin (main work)
  moodle-qbehaviour_adaptive_adapted_for_coderunner/   required companion behaviour
image/
  moodle-docker/                                moodlehq/moodle-docker + project scripts
docs/                                           dissertation report (LaTeX) and figures
survey/                                         user-survey data and analysis scripts
```

The two `deps/` plugins are **volume-mounted** into the Moodle container (not copied), so edits
on the host are live inside the running site.

## Prerequisites

- Docker Engine with **Docker Compose v2** (`docker compose ...`)
- `git`
- ~3 GB free disk for the Moodle checkout, images and DB volume

## Environment variables

Every script sets these; export them in your shell too if you want to run `bin/moodle-docker-*`
commands directly. Run all commands from **`image/moodle-docker/`**.

```sh
cd image/moodle-docker
export MOODLE_DOCKER_WWWROOT=./moodle                                        # Moodle core checkout
export CODERUNNER_REPO=../../deps/moodle-qtype_coderunner/
export CR_DEPEND_REPO=../../deps/moodle-qbehaviour_adaptive_adapted_for_coderunner/
export MOODLE_DOCKER_DB=pgsql                                                # pgsql | mariadb | mysql | mssql | oracle
export MOODLE_DOCKER_WEB_HOST=127.0.0.1                                      # the scripts default to 192.168.100.2
```

> Tip: exports set inside a script don't survive back to your shell. To keep them, `source` the
> script (`source ./quick-start.sh`) instead of executing it, or just paste the block above.

## Deploy

From `image/moodle-docker/`:

```sh
# 1. Get Moodle core (v4.5.4) into ./moodle — skip if already checked out.
#    If git:// is blocked on your network, use https://github.com/moodle/moodle.git
git clone -b v4.5.4 https://github.com/moodle/moodle.git ./moodle

# 2. Drop in the container config.
cp config.docker-template.php ./moodle/config.php

# 3. Start the stack (Moodle + DB + Jobe sandbox + Selenium).
bin/moodle-docker-compose up -d
bin/moodle-docker-wait-for-db

# 4. Install the Moodle database (admin / test).
bin/moodle-docker-compose exec webserver \
  php admin/cli/install_database.php --agree-license \
  --fullname="CodeRunner Dev" --shortname=crdev \
  --adminpass=test --adminemail=admin@example.com
```

`quick-start.sh` runs steps 1–3 for you (it uses `git://` and `MOODLE_DOCKER_WEB_HOST=192.168.100.2`;
edit it if either doesn't suit your setup).

### Access

- Web: `http://127.0.0.1:8000` (or `http://<MOODLE_DOCKER_WEB_HOST>:8000`)
- Login: **admin** / **test**
- Mail (Mailpit): `http://127.0.0.1:8000/_/mail`
- DB: database `moodle`, user `moodle`, password `m@0dl3ing`

The two plugins are mounted before install, so they're picked up automatically. If you add or
upgrade a plugin later, visit **Site administration → Notifications** to run the upgrade.

### Point CodeRunner at the local Jobe sandbox

CodeRunner runs student code on a Jobe server; the stack includes one (`trampgeek/jobeinabox`,
container `docker_moodle-coderunner`, host port `4000`). Configure the plugin to use it:

**Site administration → Plugins → Question types → CodeRunner**, set the **Jobe server**
(`jobe_host`) to `docker_moodle-coderunner` (reachable on the compose network). CodeRunner
questions will then execute against the local box.

## Tests

Run from `image/moodle-docker/`. Both scripts (re)initialise the test environment first.

```sh
# Behat — defaults to the @qtype_coderunner suite; pass args to narrow it.
bash behat.sh
bash behat.sh --tags=@layoutswitchertest
bash behat.sh --tags=@docsnavigationtest

# PHPUnit — the script targets docs_page_test.php; run others manually:
bin/moodle-docker-compose exec webserver php admin/tool/phpunit/cli/init.php
bin/moodle-docker-compose exec webserver \
  vendor/bin/phpunit question/type/coderunner/tests/questiontype_test.php
```

Relevant test files added by this project:
`tests/docs_page_test.php`, `tests/import_example_test.php`, `tests/questiontype_test.php`,
and Behat features `docs_navigation.feature`, `authorform_docs_link.feature`,
`layoutswitcher.feature`.

## Rebuilding the AMD JavaScript

After editing anything under `deps/moodle-qtype_coderunner/amd/src/`, recompile the minified
bundles (uses a throwaway `node:22` container — no local Node needed):

```sh
bash compile-amd-coderunner.sh          # default task: amd
# equivalently:
bin/moodle-docker-grunt-coderunner amd
```

## Everyday commands

```sh
bin/moodle-docker-compose logs -f webserver                              # tail logs
bin/moodle-docker-compose exec webserver php admin/cli/purge_caches.php  # after CSS/lang/template edits
bin/moodle-docker-compose exec webserver bash                           # shell in the container
bin/moodle-docker-compose down                                          # stop (keep data)
bin/moodle-docker-compose down -v                                       # stop and wipe the DB volume
```

> Purge caches after changing `styles.css`, language strings or Mustache templates — Moodle
> serves cached copies otherwise.

## Survey analysis (optional)

The `survey/` directory holds the user-study data and the plotting scripts used in the report:

```sh
python -m venv .venv && source .venv/bin/activate
pip install -r survey/requirements.txt
# scripts live in survey/src/; outputs are written to survey/out/
```

## Notes

- Always run the scripts from `image/moodle-docker/` — they use paths relative to it
  (`./moodle`, `../../deps/...`).
- Default PHP is 8.2; override with `MOODLE_DOCKER_PHP_VERSION`.
- The upstream harness is documented in `image/moodle-docker/README.md`.
