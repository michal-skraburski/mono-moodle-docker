export MOODLE_DOCKER_WWWROOT=./moodle
export CODERUNNER_REPO=../../deps/moodle-qtype_coderunner/
export CR_DEPEND_REPO=../../deps/moodle-qbehaviour_adaptive_adapted_for_coderunner/
# Choose a db server (Currently supported: pgsql, mariadb, mysql, mssql, oracle)
export MOODLE_DOCKER_DB=pgsql
export MOODLE_DOCKER_WEB_HOST=127.0.0.1

./bin/moodle-docker-grunt-coderunner "$@"
