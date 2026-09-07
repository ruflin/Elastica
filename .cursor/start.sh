#!/usr/bin/env bash
#
# Cloud Agent start script for the Elastica development environment.
#
# Responsibilities (per-boot runtime reconciliation — must tolerate restarts,
# avoid duplicates, reach readiness, then return):
#   * Apply the kernel settings Elasticsearch and nested Docker networking need.
#   * Start the Docker daemon.
#   * Bring up the docker-compose stack (2-node Elasticsearch cluster + nginx
#     proxy + php container) from images already built by install.sh.
#   * Wait until the Elasticsearch cluster is healthy before returning.
#
# The php container gets ES_VERSION at creation time so that the canonical
# `make docker-run-phpunit` command (which execs inside it) has it available.
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$REPO_ROOT"

# Elasticsearch is pinned to this release for the 9.x branch (see README.md).
export ES_VERSION="${ES_VERSION:-9.1.0}"

COMPOSE=(docker compose
    --project-name=elastica
    --file=docker/compose.yaml
    --file=docker/compose.proxy.yaml
    --file=docker/compose.es.yaml)

echo "==> Applying kernel settings for Elasticsearch and nested Docker networking"
sudo modprobe br_netfilter 2>/dev/null || true
# Elasticsearch requires a high mmap count.
sudo sysctl -w vm.max_map_count=262144 >/dev/null
# In this nested VM, routing same-bridge container traffic through the host
# netfilter FORWARD chain drops it (breaking container-to-container comms, so
# the ES nodes can never form a cluster). Let bridged traffic pass at L2.
sudo sysctl -w net.bridge.bridge-nf-call-iptables=0 net.bridge.bridge-nf-call-ip6tables=0 >/dev/null 2>&1 || true

echo "==> Starting the Docker daemon"
sudo service docker start || true
for _ in $(seq 1 30); do
    if sudo docker info >/dev/null 2>&1; then break; fi
    sleep 2
done
sudo docker info >/dev/null

echo "==> Bringing up the docker-compose stack (Elasticsearch + proxy + php)"
sudo -E "${COMPOSE[@]}" up --detach --remove-orphans

echo "==> Waiting for the Elasticsearch cluster to become healthy"
healthy=0
for _ in $(seq 1 60); do
    status="$(sudo docker exec es01 curl -s "http://localhost:9200/_cluster/health" 2>/dev/null \
        | sed -n 's/.*"status":"\([^"]*\)".*/\1/p')"
    if [ "$status" = "green" ] || [ "$status" = "yellow" ]; then
        echo "    Elasticsearch cluster status: $status"
        healthy=1
        break
    fi
    sleep 5
done
if [ "$healthy" -ne 1 ]; then
    echo "!! Elasticsearch did not become healthy in time; recent es01 logs:" >&2
    sudo docker logs --tail 20 es01 >&2 || true
    exit 1
fi

echo "==> Environment ready"
echo "    Elasticsearch:  http://localhost:9200"
echo "    Proxy:          http://localhost:8000 (and http://localhost:8001 -> 403)"
