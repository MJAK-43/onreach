#!/usr/bin/env python3
import os, sys
import paramiko

c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect("51.254.196.210", username="root", password=os.environ["ONREACH_SSH_PASS"], timeout=30)

cmds = [
    "plesk bin site --help 2>&1 | grep -i proxy | head -10",
    "plesk bin subdomain --help 2>&1 | grep -i proxy | head -10",
    "plesk bin site --info dev.onreach.inovixora.fr 2>&1 | grep -i proxy",
]

for cmd in cmds:
    print(">>>", cmd)
    _, o, e = c.exec_command(cmd)
    print(o.read().decode())
    print(e.read().decode())

c.close()
