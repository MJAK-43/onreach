#!/bin/sh
set -e

mc alias set local http://minio:9000 "${MINIO_ROOT_USER}" "${MINIO_ROOT_PASSWORD}" || true
sleep 2
mc alias set local http://minio:9000 "${MINIO_ROOT_USER}" "${MINIO_ROOT_PASSWORD}"

for bucket in documents uploads exports; do
  mc mb --ignore-existing "local/${bucket}"
done

echo "MinIO buckets ready: documents, uploads, exports"
