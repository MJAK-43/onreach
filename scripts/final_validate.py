#!/usr/bin/env python3
import os
import sys

import paramiko

PASSWORD = os.environ.get("ONREACH_SSH_PASS", "")


def run(client, cmd, timeout=120):
    print(f"\n>>> {cmd}")
    _, stdout, stderr = client.exec_command(cmd, timeout=timeout)
    out = stdout.read().decode("ascii", errors="replace")
    err = stderr.read().decode("ascii", errors="replace")
    code = stdout.channel.recv_exit_status()
    sys.stdout.buffer.write(out.encode("ascii", errors="replace"))
    if err.strip():
        sys.stdout.buffer.write(b"\nSTDERR: " + err.encode("ascii", errors="replace"))
    print(f"\n[exit {code}]")
    return code, out


def main() -> int:
    if not PASSWORD:
        print("ONREACH_SSH_PASS required", file=sys.stderr)
        return 1

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect("51.254.196.210", username="root", password=PASSWORD, timeout=30)

    cmds = [
        "sleep 10",
        "curl -s -o /dev/null -w 'backend_local:%{http_code}\\n' http://127.0.0.1:8202/health",
        "curl -s http://127.0.0.1:8202/health",
        "curl -s -o /dev/null -w 'ai_local:%{http_code}\\n' http://127.0.0.1:8203/health",
        "curl -s http://127.0.0.1:8203/health",
        "curl -s -o /dev/null -w 'dev_https:%{http_code}\\n' https://dev.onreach.inovixora.fr/",
        "curl -s -o /dev/null -w 'api_https:%{http_code}\\n' https://api.onreach.inovixora.fr/health",
        "curl -s https://api.onreach.inovixora.fr/health",
        "curl -s -o /dev/null -w 'ai_https:%{http_code}\\n' https://ai.onreach.inovixora.fr/health",
        "curl -s https://ai.onreach.inovixora.fr/health",
        "docker exec onreach-dev-postgres-1 psql -U onreach_dev -d onreach_dev -c 'SELECT 1 AS ok'",
        "docker exec onreach-dev-postgres-1 psql -U onreach_dev -d onreach_dev -c \"SELECT extname FROM pg_extension WHERE extname='vector'\"",
        "docker exec onreach-dev-redis-1 redis-cli PING",
        "docker exec onreach-dev-redis-1 redis-cli SET onreach_ping ok EX 30 && docker exec onreach-dev-redis-1 redis-cli GET onreach_ping",
        "docker run --rm --network onreach_dev minio/mc:latest alias set local http://minio:9000 $(grep MINIO_ROOT_USER /opt/onreach/app/.env.dev | cut -d= -f2) $(grep MINIO_ROOT_PASSWORD /opt/onreach/app/.env.dev | cut -d= -f2) && docker run --rm --network onreach_dev minio/mc:latest ls local/",
        "curl -s -o /dev/null -w 'grafana:%{http_code}\\n' http://127.0.0.1:3001/login",
        "docker ps --format '{{.Names}} {{.Status}}' | grep onreach | sort",
        "grep -q onreach-dev /etc/cron.d/onreach-dev 2>/dev/null && cat /etc/cron.d/onreach-dev || echo 'CRON_NOT_SET'",
    ]

    for cmd in cmds:
        run(client, cmd)

    client.close()
    return 0


if __name__ == "__main__":
    sys.exit(main())
