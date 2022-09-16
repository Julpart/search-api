FROM nginx:1.16-alpine as nginx-base
RUN apk add --no-cache bash

COPY provision/docker-images/nginx/nginx.conf /etc/nginx/nginx.conf
COPY provision/docker-images/nginx/vhost.conf /opt/vhost.conf
COPY provision/docker-images/nginx/docker-entrypoint.sh /usr/local/bin/

RUN chown -R nginx:0 /var/cache/nginx && \
    chmod -R g+w /var/cache/nginx && \
    chown -R nginx:0 /etc/nginx && \
    chmod -R g+w /etc/nginx

EXPOSE 8080
STOPSIGNAL SIGTERM
USER nginx

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

CMD ["nginx"]


FROM php:7.4.12-fpm-alpine3.12 as php-fpm-base
# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN set -xe; \
        apk add --update --no-cache -t .php-run-deps \
        bash \
        freetype \
        icu-libs \
        libbz2 \
        libevent \
        libjpeg-turbo \
        libjpeg-turbo-utils \
        libmcrypt \
        libpng \
        libuuid \
        libwebp \
        libxml2 \
        libxslt \
        libzip \
        yaml && \
    apk add --update --no-cache -t .php-build-deps \
        g++ \
        make \
        autoconf \
        libzip-dev \ 
        icu-dev \ 
        bzip2-dev \
        freetype-dev \
        libmcrypt-dev \
        jpeg-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
        unixodbc-dev \
        yaml-dev && \
    curl -O https://download.microsoft.com/download/e/4/e/e4e67866-dffd-428c-aac7-8d28ddafb39b/msodbcsql17_17.6.1.1-1_amd64.apk && \
    apk add --allow-untrusted msodbcsql17_17.6.1.1-1_amd64.apk; \
    curl -O https://download.microsoft.com/download/e/4/e/e4e67866-dffd-428c-aac7-8d28ddafb39b/mssql-tools_17.6.1.1-1_amd64.apk && \
    apk add --allow-untrusted mssql-tools_17.6.1.1-1_amd64.apk; \
    rm ./*.apk; \
    docker-php-ext-install \
        bcmath \
        bz2 \
        calendar \
        exif \
        intl \
        mysqli \
        opcache \
        pcntl \
        pdo_mysql \
        zip; \
    if [[ "${PHP_VERSION:0:3}" == "7.4" ]]; then \
        docker-php-ext-configure gd \
            --with-webp \
            --with-freetype \
            --with-jpeg; \
    else \
        docker-php-ext-configure gd \
            --with-gd \
            --with-webp-dir \
            --with-freetype-dir=/usr/include/ \
            --with-png-dir=/usr/include/ \
            --with-jpeg-dir=/usr/include/; \
    fi; \
    NPROC=$(getconf _NPROCESSORS_ONLN) && \
    docker-php-ext-install "-j${NPROC}" gd && \
    pecl channel-update pecl.php.net && \
    pecl install yaml-2.1.0 \
                 pdo_sqlsrv-5.8.1 \
                 sqlsrv-5.8.1 \
                 mcrypt-1.0.3 && \
    docker-php-ext-enable yaml \
                          pdo_sqlsrv \
                          sqlsrv \
                          mcrypt && \
    apk del --purge .php-build-deps && \
    rm -rf \
        /usr/src/php/ext/ast \
        /usr/src/php/ext/uploadprogress \
        /usr/include/php \
        /usr/lib/php/build \
        /tmp/* \
        /root/.composer \
        /var/cache/apk/*
USER www-data

FROM php-fpm-base as cli-base
USER root
RUN apk add --update --no-cache -t .build-deps \
    git \
    openssh-client \
    ca-certificates \
    patch \
    mariadb-client 
RUN wget -qO- https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=1.10.16
RUN drush_launcher_url="https://github.com/drush-ops/drush-launcher/releases/download/0.6.0/drush.phar"; \
    wget -O drush.phar "${drush_launcher_url}"; \
    chmod +x drush.phar; \
    mv drush.phar /usr/local/bin/drush
USER www-data
WORKDIR /var/www/html
ENTRYPOINT ["/bin/bash", "-c"]
CMD ["tail -f /dev/null"]

FROM cli-base as builder
COPY app/composer.json app/composer.lock /var/www/html/
COPY app/patches /var/www/html/patches
COPY app /var/www/html

FROM nginx-base as nginx
COPY --from=builder --chown=root:root /var/www/html/docroot /var/www/html/docroot

FROM php-fpm-base as php-fpm
COPY --from=builder --chown=root:root /var/www/html /var/www/html

FROM cli-base as cli
COPY --from=builder --chown=root:root /var/www/html /var/www/html

FROM php-fpm-base as php-fpm-local
USER root
RUN  apk add --update --no-cache -t .php-build-deps \
       g++ \
       make \
       autoconf && \
     pecl install xdebug-2.9.8 && \
    apk del --purge .php-build-deps && \
    rm -rf \
        /usr/src/php/ext/ast \
        /usr/src/php/ext/uploadprogress \
        /usr/include/php \
        /usr/lib/php/build \
        /tmp/* \
        /root/.composer \
        /var/cache/apk/*

COPY provision/docker-images/php-fpm-local/docker-entrypoint.sh /usr/local/bin/
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]

FROM cli-base as cli-local
USER root
COPY provision/docker-images/cli-local/docker-entrypoint.sh /usr/local/bin/
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["tail","-f","/dev/null"]
