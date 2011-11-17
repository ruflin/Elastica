#!/bin/bash

# Install Pake
pyrus channel-discover pear.indeyets.ru
pyrus install -f http://pear.indeyets.ru/get/pake-1.6.3.tgz

# Build and install PHP Memcache extension
wget http://pecl.php.net/get/memcache-2.2.6.tgz
tar -xzf memcache-2.2.6.tgz
sh -c "cd memcache-2.2.6 && php `pyrus get php_dir|tail -1`/pake.php install"
echo "extension=memcache.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
