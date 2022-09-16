#!/bin/bash

set -e

if [[ $DEBUG ]]; then
  set -x
fi

if [ -n "$PHP_SENDMAIL_PATH" ]; then
     sed -i 's@^;sendmail_path.*@'"sendmail_path = ${PHP_SENDMAIL_PATH}"'@' $PHP_INI_DIR/php.ini
fi

if [[ $PHP_XDEBUG_ENABLED = 1 ]]; then
     docker-php-ext-enable xdebug
fi

cat <<EOF >> $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini
xdebug.default_enable = 1
xdebug.remote_enable = 1
xdebug.remote_handler = dbgp
xdebug.remote_port = 9000
xdebug.remote_autostart = 1
xdebug.remote_connect_back = 1
xdebug.remote_host = localhost
xdebug.max_nesting_level = 256
EOF

if [[ $PHP_XDEBUG_AUTOSTART = 0 ]]; then
     sed -i 's/^xdebug.remote_autostart.*/xdebug.remote_autostart = 0/' $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini
fi

if [[ $PHP_XDEBUG_REMOTE_CONNECT_BACK = 0 ]]; then
     sed -i 's/^xdebug.remote_connect_back.*/xdebug.remote_connect_back = 0/' $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini
fi

if [[ $PHP_XDEBUG_REMOTE_HOST ]]; then
     sed -i 's/^xdebug.remote_host.*/'"xdebug.remote_host = ${PHP_XDEBUG_REMOTE_HOST}"'/' $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini
fi

exec "$@"
