#!/bin/bash
# Configure Plesk reverse proxy → services Docker locaux (DEV uniquement).
# SSL Let's Encrypt géré par Plesk (nginx → Apache 7081 → vhost_ssl.conf → Docker).
# Ne modifie pas les autres domaines.

set -euo pipefail

declare -A PROXY_MAP=(
  ["dev.onreach.inovixora.fr"]="8201"
  ["api.onreach.inovixora.fr"]="8202"
  ["ai.onreach.inovixora.fr"]="8203"
)

for domain in "${!PROXY_MAP[@]}"; do
  PORT="${PROXY_MAP[$domain]}"
  SSL_CONF="/var/www/vhosts/system/${domain}/conf/vhost_ssl.conf"
  HTTPD_CONF="/var/www/vhosts/system/${domain}/conf/httpd.conf"

  plesk bin domain --update "$domain" -php false -hosting true 2>/dev/null || true
  rm -f "/var/www/vhosts/system/${domain}/conf/vhost_nginx.conf"

  cat > "$SSL_CONF" <<EOF
<IfModule mod_proxy.c>
    ProxyPreserveHost On
    ProxyPass / http://127.0.0.1:${PORT}/
    ProxyPassReverse / http://127.0.0.1:${PORT}/
</IfModule>
EOF

  if [ -f "$HTTPD_CONF" ] && ! grep -qF "Include \"${SSL_CONF}\"" "$HTTPD_CONF" 2>/dev/null; then
    sed -i "/#extension sectigo end/a\\\tInclude \"${SSL_CONF}\"" "$HTTPD_CONF"
  fi

  echo "Configured Apache SSL proxy for ${domain} -> http://127.0.0.1:${PORT}"
done

apachectl configtest
systemctl reload httpd
echo "Plesk Apache SSL proxy configured."
