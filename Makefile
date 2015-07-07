#/bin/bash

BASEDIR = $(shell pwd)
SOURCE = "${BASEDIR}/lib"
IMAGE = "elastica"


### Setups around project sources. These commands should run ###
init: prepare
	composer install

prepare:
	mkdir -p ${BASEDIR}/build/api
	mkdir -p ${BASEDIR}/build/code-browser
	mkdir -p ${BASEDIR}/build/coverage
	mkdir -p ${BASEDIR}/build/logs
	mkdir -p ${BASEDIR}/build/docs
	mkdir -p ${BASEDIR}/build/pdepend

update: init

clean:
	rm -r -f ${BASEDIR}/build
	#rm ${BASEDIR}/cache.properties


# Handling virtual environment

build:
	docker-compose build

setup: build
	docker-compose scale elasticsearch=3

start:
	docker-compose up

stop:
	docker-compose stop

destroy: clean
	docker-compose kill
	docker-compose rm

# Runs commands inside virtual environemnt. Example usage inside docker: make run RUN="make phpunit"
run:
	docker-compose run elastica $(RUN)


### Quality checks / development tools ###

checkstyle:
	phpcs --standard=PSR2 ${SOURCE}

checkstyle-ci: prepare
	phpcs --report=checkstyle --report-file=${BASEDIR}/build/logs/checkstyle.xml --standard=PSR2 ${SOURCE} > /dev/null

code-browser: prepare
	phpcb --log ${BASEDIR}/build/logs --source ${SOURCE} --output ${BASEDIR}/build/code-browser

# Copy paste detector
cpd: prepare
	phpcpd --log-pmd ${BASEDIR}/build/logs/pmd-cpd.xml ${SOURCE}

messdetector: prepare
	phpmd ${SOURCE} text codesize,unusedcode,naming,design ${BASEDIR}/build/phpmd.xml

messdetector-ci: prepare
	phpmd ${SOURCE} xml codesize,unusedcode,naming,design --reportfile ${BASEDIR}/build/logs/pmd.xml

dependencies: prepare
	pdepend --jdepend-xml=${BASEDIR}/build/logs/jdepend.xml \
		--jdepend-chart=${BASEDIR}/build/pdepend/dependencies.svg \
		--overview-pyramid=${BASEDIR}/build/pdepend/overview-pyramid.svg \
		${SOURCE}

phpunit: prepare
	phpunit -c test/ --coverage-clover build/coverage/unit-coverage.xml --group unit
	phpunit -c test/ --coverage-clover build/coverage/functional-coverage.xml --group functional
	phpunit -c test/ --coverage-clover build/coverage/shutdown-coverage.xml --group shutdown

doc: prepare
	phpdoc run -d lib/ -t build/docs

# Uses the preconfigured standards in .php_cs
lint:
	php-cs-fixer fix

syntax-check:
	php -lf ${SOURCE} **/*.php
	php -lf ${BASEDIR}/test **/*.php


loc:
	cloc --by-file --xml --exclude-dir=build -out=build/cloc.xml .

phploc:
	phploc --log-csv $(BASEDIR)/build/logs/phploc.csv $(SOURCE)



# Visualise repo
gource:
	gource --log-format git \
		--seconds-per-day 0.1 \
		--title 'Elastica (https://github.com/ruflin/Elastica)' \
		--user-scale 1 \
		--max-user-speed 50
