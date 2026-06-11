#!/usr/bin/env python3
"""Validate DEV deployment on remote server."""
import json
import os
import sys

import paramiko

HOST = os.environ.get("ONREACH_SSH_HOST", "51.254.196.210")
USER = os.environ.get("ONREACH_SSH_USER", "root")
PASSWORD = os.environ.get("ONREACH_SSH_PASS", "")


def run(client: paramiko.SSHClient, cmd: str, timeout: int = 120) -> tuple[int, str, str]:
    print(f"\n=== {cmd} ===")
    _, stdout, stderr = client.exec_command(cmd, timeout=timeout)
    out = stdout.read().decode("ascii", errors="replace")
    err = stderr.read().decode("ascii", errors="replace")
    code = stdout.channel.recv_exit_status()
    if out:
        print(out.rstrip())
    if err.strip():
        print("STDERR:", err.rstrip())
    return code, out, err


def main() -> int:
    if not PASSWORD:
        print("ONREACH_SSH_PASS required", file=sys.stderr)
        return 1

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, username=USER, password=PASSWORD, timeout=30)

    cmds = [
        "docker ps --format 'table {{.Names}}\t{{.Status}}\t{{.Ports}}' | grep onreach",
        "curl -sf http://127.0.0.1:8202/health",
        "curl -sf http://127.0.0.1:8203/health",
        "curl -sf -o /dev/null -w 'frontend_local:%{http_code}\\n' http://127.0.0.1:8201/",
        "curl -sf -o /dev/null -w 'dev_https:%{http_code}\\n' https://dev.onreach.inovixora.fr/",
        "curl -sf https://api.onreach.inovixora.fr/health",
        "curl -sf https://ai.onreach.inovixora.fr/health",
        "docker exec onreach-dev-postgres-1 psql -U onreach -d onreach_dev -c 'SELECT 1 AS ok'",
        "docker exec onreach-dev-postgres-1 psql -U onreach -d onreach_dev -c \"SELECT extname FROM pg_extension WHERE extname='vector'\"",
        "docker exec onreach-dev-redis-1 redis-cli -a \"$(grep REDIS_PASSWORD /opt/onreach/app/.env.dev | cut -d= -f2)\" SET onreach_test ok EX 60",
        "docker exec onreach-dev-redis-1 redis-cli -a \"$(grep REDIS_PASSWORD /opt/onreach/app/.env.dev | cut -d= -f2)\" GET onreach_test",
        "docker run --rm --network onreach-dev_default minio/mc alias set local http://minio:9000 \"$(grep MINIO_ROOT_USER /opt/onreach/app/.env.dev | cut -d= -f2)\" \"$(grep MINIO_ROOT_PASSWORD /opt/onreach/app/.env.dev | cut -d= -f2)\" 2>/dev/null; docker run --rm --network onreach-dev_default minio/mc ls local/ 2>/dev/null || echo minio_mc_check",
        "test -f /etc/cron.d/onreach-dev && cat /etc/cron.d/onreach-dev || echo NO_CRON",
        "ls -la /opt/onreach/backups/ 2>/dev/null | tail -5",
        "curl -sf -o /dev/null -w 'grafana:%{http_code}\\n' http://127.0.0.1:3001/login",
        "for d in dev.onreach.inovixora.fr api.onreach.inovixora.fr ai.onreach.inovixora.fr; do echo \"--- $d ---\"; ls -la /var/www/vhosts/inovixora.fr/$d/conf/vhost_nginx.conf 2>/dev/null || echo missing; done",
    ]

    results: dict[str, int] = {}
    for cmd in cmds:
        code, _, _ = run(client, cmd)
        results[cmd.split()[0] if cmd.startswith("docker") else cmd[:40]] = code

    client.close()
    print("\n=== SUMMARY ===")
    print(json.dumps(results, indent=2))
    return 0


if __name__ == "__main__":
    sys.exit(main())
