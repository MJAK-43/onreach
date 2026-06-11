#!/usr/bin/env python3
import os, sys
from pathlib import Path
import paramiko
ROOT = Path(__file__).resolve().parents[1]
c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect("51.254.196.210", username="root", password=os.environ["ONREACH_SSH_PASS"], timeout=30)
sftp = c.open_sftp()
sftp.put(str(ROOT / "deployments/scripts/backup-dev.sh"), "/opt/onreach/app/deployments/scripts/backup-dev.sh")
sftp.close()
_, o, e = c.exec_command("bash /opt/onreach/app/deployments/scripts/backup-dev.sh", timeout=300)
sys.stdout.buffer.write(o.read())
sys.stdout.buffer.write(e.read())
_, o, e = c.exec_command("ls -la /opt/onreach/backups/minio/")
sys.stdout.buffer.write(o.read())
c.close()
