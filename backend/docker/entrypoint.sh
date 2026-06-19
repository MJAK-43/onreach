#!/bin/sh
set -e

needs_composer_install=false
if [ ! -f vendor/autoload.php ]; then
    needs_composer_install=true
elif [ ! -f vendor/composer/installed.json ] || [ composer.lock -nt vendor/composer/installed.json ]; then
    needs_composer_install=true
fi

if [ "$needs_composer_install" = true ]; then
    # --no-scripts: cache/migrations gérés ci-dessous (évite course avec deploy-dev.sh).
    composer install --no-interaction --ignore-platform-reqs --optimize-autoloader --no-scripts
fi

if [ ! -f .env ]; then
    cat > .env <<EOF
APP_ENV=${APP_ENV:-prod}
APP_SECRET=${APP_SECRET:-change_me}
DATABASE_URL=${DATABASE_URL:-}
REDIS_URL=${REDIS_URL:-redis://redis:6379}
JWT_PASSPHRASE=${JWT_PASSPHRASE:-}
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
CORS_ALLOW_ORIGIN=${CORS_ALLOW_ORIGIN:-}
MESSENGER_TRANSPORT_DSN=${MESSENGER_TRANSPORT_DSN:-}
DEFAULT_URI=${DEFAULT_URI:-http://localhost}
EOF
else
    grep -q '^JWT_SECRET_KEY=' .env || echo 'JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem' >> .env
    grep -q '^JWT_PUBLIC_KEY=' .env || echo 'JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem' >> .env
fi

if [ ! -f config/jwt/private.pem ]; then
    mkdir -p config/jwt
    openssl genrsa -passout "pass:${JWT_PASSPHRASE}" -out config/jwt/private.pem 4096
    openssl rsa -pubout -in config/jwt/private.pem -passin "pass:${JWT_PASSPHRASE}" -out config/jwt/public.pem
fi

php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true
php bin/console cache:clear --no-warmup || true
php bin/console cache:warmup || true
php bin/console app:seed:rbac --no-interaction || true
php bin/console app:seed:checklist --no-interaction || true
php bin/console app:seed:pathways --no-interaction || true
php bin/console app:seed:demo-users --no-interaction || true
exec "$@"
