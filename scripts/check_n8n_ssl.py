#!/usr/bin/env python3
import os,paramiko
c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect("51.254.196.210",username="root",password=os.environ["ONREACH_SSH_PASS"],timeout=30)
_,o,e=c.exec_command("grep -n vhost_ssl /var/www/vhosts/system/n8n.inovixora.com/conf/httpd.conf");print(o.read().decode())
_,o,e=c.exec_command("grep -n Include /var/www/vhosts/system/n8n.inovixora.com/conf/httpd.conf | tail -5");print(o.read().decode())
c.close()
