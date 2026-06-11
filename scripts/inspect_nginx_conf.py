#!/usr/bin/env python3
import os, sys
import paramiko
c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect("51.254.196.210",username="root",password=os.environ["ONREACH_SSH_PASS"],timeout=30)
cmds=[
 "cat /var/www/vhosts/system/dev.onreach.inovixora.fr/conf/vhost_nginx.conf 2>/dev/null || echo NO_SYSTEM_VHOST",
 "cat /var/www/vhosts/inovixora.fr/dev.onreach.inovixora.fr/conf/vhost_nginx.conf 2>/dev/null || echo NO_LEGACY",
 "grep -n 'location' /var/www/vhosts/system/dev.onreach.inovixora.fr/conf/nginx.conf",
 "sed -n '120,160p' /var/www/vhosts/system/dev.onreach.inovixora.fr/conf/nginx.conf",
]
for cmd in cmds:
 print('\n>>>',cmd);_,o,e=c.exec_command(cmd);print(o.read().decode())
c.close()
