FROM php:8.1-alpine

WORKDIR /var/www/html

COPY --from=composer /usr/bin/composer /usr/bin/composer

# Install requried packages for running Make and Phive
RUN apk add make gnupg ncurses

# Allow php to run with an
RUN adduser phpuser -u 1000 -D -g ""
USER phpuser

# Config allow-plugin.symfony.Flex on PHPUser context
RUN composer global config allow-plugins.symfony/flex false
