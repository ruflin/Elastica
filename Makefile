#/bin/bash

.PHONY: init prepare update clean build setup start stop destroy run checkstyle checkstyle-ci code-browser cpd messdetector messdetector-ci dependencies phpunit test doc lint syntax-check loc phploc gource 

SOURCE = "./lib"
IMAGE = elastica

DOCKER = docker run -v $(shell pwd):/app ruflin/${IMAGE}
DOCKER_ENV = docker-compose run ${IMAGE}


### Setups around project sources. These commands should run ###
init: prepare
	${DOCKER} composer install

prepare:
	${DOCKER} mkdir -p ./build/api
	${DOCKER} mkdir -p ./build/code-browser
	${DOCKER} mkdir -p ./build/coverage
	${DOCKER} mkdir -p ./build/logs
	${DOCKER} mkdir -p ./build/docs
	${DOCKER} mkdir -p ./build/pdepend

update: init

clean:
	${DOCKER} rm -r -f ./build

# Runs commands inside virtual environemnt. Example usage inside docker: make run RUN="make phpunit"
run:
	${DOCKER_ENV} $(RUN)

### Quality checks / development tools ###

checkstyle:
	${DOCKER} phpcs --standard=PSR2 ${SOURCE}

checkstyle-ci: prepare
	${DOCKER} phpcs --report=checkstyle --report-file=./build/logs/checkstyle.xml --standard=PSR2 ${SOURCE} > /dev/null

code-browser: prepare
	${DOCKER} phpcb --log ./build/logs --source ${SOURCE} --output ./build/code-browser

# Copy paste detector
cpd: prepare
	${DOCKER} phpcpd --log-pmd ./build/logs/pmd-cpd.xml ${SOURCE}

messdetector: prepare
	${DOCKER} phpmd ${SOURCE} text codesize,unusedcode,naming,design ./build/phpmd.xml

messdetector-ci: prepare
	${DOCKER} phpmd ${SOURCE} xml codesize,unusedcode,naming,design --reportfile ./build/logs/pmd.xml

dependencies: prepare
	${DOCKER} pdepend --jdepend-xml=./build/logs/jdepend.xml \
		--jdepend-chart=./build/pdepend/dependencies.svg \
		--overview-pyramid=./build/pdepend/overview-pyramid.svg \
		${SOURCE}

phpunit: prepare
	${DOCKER_ENV} phpunit -c test/ --coverage-clover build/coverage/unit-coverage.xml --group unit
	${DOCKER_ENV} phpunit -c test/ --coverage-clover build/coverage/functional-coverage.xml --group functional
	${DOCKER_ENV} phpunit -c test/ --coverage-clover build/coverage/shutdown-coverage.xml --group shutdown
	
# Makes it easy to run a single test file. Example to run IndexTest.php: make test TEST="IndexTest.php"
test:
	${DOCKER_ENV} phpunit -c test/ test/lib/Elastica/Test/${TEST}

doc: prepare
	${DOCKER} phpdoc run -d lib/ -t build/docs

# Uses the preconfigured standards in .php_cs
lint:
	${DOCKER} php-cs-fixer fix

syntax-check:
	${DOCKER} php -lf ${SOURCE} **/*.php
	${DOCKER} php -lf ./test **/*.php

loc:
	${DOCKER} cloc --by-file --xml --exclude-dir=build -out=build/cloc.xml .

phploc:
	${DOCKER} phploc --log-csv ./build/logs/phploc.csv ${SOURCE}

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


# Visualise repo
gource:
	gource --log-format git \
		--seconds-per-day 0.1 \
		--title 'Elastica (https://github.com/ruflin/Elastica)' \
		--user-scale 1 \
		--max-user-speed 50
