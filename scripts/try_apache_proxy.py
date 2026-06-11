#!/usr/bin/env python3
import os, sys
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
    conf = f"""<IfModule mod_proxy.c>
    ProxyPreserveHost On
    ProxyPass / http://127.0.0.1:{port}/
    ProxyPassReverse / http://127.0.0.1:{port}/
</IfModule>
"""
    path = f"/var/www/vhosts/system/{domain}/conf/vhost.conf"
    cmd = f"cat > {path} <<'APACHE_EOF'\n{conf}APACHE_EOF"
    print(">>>", domain)
    c.exec_command(f"rm -f /var/www/vhosts/system/{domain}/conf/vhost_nginx.conf")[1].channel.recv_exit_status()
    _, o, e = c.exec_command(cmd)
    o.channel.recv_exit_status()
    c.exec_command(f"plesk repair web -y -domains-only {domain}")[1].channel.recv_exit_status()

cmds = [
    "httpd -M 2>/dev/null | grep proxy || apachectl -M 2>/dev/null | grep proxy",
    "curl -s http://127.0.0.1:8202/health",
    "curl -s https://dev.onreach.inovixora.fr/ | head -3",
    "curl -s https://api.onreach.inovixora.fr/health",
    "curl -s https://ai.onreach.inovixora.fr/health",
]
for cmd in cmds:
    print("\n>>>", cmd)
    _, o, e = c.exec_command(cmd, timeout=60)
    sys.stdout.buffer.write(o.read())
c.close()
