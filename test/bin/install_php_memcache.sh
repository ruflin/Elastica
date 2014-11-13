#!/bin/bash

# Build and install PHP Memcache extension
wget http://pecl.php.net/get/memcache-${MEMCACHE_VER}.tgz
tar -xzf memcache-${MEMCACHE_VER}.tgz
sh -c "cd memcache-${MEMCACHE_VER} && phpize && ./configure --enable-memcache && make && sudo make install"
echo "extension=memcache.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`

