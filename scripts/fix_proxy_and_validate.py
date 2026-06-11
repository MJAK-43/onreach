#!/usr/bin/env python3
import os
import sys
from pathlib import Path

import paramiko

HOST = os.environ.get("ONREACH_SSH_HOST", "51.254.196.210")
USER = os.environ.get("ONREACH_SSH_USER", "root")
PASSWORD = os.environ.get("ONREACH_SSH_PASS", "")
ROOT = Path(__file__).resolve().parents[1]


def main() -> int:
    if not PASSWORD:
        print("ONREACH_SSH_PASS required", file=sys.stderr)
        return 1

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, username=USER, password=PASSWORD, timeout=30)

    sftp = client.open_sftp()
    local = ROOT / "deployments/scripts/plesk-proxy-onreach.sh"
    sftp.put(str(local), "/opt/onreach/app/deployments/scripts/plesk-proxy-onreach.sh")
    sftp.close()

    cmds = [
        "chmod +x /opt/onreach/app/deployments/scripts/plesk-proxy-onreach.sh",
        "bash /opt/onreach/app/deployments/scripts/plesk-proxy-onreach.sh",
        "docker ps --format 'table {{.Names}}\t{{.Status}}' | grep onreach",
        "curl -sf http://127.0.0.1:8202/health || echo backend_fail",
        "curl -sf http://127.0.0.1:8203/health || echo ai_fail",
        r"curl -sf -o /dev/null -w 'local_fe:%{http_code}\n' http://127.0.0.1:8201/",
        r"curl -sf -o /dev/null -w 'https_dev:%{http_code}\n' https://dev.onreach.inovixora.fr/ || echo https_dev_fail",
        "curl -sf https://api.onreach.inovixora.fr/health || echo api_https_fail",
        "curl -sf https://ai.onreach.inovixora.fr/health || echo ai_https_fail",
        "plesk bin site -l 2>/dev/null | grep -i onreach || plesk bin subdomain -l 2>/dev/null | grep -i onreach",
        "find /var/www/vhosts -name vhost_nginx.conf -path '*onreach*' 2>/dev/null",
    ]

    for cmd in cmds:
        print(f"=== {cmd} ===")
        _, stdout, stderr = client.exec_command(cmd, timeout=180)
        out = stdout.read().decode("ascii", errors="replace")
        err = stderr.read().decode("ascii", errors="replace")
        code = stdout.channel.recv_exit_status()
        if out:
            print(out.rstrip())
        if err.strip():
            print("ERR:", err.rstrip())
        print(f"exit {code}\n")

    client.close()
    return 0


if __name__ == "__main__":
    sys.exit(main())
