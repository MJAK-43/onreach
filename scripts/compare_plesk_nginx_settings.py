#!/usr/bin/env python3
import os,paramiko
c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect("51.254.196.210",username="root",password=os.environ["ONREACH_SSH_PASS"],timeout=30)
cmd="""plesk db "SELECT name, hst_type, php, nginx_proxy_mode, nginx_transparent_mode, nginx_serve_static, nginx_serve_php FROM domains d LEFT JOIN hosting h ON d.id=h.dom_id WHERE name IN ('dev.onreach.inovixora.fr','api.onreach.inovixora.fr','n8n.inovixora.com')" """
_,o,e=c.exec_command(cmd);print(o.read().decode());print(e.read().decode())
c.close()
