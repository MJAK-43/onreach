#!/usr/bin/env python3
import os,paramiko
c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect("51.254.196.210",username="root",password=os.environ["ONREACH_SSH_PASS"],timeout=30)
_,o,e=c.exec_command("grep -r 'vhost_ssl.conf' /var/www/vhosts/system/*/conf/httpd.conf 2>/dev/null | head -10");print(o.read().decode())
_,o,e=c.exec_command("find /var/www/vhosts/system -name vhost_ssl.conf 2>/dev/null");print(o.read().decode())
c.close()
