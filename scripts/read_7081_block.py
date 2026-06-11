#!/usr/bin/env python3
import os,paramiko
c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect("51.254.196.210",username="root",password=os.environ["ONREACH_SSH_PASS"],timeout=30)
_,o,e=c.exec_command("sed -n '10,150p' /var/www/vhosts/system/api.onreach.inovixora.fr/conf/httpd.conf");print(o.read().decode())
c.close()
