#!/usr/bin/env python3
import os, sys
from pathlib import Path
import paramiko

ROOT = Path(__file__).resolve().parents[1]
c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect("51.254.196.210", username="root", password=os.environ["ONREACH_SSH_PASS"], timeout=30)
sftp = c.open_sftp()
sftp.put(str(ROOT / "deployments/scripts/plesk-proxy-onreach.sh"), "/opt/onreach/app/deployments/scripts/plesk-proxy-onreach.sh")
sftp.close()

cmds = [
    "chmod +x /opt/onreach/app/deployments/scripts/plesk-proxy-onreach.sh",
    "bash /opt/onreach/app/deployments/scripts/plesk-proxy-onreach.sh",
    "grep -n vhost_nginx /var/www/vhosts/system/dev.onreach.inovixora.fr/conf/nginx.conf",
    "grep -n vhost_nginx /var/www/vhosts/system/api.onreach.inovixora.fr/conf/nginx.conf",
    "curl -s http://127.0.0.1:8202/health",
    "docker logs onreach-dev-backend-1 2>&1 | tail -5",
    "curl -s https://dev.onreach.inovixora.fr/ | head -3",
    "curl -s https://api.onreach.inovixora.fr/health",
    "curl -s https://ai.onreach.inovixora.fr/health",
]
for cmd in cmds:
    print("\n>>>", cmd)
    _, o, e = c.exec_command(cmd, timeout=180)
    out = o.read().decode("ascii", "replace")
    sys.stdout.buffer.write(out.encode("ascii", "replace"))
    err = e.read().decode("ascii", "replace")
    if err.strip():
        sys.stdout.buffer.write(b"\nERR:" + err.encode("ascii", "replace"))
c.close()
