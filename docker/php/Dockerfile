ARG PHP_VERSION=8.1

FROM php:${PHP_VERSION}-alpine

WORKDIR /var/www/html

COPY --from=composer/composer:2.8-bin /composer /usr/bin/composer

# Install requried packages for running Make and Phive
RUN apk add make gnupg ncurses

# Allow php to run with an
RUN adduser phpuser -u 1000 -D -g ""
USER phpuser
