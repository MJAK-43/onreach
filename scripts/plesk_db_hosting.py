#!/usr/bin/env python3
import os,paramiko
c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect("51.254.196.210",username="root",password=os.environ["ONREACH_SSH_PASS"],timeout=30)
_,o,e=c.exec_command('plesk db "DESCRIBE hosting"');print(o.read().decode())
_,o,e=c.exec_command('plesk db "SELECT dom_id, nginx_proxy_mode, nginx_transparent_mode, nginx_serve_static, nginx_serve_php FROM hosting LIMIT 3"');print(o.read().decode())
c.close()
