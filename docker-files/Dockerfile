FROM debian:13.3-slim AS install

SHELL ["/bin/bash", "-c"]

RUN <<EOF
set -euo pipefail

export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get --yes install --no-install-recommends \
    less \
    ca-certificates \
    curl \
    php \
    php-curl \
    php-json \
    php-mbstring \
    php-imagick \
    php-zip \
    php-mysql \
    php-xml \
    apache2 \
    libapache2-mod-php \
    mariadb-client \
    mariadb-server

apt-get clean
rm -rf /var/lib/apt/lists/*
EOF

FROM install AS db

RUN <<EOF
set -euo pipefail

mkdir -p /run/mysqld
chown mysql:mysql /run/mysqld
chmod 777 /run/mysqld
EOF

COPY ./db/mariadb.cnf /etc/mysql/mariadb.conf.d/99-bind.cnf

USER mysql

EXPOSE 3306

ENTRYPOINT [ "mariadbd" ]

FROM install AS app

RUN <<EOF
set -euo pipefail

a2enmod rewrite
a2dissite 000-default.conf

ln -sfT /dev/stderr /var/log/apache2/error.log
ln -sfT /dev/stdout /var/log/apache2/access.log
ln -sfT /dev/stdout /var/log/apache2/other_vhosts_access.log
EOF

ARG WORDPRESS_VERSION=6.9
ARG WORDPRESS_SHA1=256dda5bb6a43aecd806b7a62528f442c06e6c25

RUN <<EOF
set -euo pipefail
set -x

rm -rf /var/www/html/*
tmp_dir=$(mktemp -d)

curl \
    --location \
    --silent \
    --output "${tmp_dir}/wordpress-${WORDPRESS_VERSION}.tar.gz" \
    "https://wordpress.org/wordpress-${WORDPRESS_VERSION}.tar.gz"

echo "${WORDPRESS_SHA1}  ${tmp_dir}/wordpress-${WORDPRESS_VERSION}.tar.gz" | sha1sum --check --strict -

tar \
    --extract \
    --gzip \
    --file="${tmp_dir}/wordpress-${WORDPRESS_VERSION}.tar.gz" \
    --strip-components=1 \
    --directory=/var/www/html

chown -R www-data:www-data /var/www/html

rm -rf "${tmp_dir}"
EOF

ARG WP_CLI_VERSION=2.12.0
ARG WP_CLI_SHA256=ce34ddd838f7351d6759068d09793f26755463b4a4610a5a5c0a97b68220d85c

RUN <<EOF
set -euo pipefail
set -x

tmp_dir=$(mktemp -d)

curl \
    --location \
    --silent \
    --output "${tmp_dir}/wp-cli.phar" \
    "https://github.com/wp-cli/wp-cli/releases/download/v${WP_CLI_VERSION}/wp-cli-${WP_CLI_VERSION}.phar"

echo "${WP_CLI_SHA256}  ${tmp_dir}/wp-cli.phar" | sha256sum --check --strict -

install -m 755 "${tmp_dir}/wp-cli.phar" /usr/local/bin/wp-cli
rm -rf "${tmp_dir}"
EOF

COPY --chown=www-data ./app/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
RUN a2ensite 000-default.conf

EXPOSE 80

ENTRYPOINT [ "/usr/sbin/apachectl", "-D", "FOREGROUND" ]
