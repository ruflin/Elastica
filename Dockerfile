# PHP 6 Docker file with Composer installed
FROM composer/composer

RUN apt-get update
RUN apt-get upgrade -y
RUN apt-get install -y nano
RUN apt-get install -y cloc

# XSL and Graphviz for PhpDocumentor
RUN apt-get install -y php5-xsl
# TODO: Debian is putting the xsl extension in a different directory, should be in: /usr/local/lib/php/extensions/no-debug-non-zts-20131226
RUN echo "extension=/usr/lib/php5/20131226/xsl.so" >> /usr/local/etc/php/conf.d/xsl.ini
RUN apt-get install -y graphviz


RUN echo "date.timezone=UTC" >> /usr/local/etc/php/conf.d/timezone.ini

# Xdebug for coverage report
RUN apt-get install -y php5-xdebug
RUN echo "zend_extension=/usr/lib/php5/20131226/xdebug.so" >> /usr/local/etc/php/conf.d/xdebug.ini

# Memcache
RUN apt-get install -y php5-memcache
RUN echo "extension=/usr/lib/php5/20131226/memcache.so" >> /usr/local/etc/php/conf.d/memcache.ini

# Add composer bin to the environment
ENV PATH=/root/composer/vendor/bin:$PATH

# Overcome github access limits. GITHUB_OAUTH_TOKEN environment variable must be set with private token
RUN composer self-update

# Install development tools
RUN composer global require "phpunit/phpunit=~4.7"
RUN composer global require "pdepend/pdepend=~2.0"
RUN composer global require "phpmd/phpmd=~2.2"
RUN composer global require "mayflower/php-codebrowser=~1.1"
RUN composer global require "sebastian/phpcpd=~2.0"
RUN composer global require "squizlabs/php_codesniffer=~2.3"
RUN composer global require "phploc/phploc=~2.1"
RUN composer global require "fabpot/php-cs-fixer=1.8.1"


# Documentor dependencies
RUN composer global require "phpdocumentor/template-zend=~1.3"
RUN composer global require "phpdocumentor/phpdocumentor=~2.8"

# Install depdencies
WORKDIR /app
COPY composer.json /app/
RUN composer install

# Guzzle is not included composer.json because of PHP 5.3
RUN composer require "guzzlehttp/guzzle=~6.0"

ENTRYPOINT []

ENV ES_HOST elasticsearch
ENV PROXY_HOST nginx
