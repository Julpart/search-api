#!/bin/bash

set -e

if [[ $DEBUG ]]; then
  set -x
fi

HOST_UID=$(stat -c %u /var/www/html)
HOST_GID=$(stat -c %g /var/www/html)

if [ -n "$HOST_GID" ] && [ "$HOST_GID" != "0" ]; then
  if [ -z "$(getent group $HOST_GID)" ]; then
        echo "User group does not exist and will be created."
        echo ok to create user...
        addgroup -g $HOST_GID user
  fi
fi
if [ -n "$HOST_UID" ] && [ "$HOST_UID" != "0" ]; then
  if [ -z "$(getent passwd $HOST_UID)" ]; then
        echo "User name does not exist and will be created."
        echo ok to create user...
        adduser -u $HOST_UID -s /bin/bash -D -G user user
  fi
fi

exec "$@"
