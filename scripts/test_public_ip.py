#!/usr/bin/env python3
import os,sys,paramiko
IP="51.254.196.210"
c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect(IP,username="root",password=os.environ["ONREACH_SSH_PASS"],timeout=30)
cmds=[
 f"curl -s --resolve dev.onreach.inovixora.fr:443:{IP} https://dev.onreach.inovixora.fr/ | head -5",
 f"curl -s --resolve api.onreach.inovixora.fr:443:{IP} https://api.onreach.inovixora.fr/health",
 f"curl -s --resolve ai.onreach.inovixora.fr:443:{IP} https://ai.onreach.inovixora.fr/health",
 "curl -s https://dev.onreach.inovixora.fr/ | head -5",
 "curl -s https://api.onreach.inovixora.fr/health",
 "curl -s https://ai.onreach.inovixora.fr/health",
 "curl -s -k https://127.0.0.1:7081/health -H 'Host: api.onreach.inovixora.fr'",
]
for cmd in cmds:
 print('\n>>>',cmd);_,o,e=c.exec_command(cmd);sys.stdout.buffer.write(o.read())
c.close()
