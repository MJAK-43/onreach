#!/usr/bin/env python3
import os, sys
import paramiko

c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect("51.254.196.210", username="root", password=os.environ["ONREACH_SSH_PASS"], timeout=30)

cmds = [
    "cat /var/www/vhosts/system/api.onreach.inovixora.fr/conf/vhost.conf",
    "cat /var/www/vhosts/system/ai.onreach.inovixora.fr/conf/vhost.conf",
    "grep -n ProxyPass /var/www/vhosts/system/api.onreach.inovixora.fr/conf/httpd.conf | head -5",
    "curl -s -H 'Host: api.onreach.inovixora.fr' https://127.0.0.1/health -k",
    "curl -s -H 'Host: ai.onreach.inovixora.fr' https://127.0.0.1/health -k",
    "curl -s -H 'Host: dev.onreach.inovixora.fr' https://127.0.0.1/ -k | head -2",
    "plesk bin site --info api.onreach.inovixora.fr | grep -E 'WWW-Root|Certificate|SSL'",
    "ls -la /var/www/vhosts/inovixora.fr/api.onreach.inovixora.fr/httpdocs/",
    "tail -5 /var/www/vhosts/system/api.onreach.inovixora.fr/logs/error_log",
]
for cmd in cmds:
    print("\n>>>", cmd)
    _, o, e = c.exec_command(cmd, timeout=60)
    sys.stdout.buffer.write(o.read())
    err = e.read()
    if err:
        sys.stdout.buffer.write(b"\nERR:" + err)
c.close()
