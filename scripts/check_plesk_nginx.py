#!/usr/bin/env python3
import os, sys
import paramiko

c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect("51.254.196.210", username="root", password=os.environ["ONREACH_SSH_PASS"], timeout=30)

cmds = [
    "cat /var/www/vhosts/inovixora.fr/dev.onreach.inovixora.fr/conf/vhost_nginx.conf",
    "grep -n 'vhost_nginx\\|8201\\|8202\\|include' /var/www/vhosts/system/dev.onreach.inovixora.fr/conf/nginx.conf | head -30",
    "grep -n 'vhost_nginx\\|8201\\|8202\\|include' /var/www/vhosts/system/api.onreach.inovixora.fr/conf/nginx.conf | head -30",
    "curl -s https://dev.onreach.inovixora.fr/ | head -5",
    "curl -s http://127.0.0.1:8202/health",
    "docker restart onreach-dev-backend-nginx-1 && sleep 3 && curl -s http://127.0.0.1:8202/health",
    "docker exec onreach-dev-backend-nginx-1 nginx -t && docker exec onreach-dev-backend-nginx-1 wget -qO- http://127.0.0.1/health 2>&1 || docker exec onreach-dev-backend-nginx-1 curl -s http://127.0.0.1/health",
]
for cmd in cmds:
    print("\n>>>", cmd)
    _, o, e = c.exec_command(cmd, timeout=120)
    out = o.read().decode("ascii", "replace")
    err = e.read().decode("ascii", "replace")
    sys.stdout.buffer.write(out.encode("ascii", "replace"))
    if err.strip():
        sys.stdout.buffer.write(b"\n" + err.encode("ascii", "replace"))
c.close()
