#!/usr/bin/env python3
"""Upload patched deployment files and restart DEV stack."""
import os
import sys
from pathlib import Path

import paramiko

ROOT = Path(__file__).resolve().parents[1]
HOST = os.environ.get("ONREACH_SSH_HOST", "51.254.196.210")
USER = os.environ.get("ONREACH_SSH_USER", "root")
PASSWORD = os.environ.get("ONREACH_SSH_PASS", "")
REMOTE = "/opt/onreach/app"

FILES = [
    "deployments/docker-compose.dev.yml",
    "deployments/scripts/plesk-proxy-onreach.sh",
    "deployments/scripts/deploy-dev.sh",
    "deployments/scripts/fix-permissions.sh",
    "backend/docker/entrypoint.sh",
    "ai-service/app/config.py",
]


def main() -> int:
    if not PASSWORD:
        print("ONREACH_SSH_PASS required", file=sys.stderr)
        return 1

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, username=USER, password=PASSWORD, timeout=30)
    sftp = client.open_sftp()

    for rel in FILES:
        local = ROOT / rel
        remote = f"{REMOTE}/{rel.replace(chr(92), '/')}"
        print(f"Upload {rel}")
        sftp.put(str(local), remote)

    sftp.close()

    cmds = [
        f"chmod +x {REMOTE}/deployments/scripts/*.sh {REMOTE}/backend/docker/entrypoint.sh",
        f"cd {REMOTE} && docker compose -f deployments/docker-compose.dev.yml --env-file .env.dev up -d --build --remove-orphans",
        f"bash {REMOTE}/deployments/scripts/plesk-proxy-onreach.sh",
        "curl -sf http://127.0.0.1:8202/health",
        "curl -sf http://127.0.0.1:8203/health",
        "curl -sf -o /dev/null -w '%{http_code}' http://127.0.0.1:8201/",
    ]
    for cmd in cmds:
        print(f"$ {cmd}")
        _, stdout, stderr = client.exec_command(cmd, timeout=900)
        out = stdout.read().decode("ascii", errors="replace")
        err = stderr.read().decode("ascii", errors="replace")
        code = stdout.channel.recv_exit_status()
        if out:
            print(out)
        if err:
            print(err, file=sys.stderr)
        if code != 0:
            print(f"Exit {code}", file=sys.stderr)
            client.close()
            return code

    client.close()
    return 0


if __name__ == "__main__":
    sys.exit(main())
