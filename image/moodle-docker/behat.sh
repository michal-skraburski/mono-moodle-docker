# Change ./moodle to your /path/to/moodle if you already have it checked out
export MOODLE_DOCKER_WWWROOT=./moodle
export CODERUNNER_REPO=../../deps/moodle-qtype_coderunner/
export CR_DEPEND_REPO=../../deps/moodle-qbehaviour_adaptive_adapted_for_coderunner/
export CODERUNNER_REPO_OLD=../../deps/moodle-qtype_coderunner/
export CR_DEPEND_REPO_OLD=../../deps/moodle-qbehaviour_adaptive_adapted_for_coderunner/
# Choose a db server (Currently supported: pgsql, mariadb, mysql, mssql, oracle)
export MOODLE_DOCKER_DB=pgsql
export MOODLE_DOCKER_WEB_HOST=192.168.100.2

# bin/moodle-docker-compose exec webserver php admin/tool/behat/cli/util.php --drop
bin/moodle-docker-compose exec webserver php admin/tool/behat/cli/init.php

if [ $# -gt 0 ]; then
  bin/moodle-docker-compose exec -u www-data webserver php admin/tool/behat/cli/run.php "$@"
else
  bin/moodle-docker-compose exec -u www-data webserver php admin/tool/behat/cli/run.php --tags=@qtype_coderunner
fi
