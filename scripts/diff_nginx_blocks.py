#!/usr/bin/env python3
import os
import paramiko
c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect("51.254.196.210",username="root",password=os.environ["ONREACH_SSH_PASS"],timeout=30)
for domain in ["dev.onreach.inovixora.fr","n8n.inovixora.com"]:
 print(f"\n===== {domain} =====")
 _,o,e=c.exec_command(f"sed -n '65,145p' /var/www/vhosts/system/{domain}/conf/nginx.conf")
 print(o.read().decode())
c.close()
