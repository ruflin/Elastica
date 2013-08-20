#!/bin/sh

sudo killall nginx 2>/dev/null

echo "installing nginx"

sudo apt-get install nginx

echo "stopping stock nginx"

/etc/init.d/nginx stop

echo "running nginx"

sudo nginx -p test/nginx/ -c nginx.conf
