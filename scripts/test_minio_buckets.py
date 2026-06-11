#!/usr/bin/env python3
import os,sys,paramiko
c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect("51.254.196.210",username="root",password=os.environ["ONREACH_SSH_PASS"],timeout=30)
cmd="docker exec onreach-dev-minio-1 sh -c 'mc alias set local http://127.0.0.1:9000 \"$MINIO_ROOT_USER\" \"$MINIO_ROOT_PASSWORD\" && mc ls local/'"
_,o,e=c.exec_command(cmd);print(o.read().decode());print(e.read().decode())
c.close()
