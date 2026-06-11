#!/usr/bin/env python3
import os
import sys

import paramiko

DOMAINS = {
    "dev.onreach.inovixora.fr": "8201",
    "api.onreach.inovixora.fr": "8202",
    "ai.onreach.inovixora.fr": "8203",
}

c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect("51.254.196.210", username="root", password=os.environ["ONREACH_SSH_PASS"], timeout=30)

for domain, port in DOMAINS.items():
    ssl_path = f"/var/www/vhosts/system/{domain}/conf/vhost_ssl.conf"
    httpd = f"/var/www/vhosts/system/{domain}/conf/httpd.conf"
    ssl = f"""<IfModule mod_proxy.c>
    ProxyPreserveHost On
    ProxyPass / http://127.0.0.1:{port}/
    ProxyPassReverse / http://127.0.0.1:{port}/
</IfModule>
"""
    cmds = [
        f"cat > {ssl_path} <<'EOF'\n{ssl}EOF",
        f"grep -q 'Include \"{ssl_path}\"' {httpd} || sed -i '/#extension sectigo end/a\\\tInclude \"{ssl_path}\"' {httpd}",
        f"grep Include {httpd}",
    ]
    print(f"\n=== {domain} ===")
    for cmd in cmds:
        _, o, e = c.exec_command(cmd, timeout=60)
        out = o.read().decode("ascii", errors="replace")
        if out.strip():
            print(out.strip())

c.exec_command("apachectl configtest && systemctl reload httpd")[1].channel.recv_exit_status()

for cmd in [
    "curl -s https://dev.onreach.inovixora.fr/ | grep -i title | head -1",
    "curl -s https://api.onreach.inovixora.fr/health",
    "curl -s https://ai.onreach.inovixora.fr/health",
    "curl -sk --resolve api.onreach.inovixora.fr:7081:127.0.0.1 https://127.0.0.1:7081/health --connect-to api.onreach.inovixora.fr:7081:127.0.0.1:7081 -H 'Host: api.onreach.inovixora.fr'",
]:
    print("\n>>>", cmd)
    _, o, e = c.exec_command(cmd)
    sys.stdout.buffer.write(o.read())

c.close()
