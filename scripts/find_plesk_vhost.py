#!/usr/bin/env python3
import os, sys
import paramiko

c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect("51.254.196.210", username="root", password=os.environ["ONREACH_SSH_PASS"], timeout=30)

cmds = [
    "find /var/www/vhosts -name 'vhost_nginx.conf' 2>/dev/null | head -20",
    "ls -la /var/www/vhosts/system/dev.onreach.inovixora.fr/conf/ 2>/dev/null",
    "plesk bin subscription --info inovixora.fr 2>/dev/null | head -15",
    "plesk bin subdomain --info dev.onreach.inovixora.fr 2>/dev/null",
    "docker exec onreach-dev-backend-1 tail -30 /var/www/html/var/log/prod.log 2>/dev/null || docker exec onreach-dev-backend-1 ls -la /var/www/html/var/log/",
    "docker exec onreach-dev-backend-1 php bin/console cache:clear --env=prod 2>&1",
    "docker exec onreach-dev-backend-1 curl -s http://127.0.0.1:9000 2>&1 | head -3 || true",
    "docker exec onreach-dev-backend-nginx-1 getent hosts backend",
]
for cmd in cmds:
    print("\n>>>", cmd)
    _, o, e = c.exec_command(cmd, timeout=120)
    sys.stdout.buffer.write(o.read().decode("ascii","replace").encode("ascii","replace"))
    err = e.read().decode("ascii","replace")
    if err.strip():
        sys.stdout.buffer.write(b"\nERR:"+ err.encode("ascii","replace"))
c.close()
