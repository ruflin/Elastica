#/bin/bash

.PHONY: init prepare update clean build setup start stop destroy run checkstyle checkstyle-ci code-browser cpd messdetector messdetector-ci dependencies phpunit test doc lint syntax-check loc phploc gource 

SOURCE = "./lib"

### Setups around project sources. These commands should run ###
init: prepare
	composer install --prefer-source

prepare:
	mkdir -p ./build/api
	mkdir -p ./build/code-browser
	mkdir -p ./build/coverage
	mkdir -p ./build/logs
	mkdir -p ./build/docs
	mkdir -p ./build/pdepend

update: init

clean:
	rm -r -f ./build

# Runs commands inside virtual environemnt. Example usage inside docker: make run RUN="make phpunit"
run:
	docker-compose run elastica $(RUN)

### Quality checks / development tools ###

code-browser:
	phpcb --log ./build/logs --source ${SOURCE} --output ./build/code-browser

# Copy paste detector
cpd:
	phpcpd --log-pmd ./build/logs/pmd-cpd.xml ${SOURCE}

messdetector:
	phpmd ${SOURCE} text codesize,unusedcode,naming,design ./build/phpmd.xml

messdetector-ci:
	phpmd ${SOURCE} xml codesize,unusedcode,naming,design --reportfile ./build/logs/pmd.xml

dependencies:
	pdepend --jdepend-xml=./build/logs/jdepend.xml \
		--jdepend-chart=./build/pdepend/dependencies.svg \
		--overview-pyramid=./build/pdepend/overview-pyramid.svg \
		${SOURCE}

phpunit: prepare
	-phpunit -c test/ --coverage-clover build/coverage/unit-coverage.xml --group unit
	-phpunit -c test/ --coverage-clover build/coverage/functional-coverage.xml --group functional
	-phpunit -c test/ --coverage-clover build/coverage/shutdown-coverage.xml --group shutdown
	
# Makes it easy to run a single test file. Example to run IndexTest.php: make test TEST="IndexTest.php"
test:
	${DOCKER} phpunit -c test/ test/lib/Elastica/Test/${TEST}

doc: prepare
	phpdoc run -d lib/ -t build/docs

# Uses the preconfigured standards in .php_cs
lint:
	php-cs-fixer fix

loc: 
	cloc --by-file --xml --exclude-dir=build -out=build/cloc.xml .

phploc:
	phploc --log-csv ./build/logs/phploc.csv ${SOURCE}

# Handling virtual environment

build:
	docker-compose build

setup: build
	docker-compose scale elasticsearch=3
	# TODO: Makes the snapshot directory writable for all instances. Nicer solution needed.
	docker-compose run elasticsearch chmod -R 777 /mount/

start:
	docker-compose up

stop:
	docker-compose stop

destroy: clean
	docker-compose kill
	docker-compose rm
	
# Starts a shell inside the elastica image
shell:
	docker run -v $(shell pwd):/elastica -ti ruflin/elastica /bin/bash

# Starts a shell inside the elastica image with the full environment running
env-shell:
	docker-compose run elastica /bin/bash

# Visualise repo
gource:
	gource --log-format git \
		--seconds-per-day 0.1 \
		--title 'Elastica (https://github.com/ruflin/Elastica)' \
		--user-scale 1 \
		--max-user-speed 50

google-setup:
	docker-machine create --driver google --google-project elastica-1024 --google-machine-type n1-standard-8 elastica
	eval "$(docker-machine env elastica)"