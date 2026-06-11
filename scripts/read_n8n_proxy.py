#!/usr/bin/env python3
import os, sys
import paramiko
c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect("51.254.196.210",username="root",password=os.environ["ONREACH_SSH_PASS"],timeout=30)
for cmd in ["cat /var/www/vhosts/system/n8n.inovixora.com/conf/vhost_nginx.conf","grep -n vhost_nginx /var/www/vhosts/system/n8n.inovixora.com/conf/nginx.conf"]:
 print('>>>',cmd);_,o,e=c.exec_command(cmd);print(o.read().decode())
c.close()
