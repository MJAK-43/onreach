#!/usr/bin/env python3
import os,paramiko
c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect("51.254.196.210",username="root",password=os.environ["ONREACH_SSH_PASS"],timeout=30)
for d in ["dev.onreach.inovixora.fr","api.onreach.inovixora.fr","n8n.inovixora.com"]:
 _,o,e=c.exec_command(f"awk '/7081/,/VirtualHost/' /var/www/vhosts/system/{d}/conf/httpd.conf | grep Include")
 print(d, o.read().decode() or "(no Include in 7081 block)")
c.close()
