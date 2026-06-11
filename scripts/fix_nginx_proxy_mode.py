#!/usr/bin/env python3
import os
import sys

import paramiko

DOMAINS = {
    "dev.onreach.inovixora.fr": "8201",
    "api.onreach.inovixora.fr": "8202",
    "ai.onreach.inovixora.fr": "8203",
}

NGINX_SNIPPET = """location / {{
    proxy_pass http://127.0.0.1:{port};
    proxy_http_version 1.1;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_read_timeout 300s;
}}
"""

c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect("51.254.196.210", username="root", password=os.environ["ONREACH_SSH_PASS"], timeout=30)

for domain, port in DOMAINS.items():
    snippet = NGINX_SNIPPET.format(port=port)
    steps = [
        f"rm -f /var/www/vhosts/system/{domain}/conf/vhost_nginx.conf",
        f"plesk bin domain --update {domain} -nginx-proxy-mode false -php false",
        f"cat > /var/www/vhosts/system/{domain}/conf/vhost_nginx.conf <<'EOF'\n{snippet}EOF",
        f"plesk repair web -y -domains-only {domain}",
        f"grep -c 'proxy_pass.*7081' /var/www/vhosts/system/{domain}/conf/nginx.conf || true",
        f"grep -n vhost_nginx /var/www/vhosts/system/{domain}/conf/nginx.conf | tail -1",
    ]
    print(f"\n========== {domain} ==========")
    for cmd in steps:
        print(f">>> {cmd.split(chr(10))[0][:100]}")
        _, o, e = c.exec_command(cmd, timeout=180)
        out = o.read().decode("ascii", errors="replace")
        err = e.read().decode("ascii", errors="replace")
        code = o.channel.recv_exit_status()
        if "repair" in cmd or code != 0:
            if out.strip():
                print(out.strip()[-400:])
            if err.strip() and code != 0:
                print("ERR:", err.strip()[-300:])
        print(f"exit {code}")

for cmd in [
    "curl -s https://dev.onreach.inovixora.fr/ | head -4",
    "curl -s https://api.onreach.inovixora.fr/health",
    "curl -s https://ai.onreach.inovixora.fr/health",
]:
    print(f"\n>>> {cmd}")
    _, o, e = c.exec_command(cmd)
    sys.stdout.buffer.write(o.read())

c.close()
