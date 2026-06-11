#!/usr/bin/env python3
import os,paramiko
c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect("51.254.196.210",username="root",password=os.environ["ONREACH_SSH_PASS"],timeout=30)
_,o,e=c.exec_command("grep 7081 /var/www/vhosts/system/chaningfp.inovixora.fr/conf/nginx.conf | wc -l");print('chaningfp 7081 count', o.read().decode())
_,o,e=c.exec_command("cat /var/www/vhosts/system/chaningfp.inovixora.fr/conf/vhost_nginx.conf 2>/dev/null | head -10");print(o.read().decode())
_,o,e=c.exec_command("grep Include /var/www/vhosts/system/inovixora.fr/conf/httpd.conf | grep ssl");print(o.read().decode())
c.close()
