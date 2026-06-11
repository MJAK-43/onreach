#!/usr/bin/env python3
"""Add local id_ed25519.pub to server ~/.ssh/authorized_keys."""
import os
import sys
from pathlib import Path

import paramiko

HOST = os.environ.get("ONREACH_SSH_HOST", "51.254.196.210")
USER = os.environ.get("ONREACH_SSH_USER", "root")
PASSWORD = os.environ.get("ONREACH_SSH_PASS", "")

PUB_KEY_PATH = Path.home() / ".ssh" / "id_ed25519.pub"


def main() -> int:
    if not PASSWORD:
        print("ONREACH_SSH_PASS required", file=sys.stderr)
        return 1
    if not PUB_KEY_PATH.is_file():
        print(f"Missing {PUB_KEY_PATH}", file=sys.stderr)
        return 1

    pub_key = PUB_KEY_PATH.read_text(encoding="utf-8").strip()
    if not pub_key:
        print("Empty public key file", file=sys.stderr)
        return 1

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, username=USER, password=PASSWORD, timeout=30)

    cmds = [
        "mkdir -p ~/.ssh && chmod 700 ~/.ssh",
        f"touch ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys",
        f"grep -qxF '{pub_key}' ~/.ssh/authorized_keys || echo '{pub_key}' >> ~/.ssh/authorized_keys",
        "wc -l ~/.ssh/authorized_keys",
        "tail -1 ~/.ssh/authorized_keys",
    ]

    for cmd in cmds:
        _, stdout, stderr = client.exec_command(cmd, timeout=30)
        out = stdout.read().decode("ascii", errors="replace").strip()
        err = stderr.read().decode("ascii", errors="replace").strip()
        code = stdout.channel.recv_exit_status()
        if code != 0:
            print(f"Failed: {cmd}\n{err}", file=sys.stderr)
            client.close()
            return code
        if cmd.startswith("wc") or cmd.startswith("tail"):
            print(out)

    client.close()
    print(f"OK: key added for {USER}@{HOST}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
