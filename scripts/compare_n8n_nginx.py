#!/usr/bin/env python3
import os
c=__import__('paramiko').SSHClient();c.set_missing_host_key_policy(__import__('paramiko').AutoAddPolicy());c.connect("51.254.196.210",username="root",password=os.environ["ONREACH_SSH_PASS"],timeout=30)
_,o,e=c.exec_command("grep -n 'location' /var/www/vhosts/system/n8n.inovixora.com/conf/nginx.conf");print(o.read().decode())
c.close()
