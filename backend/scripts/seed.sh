#!/bin/sh
set -e

echo "Seeding database (placeholder for Sprint 1)..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
