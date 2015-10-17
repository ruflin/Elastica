#/bin/bash

SOURCE = "./lib"

# By default docker environment is used to run commands. To run without the predefined environment, set RUN_ENV=" " either as parameter or as environment variable
ifndef RUN_ENV
	RUN_ENV = docker run -v $(shell pwd):/elastica ruflin/elastica
endif

.PHONY: clean
clean:
	rm -r -f ./build
	rm -r -f ./vendor
	rm -r -f ./composer.lock

# Runs commands inside virtual environemnt. Example usage inside docker: make run RUN="make phpunit"
.PHONY: run
run:
	docker run -v $(shell pwd):/elastica ruflin/elastica $(RUN)


### Quality checks / development tools ###
.PHONY: code-browser
code-browser:
	${RUN_ENV} phpcb --log ./build/logs --source ${SOURCE} --output ./build/code-browser

# Copy paste detector
.PHONY: cpd
cpd:
	${RUN_ENV} phpcpd --log-pmd ./build/logs/pmd-cpd.xml ${SOURCE}

.PHONY: messdetector
messdetector:
	${RUN_ENV} phpmd ${SOURCE} text codesize,unusedcode,naming,design ./build/phpmd.xml

.PHONY: messdetector-ci
messdetector-ci:
	${RUN_ENV} phpmd ${SOURCE} xml codesize,unusedcode,naming,design --reportfile ./build/logs/pmd.xml

.PHONY: dependencies
dependencies:
	${RUN_ENV} pdepend --jdepend-xml=./build/logs/jdepend.xml \
		--jdepend-chart=./build/pdepend/dependencies.svg \
		--overview-pyramid=./build/pdepend/overview-pyramid.svg \
		${SOURCE}
.PHONY: phpunit
phpunit:
	phpunit -c test/ --coverage-clover build/coverage/unit-coverage.xml --group unit
	phpunit -c test/ --coverage-clover build/coverage/functional-coverage.xml --group functional
	phpunit -c test/ --coverage-clover build/coverage/shutdown-coverage.xml --group shutdown

.PHONY: tests
tests:
	# Rebuild image to copy changes files to the image
	make elastica-image
	make setup
	docker-compose run elastica make phpunit

# Makes it easy to run a single test file. Example to run IndexTest.php: make test TEST="IndexTest.php"
.PHONY: test
test:
	make elastica-image
	make setup
	docker-compose run elastica phpunit -c test/ ${TEST}

.PHONY: doc
doc:
	${RUN_ENV} phpdoc run -d lib/ -t build/docs

# Uses the preconfigured standards in .php_cs
.PHONY: lint
lint:
	${RUN_ENV} php-cs-fixer fix

.PHONY: loc
loc:
	${RUN_ENV} cloc --by-file --xml --exclude-dir=build -out=build/cloc.xml .

.PHONY: phploc
phploc:
	${RUN_ENV} phploc --log-csv ./build/logs/phploc.csv ${SOURCE}


# VIRTUAL ENVIRONMENT
.PHONY: build
build:
	docker-compose build

.PHONY: setup
setup: build
	docker-compose scale elasticsearch=3
	# TODO: Makes the snapshot directory writable for all instances. Nicer solution needed.
	docker-compose run elasticsearch chmod -R 777 /tmp/backups/

.PHONY: start
start:
	docker-compose up

.PHONY: stop
stop:
	docker-compose stop

.PHONY: destroy
destroy: clean
	docker-compose kill
	docker-compose rm

# Stops and removes all containers and removes all images
.PHONY: destroy-environment
destroy-environment:
	make remove-containers
	-docker rmi $(shell docker images -q)

.PHONY: remove-containers
remove-containers:
	-docker stop $(shell docker ps -a -q)
	-docker rm -v $(shell docker ps -a -q)

# Starts a shell inside the elastica image
.PHONY: shell
shell:
	docker run -v $(shell pwd):/elastica -ti ruflin/elastica /bin/bash

# Starts a shell inside the elastica image with the full environment running
.PHONY: env-shell
env-shell:
	docker-compose run elastica /bin/bash

# Visualise repo
.PHONY: gource
gource:
	gource --log-format git \
		--seconds-per-day 0.1 \
		--title 'Elastica (https://github.com/ruflin/Elastica)' \
		--user-scale 1 \
		--max-user-speed 50

## DOCKER IMAGES

.PHONY: elastica-image
elastica-image:
	docker build -t ruflin/elastica .

# Builds all image locally. This can be used to use local images if changes are made locally to the Dockerfiles
.PHONY: build-images
build-images:
	docker build -t ruflin/elastica-dev-base env/elastica/
	docker build -t ruflin/elasticsearch-elastica env/elasticsearch/
	docker build -t ruflin/nginx-elastica env/nginx/
	docker build -t ruflin/elastica-data env/data/
	make elastica-image

# Removes all local images
.PHONY: clean-images
clean-images:
	-docker rmi ruflin/elastica-dev-base
	-docker rmi ruflin/elasticsearch-elastica
	-docker rmi ruflin/nginx-elastica
	-docker rmi ruflin/elastica
	-docker rmi ruflin/elastica-data

# Pushs images as latest to the docker registry. This is normally not needed as they are directly fetched and built from Github
.PHONY: push-images
push-images: build-images
	docker push ruflin/elastica-dev-base
	docker push ruflin/elasticsearch-elastica
	docker push ruflin/nginx-elastica
	docker push ruflin/elastica


## OTHER

# google-setup:
# 	docker-machine create --driver google --google-project elastica-1024 --google-machine-type n1-standard-8 elastica
# 	eval "$(docker-machine env elastica)"
