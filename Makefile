#/bin/bash

.PHONY: update clean build setup start stop destroy run checkstyle checkstyle-ci code-browser cpd messdetector messdetector-ci dependencies phpunit test tests doc lint syntax-check loc phploc gource 

SOURCE = "./lib"

# By default docker environment is used to run commands. To run without the predefined environment, set RUN_ENV=" " either as parameter or as environment variable
ifndef RUN_ENV
	RUN_ENV = docker run -v $(shell pwd):/elastica ruflin/elastica
endif

clean:
	rm -r -f ./build
	rm -r -f ./vendor
	rm -r -f ./composer.lock

# Runs commands inside virtual environemnt. Example usage inside docker: make run RUN="make phpunit"
run:
	docker-compose run elastica $(RUN)

### Quality checks / development tools ###

code-browser:
	${RUN_ENV} phpcb --log ./build/logs --source ${SOURCE} --output ./build/code-browser

# Copy paste detector
cpd:
	${RUN_ENV} phpcpd --log-pmd ./build/logs/pmd-cpd.xml ${SOURCE}

messdetector:
	${RUN_ENV} phpmd ${SOURCE} text codesize,unusedcode,naming,design ./build/phpmd.xml

messdetector-ci:
	${RUN_ENV} phpmd ${SOURCE} xml codesize,unusedcode,naming,design --reportfile ./build/logs/pmd.xml

dependencies:
	${RUN_ENV} pdepend --jdepend-xml=./build/logs/jdepend.xml \
		--jdepend-chart=./build/pdepend/dependencies.svg \
		--overview-pyramid=./build/pdepend/overview-pyramid.svg \
		${SOURCE}

phpunit:
	-phpunit -c test/ --coverage-clover build/coverage/unit-coverage.xml --group unit
	-phpunit -c test/ --coverage-clover build/coverage/functional-coverage.xml --group functional
	-phpunit -c test/ --coverage-clover build/coverage/shutdown-coverage.xml --group shutdown
	
	
tests:
	make setup
	docker-compose run elastica make phpunit
	
# Makes it easy to run a single test file. Example to run IndexTest.php: make test TEST="IndexTest.php"
test:
	${DOCKER} phpunit -c test/ test/lib/Elastica/Test/${TEST}

doc:
	${RUN_ENV} phpdoc run -d lib/ -t build/docs

# Uses the preconfigured standards in .php_cs
lint:
	${RUN_ENV} php-cs-fixer fix

loc: 
	${RUN_ENV} cloc --by-file --xml --exclude-dir=build -out=build/cloc.xml .

phploc:
	${RUN_ENV} phploc --log-csv ./build/logs/phploc.csv ${SOURCE}

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

# google-setup:
# 	docker-machine create --driver google --google-project elastica-1024 --google-machine-type n1-standard-8 elastica
# 	eval "$(docker-machine env elastica)"