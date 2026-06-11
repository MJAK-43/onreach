#!/usr/bin/env python3
"""Utility SSH remote command runner (credentials via env ONREACH_SSH_PASS)."""
import os
import sys

import paramiko

HOST = os.environ.get("ONREACH_SSH_HOST", "51.254.196.210")
USER = os.environ.get("ONREACH_SSH_USER", "root")
PASSWORD = os.environ.get("ONREACH_SSH_PASS", "")


def run_commands(commands: list[str]) -> int:
    if not PASSWORD:
        print("ONREACH_SSH_PASS required", file=sys.stderr)
        return 1

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, username=USER, password=PASSWORD, timeout=30)

    for cmd in commands:
        print(f"=== {cmd} ===")
        _, stdout, stderr = client.exec_command(cmd, timeout=120)
        out = stdout.read().decode()
        err = stderr.read().decode()
        if out:
            print(out)
        if err.strip():
            print("STDERR:", err)
        print()

    client.close()
    return 0


if __name__ == "__main__":
    cmds = sys.argv[1:] if len(sys.argv) > 1 else [
        "uname -a",
        "cat /etc/os-release | head -5",
        "free -h",
        "nproc",
        "df -h",
        "docker --version 2>/dev/null || echo NO_DOCKER",
        "docker compose version 2>/dev/null || echo NO_COMPOSE",
        "ss -tlnp | head -25",
        "docker ps --format 'table {{.Names}}\t{{.Status}}\t{{.Ports}}' 2>/dev/null | head -25",
        "ls -la /opt/ 2>/dev/null",
    ]
    sys.exit(run_commands(cmds))
