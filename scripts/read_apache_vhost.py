#!/usr/bin/env python3
import os,paramiko
c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect("51.254.196.210",username="root",password=os.environ["ONREACH_SSH_PASS"],timeout=30)
_,o,e=c.exec_command("sed -n '140,169p' /var/www/vhosts/system/api.onreach.inovixora.fr/conf/httpd.conf");print("=== api httpd tail ===\n",o.read().decode())
_,o,e=c.exec_command("cat /var/www/vhosts/system/dev.onreach.inovixora.fr/conf/vhost.conf");print("=== dev vhost.conf ===\n",o.read().decode())
_,o,e=c.exec_command("curl -s -k --resolve api.onreach.inovixora.fr:7081:127.0.0.1 https://127.0.0.1:7081/health -H 'Host: api.onreach.inovixora.fr'");print("=== apache direct ===\n",o.read().decode())
c.close()
