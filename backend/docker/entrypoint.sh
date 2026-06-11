#!/bin/sh
set -e

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --ignore-platform-reqs
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
fi

if [ ! -f config/jwt/private.pem ]; then
    mkdir -p config/jwt
    openssl genrsa -passout "pass:${JWT_PASSPHRASE}" -out config/jwt/private.pem 4096
    openssl rsa -pubout -in config/jwt/private.pem -passin "pass:${JWT_PASSPHRASE}" -out config/jwt/public.pem
fi

php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true
php bin/console app:seed:rbac --no-interaction || true
exec "$@"
