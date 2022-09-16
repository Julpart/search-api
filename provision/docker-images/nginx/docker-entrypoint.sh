#!/bin/bash

set -e

if [[ $DEBUG ]]; then
  set -x
fi

if [ ! "$(ls -A /etc/nginx/conf.d)" ]; then
    cp /opt/vhost.conf /etc/nginx/conf.d/
else
    cp /opt/vhost.conf /etc/nginx/conf.d/default.conf
fi

# Configure docroot.
if [ -n "$NGINX_DOCROOT" ]; then
    sed -i 's@root /var/www/html/;@'"root /var/www/html/${NGINX_DOCROOT};"'@' /etc/nginx/conf.d/*.conf
fi

# Ensure server name defined.
if [ -z "$NGINX_SERVER_NAME" ]; then
    NGINX_SERVER_NAME=localhost
fi

# Set server name
sed -i 's/SERVER_NAME/'"${NGINX_SERVER_NAME}"'/' /etc/nginx/conf.d/*.conf

# Ensure upstream defined.
if [ -z "$PHP_UPSTREAM" ]; then
    PHP_UPSTREAM="127.0.0.1:9000"
fi

# Set server name
sed -i 's/PHP_UPSTREAM/'"${PHP_UPSTREAM}"'/' /etc/nginx/nginx.conf

exec "$@"
