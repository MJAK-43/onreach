#!/usr/bin/env python3
import os,paramiko
c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect("51.254.196.210",username="root",password=os.environ["ONREACH_SSH_PASS"],timeout=30)
for d in ["dev.onreach.inovixora.fr","api.onreach.inovixora.fr"]:
 print(f"\n=== {d} ===")
 for cmd in [f"grep -n vhost.conf /var/www/vhosts/system/{d}/conf/httpd.conf", f"grep -n ProxyPass /var/www/vhosts/system/{d}/conf/httpd.conf", f"wc -l /var/www/vhosts/system/{d}/conf/httpd.conf"]:
  _,o,e=c.exec_command(cmd); print(cmd, "->", o.read().decode().strip())
c.close()
