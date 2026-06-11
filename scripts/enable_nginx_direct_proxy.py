#!/usr/bin/env python3
import os
import sys

import paramiko

DOMAINS = {
    "dev.onreach.inovixora.fr": "8201",
    "api.onreach.inovixora.fr": "8202",
    "ai.onreach.inovixora.fr": "8203",
}

PROXY_CONF = """location / {{
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
    conf = PROXY_CONF.format(port=port)
    cmds = [
        f"plesk bin site --update {domain} -nginx-proxy-mode false -php false",
        f"rm -f /var/www/vhosts/system/{domain}/conf/vhost.conf",
        f"cat > /var/www/vhosts/system/{domain}/conf/vhost_nginx.conf <<'EOF'\n{conf}EOF",
        f"plesk repair web -y -domains-only {domain}",
        f"grep -c 'location /' /var/www/vhosts/system/{domain}/conf/nginx.conf",
        f"grep -n vhost_nginx /var/www/vhosts/system/{domain}/conf/nginx.conf | tail -1",
    ]
    for cmd in cmds:
        print(f"\n>>> {domain}: {cmd[:80]}...")
        _, o, e = c.exec_command(cmd, timeout=180)
        out = o.read().decode("ascii", errors="replace")
        err = e.read().decode("ascii", errors="replace")
        code = o.channel.recv_exit_status()
        if out.strip():
            print(out.strip()[:500])
        if err.strip() and code != 0:
            print("ERR:", err.strip()[:500])
        print(f"exit {code}")

test_cmds = [
    "curl -s http://127.0.0.1:8202/health",
    "curl -s https://dev.onreach.inovixora.fr/ | head -3",
    "curl -s https://api.onreach.inovixora.fr/health",
    "curl -s https://ai.onreach.inovixora.fr/health",
]
for cmd in test_cmds:
    print(f"\n>>> {cmd}")
    _, o, e = c.exec_command(cmd, timeout=60)
    sys.stdout.buffer.write(o.read())

c.close()
