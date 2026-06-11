#!/usr/bin/env python3
import os
import sys

import paramiko

PASSWORD = os.environ.get("ONREACH_SSH_PASS", "")


def main() -> int:
    if not PASSWORD:
        print("ONREACH_SSH_PASS required", file=sys.stderr)
        return 1

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect("51.254.196.210", username="root", password=PASSWORD, timeout=30)

    cmds = [
        "curl -v http://127.0.0.1:8202/health 2>&1 | tail -30",
        "docker logs onreach-dev-backend-nginx-1 2>&1 | tail -20",
        "docker logs onreach-dev-backend-1 2>&1 | tail -40",
        "docker exec onreach-dev-backend-1 ls -la /var/www/html/.env 2>&1",
        "docker exec onreach-dev-backend-1 php bin/console debug:router 2>&1 | head -20",
        "docker exec onreach-dev-backend-nginx-1 wget -qO- http://backend:9000 2>&1 | head -5 || true",
        "docker port onreach-dev-backend-nginx-1",
        "ss -tlnp | grep 8202",
        "cat /var/www/vhosts/inovixora.fr/api.onreach.inovixora.fr/conf/vhost_nginx.conf",
        "curl -vk https://api.onreach.inovixora.fr/health 2>&1 | tail -25",
        "curl -vk https://ai.onreach.inovixora.fr/health 2>&1 | tail -15",
    ]

    for cmd in cmds:
        print(f"\n{'='*60}\n{cmd}\n{'='*60}")
        _, stdout, stderr = client.exec_command(cmd, timeout=120)
        out = stdout.read().decode("ascii", errors="replace")
        err = stderr.read().decode("ascii", errors="replace")
        if out:
            sys.stdout.buffer.write(out.rstrip().encode("ascii", errors="replace") + b"\n")
        if err.strip():
            sys.stdout.buffer.write(b"ERR: " + err.rstrip().encode("ascii", errors="replace") + b"\n")

    client.close()
    return 0


if __name__ == "__main__":
    sys.exit(main())
