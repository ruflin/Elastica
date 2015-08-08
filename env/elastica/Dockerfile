# This image is the base image for the Elastica development and includes all parts which rarely change
# PHP 6 Docker file with Composer installed
FROM php:5.6
MAINTAINER Nicolas Ruflin <spam@ruflin.com>

RUN apt-get update && apt-get install -y \
	cloc \
	git \
	graphviz \
	nano \
	php5-memcache \ 
	php5-xsl
	# XSL and Graphviz for PhpDocumentor
	
RUN docker-php-ext-install sockets
	
RUN rm -r /var/lib/apt/lists/*
	
# Xdebug for coverage report
RUN pecl install xdebug

## PHP Configuration

RUN echo "memory_limit=1024M" >> /usr/local/etc/php/conf.d/memory-limit.ini
RUN echo "date.timezone=UTC" >> /usr/local/etc/php/conf.d/timezone.ini
RUN echo "extension=/usr/lib/php5/20131226/memcache.so" >> /usr/local/etc/php/conf.d/memcache.ini # Enable Memcache
RUN echo "extension=/usr/lib/php5/20131226/xsl.so" >> /usr/local/etc/php/conf.d/xsl.ini # TODO: Debian is putting the xsl extension in a different directory, should be in: /usr/local/lib/php/extensions/no-debug-non-zts-20131226
RUN echo "zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20131226/xdebug.so" >> /usr/local/etc/php/conf.d/xdebug.ini

# Install and setup composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_HOME /root/composer

# Add composer bin to the environment
ENV PATH=/root/composer/vendor/bin:$PATH

COPY composer.json /root/composer/

# Install development tools
RUN composer global install --prefer-source
