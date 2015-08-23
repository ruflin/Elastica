# PHP 6 Docker file with Composer installed
FROM ruflin/elastica-dev-base
MAINTAINER Nicolas Ruflin <spam@ruflin.com>

# ENVIRONMENT Setup - Needed in this image?
ENV ES_HOST elasticsearch
ENV PROXY_HOST nginx

# Install depdencies
WORKDIR /elastica

# Copy composer file first as this only changes rarely
COPY composer.json /elastica/

ENV ELASTICA_DEV true

# Set empty environment so that Makefile commands inside container do not prepend the environment
ENV RUN_ENV " "

# Commands are taken from Makefile. Everytime the makefile is updated, this commands is rerun
RUN mkdir -p \
	./build/code-browser \
	./build/docs \
	./build/logs \
	./build/pdepend \
	./build/coverage
	
RUN composer install --prefer-source

# Copy rest of the files, ignoring .dockerignore files
COPY lib /elastica/lib
COPY test /elastica/test
COPY Makefile /elastica/

ENTRYPOINT []
