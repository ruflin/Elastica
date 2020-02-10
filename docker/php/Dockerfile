FROM php:7.2-fpm-alpine

COPY --from=composer /usr/bin/composer /usr/bin/composer

# Install requried packages for running Make and Phive
RUN apk add make gnupg ncurses

# Allow php to run with an
RUN adduser phpuser -u 1000 -D -g ""
USER phpuser
