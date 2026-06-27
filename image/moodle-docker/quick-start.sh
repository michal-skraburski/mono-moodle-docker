# Change ./moodle to your /path/to/moodle if you already have it checked out
export MOODLE_DOCKER_WWWROOT=./moodle
export CODERUNNER_REPO=../../deps/moodle-qtype_coderunner/
export CR_DEPEND_REPO=../../deps/moodle-qbehaviour_adaptive_adapted_for_coderunner/
# Choose a db server (Currently supported: pgsql, mariadb, mysql, mssql, oracle)
export MOODLE_DOCKER_DB=pgsql
export MOODLE_DOCKER_WEB_HOST=192.168.100.2

# Get Moodle code, you could select another version branch (skip this if you already got the code)
git clone -b v4.5.4 git://git.moodle.org/moodle.git $MOODLE_DOCKER_WWWROOT

# Ensure customized config.php for the Docker containers is in place
cp config.docker-template.php $MOODLE_DOCKER_WWWROOT/config.php

# Start up containers
bin/moodle-docker-compose up -d

# Wait for DB to come up (important for oracle/mssql)
bin/moodle-docker-wait-for-db

# Work with the containers (see below)
# [..]

# Shut down and destroy containers
#bin/moodle-docker-compose down
