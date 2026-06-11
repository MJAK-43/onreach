#!/usr/bin/env python3
import os,paramiko
c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect("51.254.196.210",username="root",password=os.environ["ONREACH_SSH_PASS"],timeout=30)
_,o,e=c.exec_command("grep vhost_ssl /var/www/vhosts/system/dev.onreach.inovixora.fr/conf/httpd.conf");print(o.read().decode())
_,o,e=c.exec_command("ls -la /var/www/vhosts/system/dev.onreach.inovixora.fr/conf/vhost_ssl.conf 2>&1");print(o.read().decode())
c.close()
