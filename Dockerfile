# PHP 6 Docker file with Composer installed
FROM ruflin/elastica-dev-base

# ENVIRONMENT Setup - Needed in this image?
ENV ES_HOST elasticsearch
ENV PROXY_HOST nginx

# Install depdencies
WORKDIR /elastica

# Copy composer file first as this only changes rarely
COPY composer.json /elastica/
COPY Makefile /elastica/

ENV ELASTICA_DEV true

RUN make init

# Copy rest of the files, ignoring .dockerignore files
COPY lib /elastica/lib
COPY test /elastica/test

RUN make clean
RUN make 

ENTRYPOINT []

