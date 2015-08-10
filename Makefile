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
	docker run -v $(shell pwd):/elastica ruflin/elastica $(RUN)


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
	# Rebuild image to copy changes files to the image
	make elastica-image
	make setup
	docker-compose run elastica make phpunit
	
# Makes it easy to run a single test file. Example to run IndexTest.php: make test TEST="IndexTest.php"
test:
	make elastica-image
	make setup
	docker-compose run elastica phpunit -c test/ ${TEST}

doc:
	${RUN_ENV} phpdoc run -d lib/ -t build/docs

# Uses the preconfigured standards in .php_cs
lint:
	${RUN_ENV} php-cs-fixer fix

loc: 
	${RUN_ENV} cloc --by-file --xml --exclude-dir=build -out=build/cloc.xml .

phploc:
	${RUN_ENV} phploc --log-csv ./build/logs/phploc.csv ${SOURCE}


# VIRTUAL ENVIRONMENT

build:
	docker-compose build

setup: build
	docker-compose scale elasticsearch=3
	# TODO: Makes the snapshot directory writable for all instances. Nicer solution needed.
	docker-compose run elasticsearch chmod -R 777 /tmp/backups/

start:
	docker-compose up

stop:
	docker-compose stop

destroy: clean
	docker-compose kill
	docker-compose rm

# Stops and removes all containers and removes all images
destroy-environment:
	make remove-containers
	-docker rmi $(shell docker images -q)
	
remove-containers:
	-docker stop $(shell docker ps -a -q)
	-docker rm -v $(shell docker ps -a -q)
	
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

## DOCKER IMAGES


# This creates the base image locally for local development. In case no local development is done anymore, make sure to remove this image.
all: nginx-image elasticsearch-image elastica-dev-image elastica-image elastica-data
	# elastica image has to be built after elastica-dev image as it depends on it. Otherwise the remote image is fetched.
	
elastica-image:
	docker build -t ruflin/elastica .

# Builds all image locally. This can be used to use local images if changes are made locally to the Dockerfiles
build-images:
	docker build -t ruflin/elastica-dev-base env/elastica/
	docker build -t ruflin/elasticsearch-elastica env/elasticsearch/
	docker build -t ruflin/nginx-elastica env/nginx/
	docker build -t ruflin/elastica-data env/data/
	make elastica-image

# Removes all local images
clean-images:
	-docker rmi ruflin/elastica-dev-base
	-docker rmi ruflin/elasticsearch-elastica
	-docker rmi ruflin/nginx-elastica
	-docker rmi ruflin/elastica
	-docker rmi ruflin/elastica-data

# Pushs images as latest to the docker registry. This is normally not needed as they are directly fetched and built from Github
push-images: build-images
	docker push ruflin/elastica-dev-base
	docker push ruflin/elasticsearch-elastica
	docker push ruflin/nginx-elastica
	docker push ruflin/elastica


## OTHER

# google-setup:
# 	docker-machine create --driver google --google-project elastica-1024 --google-machine-type n1-standard-8 elastica
# 	eval "$(docker-machine env elastica)"