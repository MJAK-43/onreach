#!/usr/bin/env python3
import os,paramiko
c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect("51.254.196.210",username="root",password=os.environ["ONREACH_SSH_PASS"],timeout=30)
_,o,e=c.exec_command('plesk db "SHOW TABLES LIKE \'%nginx%\'"');print(o.read().decode())
_,o,e=c.exec_command('plesk db "SELECT * FROM dom_param WHERE dom_id=(SELECT id FROM domains WHERE name=\'n8n.inovixora.com\') AND param LIKE \'%nginx%\'"');print(o.read().decode())
_,o,e=c.exec_command('plesk db "SELECT * FROM dom_param WHERE dom_id=(SELECT id FROM domains WHERE name=\'dev.onreach.inovixora.fr\') AND param LIKE \'%nginx%\'"');print(o.read().decode())
c.close()
