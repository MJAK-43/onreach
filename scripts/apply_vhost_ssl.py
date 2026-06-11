#!/usr/bin/env python3
"""Configure Apache SSL reverse proxy via vhost_ssl.conf + manual Include in httpd.conf."""
import os
import sys
import re

import paramiko

DOMAINS = {
    "dev.onreach.inovixora.fr": "8201",
    "api.onreach.inovixora.fr": "8202",
    "ai.onreach.inovixora.fr": "8203",
}

SSL_CONF = """<IfModule mod_proxy.c>
    ProxyPreserveHost On
    ProxyPass / http://127.0.0.1:{port}/
    ProxyPassReverse / http://127.0.0.1:{port}/
</IfModule>
"""

c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect("51.254.196.210", username="root", password=os.environ["ONREACH_SSH_PASS"], timeout=30)

for domain, port in DOMAINS.items():
    ssl = SSL_CONF.format(port=port)
    print(f"\n===== {domain} =====")

    # Remove broken nginx custom config
    c.exec_command(f"rm -f /var/www/vhosts/system/{domain}/conf/vhost_nginx.conf")[1].channel.recv_exit_status()

    # Write SSL apache directives
    _, o, e = c.exec_command(
        f"cat > /var/www/vhosts/system/{domain}/conf/vhost_ssl.conf <<'EOF'\n{ssl}EOF"
    )
    o.channel.recv_exit_status()

    # Patch httpd.conf 7081 block to Include vhost_ssl.conf before closing VirtualHost
    httpd_path = f"/var/www/vhosts/system/{domain}/conf/httpd.conf"
    _, o, e = c.exec_command(f"cat {httpd_path}")
    content = o.read().decode()

    include_line = f'\tInclude "{httpd_path.replace("httpd.conf", "vhost_ssl.conf")}"\n'
    if "vhost_ssl.conf" not in content:
        # Insert before closing of first VirtualHost (7081)
        content = content.replace(
            "\t\t#extension sectigo end\n\t</VirtualHost>",
            f"\t\t#extension sectigo end\n{include_line}\t</VirtualHost>",
            1,
        )
        # Write back via heredoc is tricky; use python on server
        escaped = content.replace("'", "'\"'\"'")
        c.exec_command(f"cat > {httpd_path} <<'HTTPDEOF'\n{content}HTTPDEOF")[1].channel.recv_exit_status()

    _, o, e = c.exec_command(f"grep vhost_ssl {httpd_path}")
    print("include:", o.read().decode().strip())

    _, o, e = c.exec_command("apachectl configtest 2>&1")
    print(o.read().decode()[:300])

c.exec_command("systemctl reload httpd 2>&1 || service httpd reload 2>&1")[1].channel.recv_exit_status()

for cmd in [
    "curl -s https://dev.onreach.inovixora.fr/ | head -4",
    "curl -s https://api.onreach.inovixora.fr/health",
    "curl -s https://ai.onreach.inovixora.fr/health",
]:
    print("\n>>>", cmd)
    _, o, e = c.exec_command(cmd)
    sys.stdout.buffer.write(o.read())

c.close()
