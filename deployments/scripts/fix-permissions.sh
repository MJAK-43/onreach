#!/bin/bash
set -euo pipefail

mkdir -p /opt/onreach/docker/{grafana,prometheus,loki,postgres,redis,minio}/data

chown -R 472:0 /opt/onreach/docker/grafana/data
chown -R 65534:65534 /opt/onreach/docker/prometheus/data
chown -R 10001:10001 /opt/onreach/docker/loki/data

chmod -R 775 /opt/onreach/docker/grafana/data
chmod -R 775 /opt/onreach/docker/prometheus/data
chmod -R 775 /opt/onreach/docker/loki/data

echo "Volume permissions fixed."
