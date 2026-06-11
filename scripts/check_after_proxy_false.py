#!/usr/bin/env python3
import os,paramiko
c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect("51.254.196.210",username="root",password=os.environ["ONREACH_SSH_PASS"],timeout=30)
for d in ["dev.onreach.inovixora.fr","api.onreach.inovixora.fr","n8n.inovixora.com"]:
 print(f"\n=== {d} ===")
 for cmd in [
  f"grep -n 'location /' /var/www/vhosts/system/{d}/conf/nginx.conf",
  f"grep 7081 /var/www/vhosts/system/{d}/conf/nginx.conf | head -3",
  f"grep vhost_nginx /var/www/vhosts/system/{d}/conf/nginx.conf",
 ]:
  _,o,e=c.exec_command(cmd); print(cmd.split('/')[-2], o.read().decode().strip() or "(empty)")
c.close()
