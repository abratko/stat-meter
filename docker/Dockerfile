FROM alpine:3.14 as base

RUN apk add --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/edge/community php
RUN apk add --no-cache --update \
    dpkg \
    nginx \
    php7 \
    php7-cli \
    php7-fpm \
    php7-ctype \
    php7-mbstring \
    php7-json \
    php7-opcache \
    php7-pdo \
    php7-phar \
    php7-curl \
    php7-openssl \
    php7-session  \
    php7-iconv \
    php7-xml \
    php7-dom \
    php7-simplexml \
    php7-tokenizer \
    openssl \
    curl \
    tzdata \
    tini \
    supervisor \
    logrotate \
    dcron \
    libcap \
#    && chown nobody:nobody /usr/sbin/crond \
#    && setcap cap_setgid=ep /usr/sbin/crond \
    && mkdir -p /app \
    && rm -rf /tmp/* \
    /var/{cache,log}/* \
    /etc/logrotate.d \
    /etc/crontabs/* \
    /etc/periodic/daily/logrotate

RUN export LATEST_VERSION=`curl https://api.github.com/repos/ClickHouse/ClickHouse/tags 2>/dev/null | grep -Eo '([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)-stable' | grep -Eo '([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)' | head -n 1` \
  && curl -o clickhouse-common-static.tgz -L  https://github.com/ClickHouse/ClickHouse/releases/download/v$LATEST_VERSION-stable/clickhouse-common-static-$LATEST_VERSION.tgz \
  && curl -o clickhouse-client.tgz -L https://github.com/ClickHouse/ClickHouse/releases/download/v$LATEST_VERSION-stable/clickhouse-client-$LATEST_VERSION.tgz \
  && tar -xzvf clickhouse-common-static.tgz \
  && clickhouse-common-static-$LATEST_VERSION/install/doinst.sh \
  && tar -xzvf clickhouse-client.tgz \
  && clickhouse-client-$LATEST_VERSION/install/doinst.sh \
  && rm clickhouse-common-static.tgz -f \
  && rm clickhouse-client.tgz -f

#RUN #/usr/bin/clickhouse-client

#RUN apk add  \
#    apt-transport-https \
#    ca-certificates \
#    dirmngr
#
#RUN apk update \
#    && apk fetch gnupg \
#    && apk add gnupg
#
#RUN gpg --keyserver hkp://keyserver.ubuntu.com:80 --recv E0C56BD4
#
#echo "deb https://repo.clickhouse.com/deb/stable/ main/" | sudo tee \
#    /etc/apt/sources.list.d/clickhouse.list
#sudo apt-get update
#
#sudo apt-get install -y clickhouse-server clickhouse-client
#
#sudo service clickhouse-server start
#clickhouse-client

COPY docker/rootfs /

FROM base as builder

RUN apk add --no-cache --update \
    nodejs \
    npm

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . ./

RUN mkdir -p .composer \
    && mv .env.production .env.local \
    && composer install --no-dev --no-scripts --optimize-autoloader \
    && composer dump-env prod \
    && APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear \
    && npm install --global yarn \
    && yarn install --frozenlock-file \
    && yarn encore production


FROM base

RUN chown -R nobody:nobody /var/log/nginx

USER nobody

WORKDIR /app

COPY --from=builder --chown=nobody:nobody /app/bin ./bin
COPY --from=builder --chown=nobody:nobody /app/config ./config
COPY --from=builder --chown=nobody:nobody /app/migrations  ./migrations
COPY --from=builder --chown=nobody:nobody /app/public  ./public
COPY --from=builder --chown=nobody:nobody /app/src  ./src
COPY --from=builder --chown=nobody:nobody /app/templates ./templates
COPY --from=builder --chown=nobody:nobody /app/vendor ./vendor
COPY --from=builder --chown=nobody:nobody /app/var ./var
COPY --from=builder --chown=nobody:nobody /app/.env.local.php ./

# Symfony calculates the root directory of the application from the directory containing the composer.json file.
# Therefore, we need it.
COPY --from=builder --chown=nobody:nobody /app/composer.json ./

VOLUME "/app/var/log"
VOLUME "/var/log/nginx"
VOLUME "/var/log/php-fpm"
VOLUME "/tmp"

EXPOSE 8080

ENTRYPOINT ["/sbin/tini", "--", "/usr/bin/docker-entrypoint.sh"]

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:8080/fpm-ping
