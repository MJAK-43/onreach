#!/usr/bin/env python3
"""Disable PHP on On'Reach DEV subdomains so nginx reverse proxy works like n8n."""
import os
import sys
from pathlib import Path

import paramiko

ROOT = Path(__file__).resolve().parents[1]
DOMAINS = [
    "dev.onreach.inovixora.fr",
    "api.onreach.inovixora.fr",
    "ai.onreach.inovixora.fr",
]
PROXY_MAP = {
    "dev.onreach.inovixora.fr": "http://127.0.0.1:8201",
    "api.onreach.inovixora.fr": "http://127.0.0.1:8202",
    "ai.onreach.inovixora.fr": "http://127.0.0.1:8203",
}


def main() -> int:
    password = os.environ.get("ONREACH_SSH_PASS", "")
    if not password:
        print("ONREACH_SSH_PASS required", file=sys.stderr)
        return 1

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect("51.254.196.210", username="root", password=password, timeout=30)

    sftp = client.open_sftp()
    sftp.put(
        str(ROOT / "deployments/scripts/plesk-proxy-onreach.sh"),
        "/opt/onreach/app/deployments/scripts/plesk-proxy-onreach.sh",
    )
    sftp.close()

    cmds = []
    for domain in DOMAINS:
        cmds.append(f"plesk bin site --update {domain} -php false")
        cmds.append(f"rm -f /var/www/vhosts/inovixora.fr/{domain}/conf/vhost_nginx.conf")

    cmds.append("chmod +x /opt/onreach/app/deployments/scripts/plesk-proxy-onreach.sh")
    cmds.append("bash /opt/onreach/app/deployments/scripts/plesk-proxy-onreach.sh")

    for domain in DOMAINS:
        cmds.append(f"grep -n vhost_nginx /var/www/vhosts/system/{domain}/conf/nginx.conf | tail -1")
        cmds.append(f"grep -c 'location /' /var/www/vhosts/system/{domain}/conf/nginx.conf")

    cmds.extend([
        "curl -s http://127.0.0.1:8202/health",
        "curl -s -o /dev/null -w 'dev:%{http_code}\\n' https://dev.onreach.inovixora.fr/",
        "curl -s https://api.onreach.inovixora.fr/health",
        "curl -s https://ai.onreach.inovixora.fr/health",
        "curl -s https://dev.onreach.inovixora.fr/ | head -2",
    ])

    for cmd in cmds:
        print(f"\n>>> {cmd}")
        _, stdout, stderr = client.exec_command(cmd, timeout=180)
        out = stdout.read().decode("ascii", errors="replace")
        err = stderr.read().decode("ascii", errors="replace")
        code = stdout.channel.recv_exit_status()
        sys.stdout.buffer.write(out.encode("ascii", errors="replace"))
        if err.strip():
            sys.stdout.buffer.write(b"\nERR: " + err.encode("ascii", errors="replace"))
        print(f"[exit {code}]")

    client.close()
    return 0


if __name__ == "__main__":
    sys.exit(main())
