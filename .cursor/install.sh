#!/usr/bin/env bash
#
# Cloud Agent install script for the Elastica development environment.
#
# Responsibilities (durable, source-derived setup — runs once to build the
# environment snapshot and again whenever dependencies change):
#   * Install the host toolchain: Docker + fuse-overlayfs, PHP + Composer, Make.
#   * Configure the Docker daemon for this nested-container VM (fuse-overlayfs).
#   * Pre-build/pull the docker-compose images so a later boot needs no egress.
#   * Install the project's Composer dependencies (into the bind-mounted vendor/).
#
# It must stay idempotent and must not leave a process it relies on surviving:
# per-boot services (Docker daemon, Elasticsearch) are started by start.sh.
#
# Note on networking: in this VM the host has full internet access but Docker
# containers cannot reach the public internet. Composer therefore runs on the
# host (here), and the resulting vendor/ directory is bind-mounted into the
# php container by docker/compose.yaml.
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$REPO_ROOT"

export DEBIAN_FRONTEND=noninteractive
APT_INSTALL="sudo -E apt-get install -y -q --no-install-recommends -o Dpkg::Options::=--force-confold"

echo "==> Installing base packages (PHP, Composer prerequisites, Make, tooling)"
sudo -E apt-get update -qq
# PHP 8.3 (within the project's ~8.1..~8.5 support range) plus the extensions
# required by PHPUnit / PHPStan / php-cs-fixer, and helpers used by the Makefile.
$APT_INSTALL \
    make git curl wget unzip gnupg ca-certificates \
    php8.3-cli php8.3-curl php8.3-mbstring php8.3-xml \
    php8.3-bcmath php8.3-zip php8.3-intl \
    fuse-overlayfs

echo "==> Installing Composer"
if ! command -v composer >/dev/null 2>&1; then
    php -r "copy('https://getcomposer.org/installer', '/tmp/composer-setup.php');"
    php /tmp/composer-setup.php --quiet --install-dir=/tmp --filename=composer.phar
    sudo mv /tmp/composer.phar /usr/local/bin/composer
    rm -f /tmp/composer-setup.php
fi
composer --version

echo "==> Installing Docker engine"
if ! command -v docker >/dev/null 2>&1; then
    curl -fsSL https://get.docker.com -o /tmp/get-docker.sh
    sudo sh /tmp/get-docker.sh
    rm -f /tmp/get-docker.sh
fi
# Allow the agent user to talk to the daemon without sudo (matches the Makefile,
# which invokes `docker` directly). Group membership takes effect on next login,
# which is the case for shells started after this build.
sudo usermod -aG docker "$(id -un)" || true

echo "==> Configuring Docker daemon for the nested-container VM (fuse-overlayfs)"
# The default overlayfs/overlay2 driver cannot mount inside this VM; fuse-overlayfs
# is the supported nested-container storage driver.
sudo mkdir -p /etc/docker
if [ ! -f /etc/docker/daemon.json ] || ! grep -q fuse-overlayfs /etc/docker/daemon.json; then
    echo '{ "storage-driver": "fuse-overlayfs" }' | sudo tee /etc/docker/daemon.json >/dev/null
    sudo service docker restart || sudo service docker start || true
fi
sudo service docker start || true

echo "==> Waiting for the Docker daemon"
for _ in $(seq 1 30); do
    if sudo docker info >/dev/null 2>&1; then break; fi
    sleep 2
done
sudo docker info >/dev/null

echo "==> Pre-building / pulling docker-compose images (baked into the snapshot)"
# Building/pulling here means a later boot can start the stack from cache without
# needing container egress.
sudo docker compose \
    --project-name=elastica \
    --file=docker/compose.yaml \
    --file=docker/compose.proxy.yaml \
    --file=docker/compose.es.yaml \
    pull --ignore-buildable || true
sudo docker compose \
    --project-name=elastica \
    --file=docker/compose.yaml \
    --file=docker/compose.proxy.yaml \
    --file=docker/compose.es.yaml \
    build

echo "==> Installing Composer dependencies (host-side; bind-mounted into the php container)"
composer install --prefer-dist --no-interaction

echo "==> Pre-installing PHP CS Fixer via Phive (best effort; needs GPG keyservers)"
# Non-fatal: `make run-phpcs` will (re)install it on demand if this is skipped.
make install-tools || echo "   (skipped tool pre-install; run 'make run-phpcs' later to fetch it)"

echo "==> Install complete"
