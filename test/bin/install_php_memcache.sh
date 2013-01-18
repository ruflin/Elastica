#!/bin/bash

# Build and install PHP Memcache extension
wget http://pecl.php.net/get/memcache-2.2.7.tgz
tar -xzf memcache-2.2.7.tgz
sh -c "cd memcache-2.2.7 && phpize && ./configure --enable-memcache && make && sudo make install"
echo "extension=memcache.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
