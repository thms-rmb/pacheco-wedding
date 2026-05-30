# syntax=docker/dockerfile:1.17

ARG DEBIAN_FRONTEND=noninteractive

ARG WORDPRESS_VERSION=7.0
ARG WORDPRESS_SHA256=530c8fdeb16fb0affdb53eb727b6a04bb8d166621c20029e389cabb01a0fa921

ARG WP_CLI_VERSION=2.12.0
ARG WP_CLI_SHA256=ce34ddd838f7351d6759068d09793f26755463b4a4610a5a5c0a97b68220d85c

FROM scratch AS data

ARG WORDPRESS_VERSION
ARG WORDPRESS_SHA256

ADD \
    --checksum=sha256:${WORDPRESS_SHA256} \
    --unpack=true \
    "https://wordpress.org/wordpress-${WORDPRESS_VERSION}.tar.gz" \
    /wordpress

ARG WP_CLI_VERSION
ARG WP_CLI_SHA256

ADD \
    --checksum=sha256:${WP_CLI_SHA256} \
    "https://github.com/wp-cli/wp-cli/releases/download/v${WP_CLI_VERSION}/wp-cli-${WP_CLI_VERSION}.phar" \
    /wp-cli

FROM debian:13-slim AS base

ARG DEBIAN_FRONTEND

SHELL ["/bin/bash", "-e", "-u", "-o", "pipefail", "-c"]

ADD --chown=root:root --chmod=644 \
    etc/apt/apt.conf.d/99-update-error-mode \
    /etc/apt/apt.conf.d/99-update-error-mode

ADD --chown=root:root --chmod=644 \
    etc/apt/sources.list.d/mysql.sources \
    /etc/apt/sources.list.d/mysql.sources

ADD --chown=root:root --chmod=644 \
    usr/share/keyrings/mysql-apt-config.asc \
    /usr/share/keyrings/mysql-apt-config.asc

RUN --mount=target=/var/lib/apt/lists,id=apt-lists,type=cache,sharing=locked \
    --mount=target=/var/cache/apt,id=apt-cache,type=cache,sharing=locked \
<<EOF
apt-get --yes update
apt-get --yes install --no-install-recommends \
    gosu
EOF

FROM base AS app

ARG WORDPRESS_VERSION
ARG WP_CLI_VERSION

RUN --mount=target=/var/lib/apt/lists,id=apt-lists,type=cache,sharing=locked \
    --mount=target=/var/cache/apt,id=apt-cache,type=cache,sharing=locked \
<<EOF
apt-get --yes install --no-install-recommends \
    apache2 \
    less \
    libapache2-mod-php \
    mysql-community-client \
    php \
    php-curl \
    php-imagick \
    php-json \
    php-mbstring \
    php-mysql \
    php-xml \
    php-zip
EOF

COPY --chown=root:root etc/apache2/sites-available/apache-vhost.conf /etc/apache2/sites-available/apache-vhost.conf

RUN <<EOF
a2enmod rewrite
a2dissite 000-default.conf
a2ensite apache-vhost.conf
EOF

RUN rm -rf /var/www/html/*

COPY --chown=www-data:www-data --from=data /wordpress/wordpress/ /var/www/html/
COPY --chown=www-data:www-data --chmod=755 --from=data /wp-cli /usr/local/bin/wp-cli

COPY --chown=www-data:www-data --chmod=755 scripts/app-entrypoint /usr/local/bin/app-entrypoint

EXPOSE 80

ENTRYPOINT ["app-entrypoint"]

FROM base AS db

RUN --mount=target=/var/lib/apt/lists,id=apt-lists,type=cache,sharing=locked \
    --mount=target=/var/cache/apt,id=apt-cache,type=cache,sharing=locked \
<<EOF
apt-get --yes install --no-install-recommends \
    mysql-community-server
EOF

RUN find /var/lib/mysql -mindepth 1 -maxdepth 1 -exec rm -rf {} +

COPY --chown=mysql:mysql --chmod=644 etc/mysql/conf.d/my.cnf /etc/mysql/conf.d/my.cnf

VOLUME /var/lib/mysql

EXPOSE 3306

COPY --chown=root:root --chmod=755 scripts/db-entrypoint /usr/local/bin/db-entrypoint

ENTRYPOINT ["db-entrypoint"]
CMD ["mysqld"]
