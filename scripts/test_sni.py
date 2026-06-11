#!/usr/bin/env python3
import os,sys,paramiko
c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect("51.254.196.210",username="root",password=os.environ["ONREACH_SSH_PASS"],timeout=30)
cmds=[
 "curl -s --resolve dev.onreach.inovixora.fr:443:127.0.0.1 https://dev.onreach.inovixora.fr/ | head -5",
 "curl -s --resolve api.onreach.inovixora.fr:443:127.0.0.1 https://api.onreach.inovixora.fr/health",
 "curl -s --resolve ai.onreach.inovixora.fr:443:127.0.0.1 https://ai.onreach.inovixora.fr/health",
 "curl -s https://api.onreach.inovixora.fr/health",
 "curl -sI https://api.onreach.inovixora.fr/health | head -10",
 "grep -r 'api.onreach' /etc/nginx/plesk.conf.d/vhosts/ 2>/dev/null | head -3",
 "ls /etc/nginx/plesk.conf.d/vhosts/ | grep onreach",
]
for cmd in cmds:
 print('\n>>>',cmd);_,o,e=c.exec_command(cmd);sys.stdout.buffer.write(o.read())
c.close()
