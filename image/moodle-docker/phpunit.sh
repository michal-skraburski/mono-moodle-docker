# Change ./moodle to your /path/to/moodle if you already have it checked out
export MOODLE_DOCKER_WWWROOT=./moodle
export CODERUNNER_REPO=../../deps/moodle-qtype_coderunner/
export CR_DEPEND_REPO=../../deps/moodle-qbehaviour_adaptive_adapted_for_coderunner/
# Choose a db server (Currently supported: pgsql, mariadb, mysql, mssql, oracle)
export MOODLE_DOCKER_DB=pgsql
export MOODLE_DOCKER_WEB_HOST=192.168.100.2

bin/moodle-docker-compose exec webserver php admin/tool/phpunit/cli/init.php

bin/moodle-docker-compose exec webserver vendor/bin/phpunit --verbose question/type/coderunner/tests/docs_page_test.php
