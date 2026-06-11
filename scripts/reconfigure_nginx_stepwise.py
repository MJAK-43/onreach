#!/usr/bin/env python3
import os
import sys

import paramiko

DOMAINS = ["dev.onreach.inovixora.fr", "api.onreach.inovixora.fr", "ai.onreach.inovixora.fr"]
PORTS = {"dev.onreach.inovixora.fr": "8201", "api.onreach.inovixora.fr": "8202", "ai.onreach.inovixora.fr": "8203"}

c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect("51.254.196.210", username="root", password=os.environ["ONREACH_SSH_PASS"], timeout=30)

# Step 1: clean + disable proxy mode + repair without custom nginx
for domain in DOMAINS:
    cmds = [
        f"rm -f /var/www/vhosts/system/{domain}/conf/vhost_nginx.conf /var/www/vhosts/system/{domain}/conf/vhost.conf",
        f"plesk bin domain --update {domain} -nginx-proxy-mode false -php false -hosting true",
        f"plesk repair web -y -domains-only {domain}",
        f"grep 7081 /var/www/vhosts/system/{domain}/conf/nginx.conf | wc -l",
        f"grep -n 'location /' /var/www/vhosts/system/{domain}/conf/nginx.conf",
    ]
    print(f"\n===== PHASE1 {domain} =====")
    for cmd in cmds:
        print(">>>", cmd)
        _, o, e = c.exec_command(cmd, timeout=180)
        out = o.read().decode("ascii", errors="replace")
        err = e.read().decode("ascii", errors="replace")
        code = o.channel.recv_exit_status()
        if out.strip():
            print(out.strip()[:600])
        if code != 0 and err.strip():
            print("ERR:", err.strip()[:400])
        print("exit", code)

# Step 2: add vhost_nginx and repair
snippet_tpl = """location / {{
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

for domain in DOMAINS:
    port = PORTS[domain]
    snippet = snippet_tpl.format(port=port)
    print(f"\n===== PHASE2 {domain} =====")
    for cmd in [
        f"cat > /var/www/vhosts/system/{domain}/conf/vhost_nginx.conf <<'EOF'\n{snippet}EOF",
        f"plesk repair web -y -domains-only {domain}",
        f"grep vhost_nginx /var/www/vhosts/system/{domain}/conf/nginx.conf",
    ]:
        print(">>>", cmd.split("\n")[0])
        _, o, e = c.exec_command(cmd, timeout=180)
        out = o.read().decode("ascii", errors="replace")
        err = e.read().decode("ascii", errors="replace")
        code = o.channel.recv_exit_status()
        print(out.strip()[-500:] if out else "")
        if code != 0:
            print("ERR:", err.strip()[-400:])
        print("exit", code)

for cmd in [
    "curl -s https://dev.onreach.inovixora.fr/ | head -4",
    "curl -s https://api.onreach.inovixora.fr/health",
    "curl -s https://ai.onreach.inovixora.fr/health",
]:
    print("\n>>>", cmd)
    _, o, e = c.exec_command(cmd)
    sys.stdout.buffer.write(o.read())

c.close()
