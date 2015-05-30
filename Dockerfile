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
RUN composer global require "phpunit/phpunit"
RUN composer global require "pdepend/pdepend=2.0.*"
RUN composer global require "phpmd/phpmd"
RUN composer global require "mayflower/php-codebrowser"
RUN composer global require "sebastian/phpcpd"
RUN composer global require "squizlabs/php_codesniffer"
RUN composer global require "phploc/phploc"
RUN composer global require "fabpot/php-cs-fixer=1.8.1"


# Documentor dependencies
RUN composer global require "phpdocumentor/template-zend"
RUN composer global require "phpdocumentor/phpdocumentor"

# Install depdencies
WORKDIR /app
COPY composer.json /app/
RUN composer install

# Guzzle is not included composer.json because of PHP 5.3
RUN composer require "guzzlehttp/guzzle"

ENTRYPOINT []

ENV ES_HOST elasticsearch
ENV PROXY_HOST nginx
