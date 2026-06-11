#!/usr/bin/env python3
import os,paramiko
c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect("51.254.196.210",username="root",password=os.environ["ONREACH_SSH_PASS"],timeout=30)
_,o,e=c.exec_command("grep -n '7081\\|listen.*443' /var/www/vhosts/system/n8n.inovixora.com/conf/nginx.conf | head -20");print(o.read().decode())
_,o,e=c.exec_command("sed -n '1,40p' /var/www/vhosts/system/n8n.inovixora.com/conf/nginx.conf");print(o.read().decode())
c.close()
