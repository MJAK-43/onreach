#!/usr/bin/env python3
import os
import sys
from pathlib import Path

import paramiko

ROOT = Path(__file__).resolve().parents[1]
PASSWORD = os.environ.get("ONREACH_SSH_PASS", "")


def run(client, cmd, timeout=180):
    print(f"\n=== {cmd} ===")
    _, stdout, stderr = client.exec_command(cmd, timeout=timeout)
    out = stdout.read().decode("ascii", errors="replace")
    err = stderr.read().decode("ascii", errors="replace")
    code = stdout.channel.recv_exit_status()
    sys.stdout.buffer.write(out.encode("ascii", errors="replace"))
    if err.strip():
        sys.stdout.buffer.write(b"\nERR: " + err.encode("ascii", errors="replace"))
    sys.stdout.buffer.write(f"\nexit {code}\n".encode())
    return code


def main() -> int:
    if not PASSWORD:
        print("ONREACH_SSH_PASS required", file=sys.stderr)
        return 1

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect("51.254.196.210", username="root", password=PASSWORD, timeout=30)

    sftp = client.open_sftp()
    for rel in [
        "backend/docker/entrypoint.sh",
        "deployments/docker-compose.dev.yml",
        "deployments/scripts/plesk-proxy-onreach.sh",
    ]:
        sftp.put(str(ROOT / rel), f"/opt/onreach/app/{rel}")
    sftp.close()

    cmds = [
        "grep -q '^DEFAULT_URI=' /opt/onreach/app/.env.dev || echo 'DEFAULT_URI=https://api.onreach.inovixora.fr' >> /opt/onreach/app/.env.dev",
        "chmod +x /opt/onreach/app/backend/docker/entrypoint.sh",
        "docker exec onreach-dev-backend-1 rm -f /var/www/html/.env",
        "cd /opt/onreach/app && docker compose -f deployments/docker-compose.dev.yml --env-file .env.dev up -d --build backend backend-nginx",
        "sleep 5",
        "curl -sf http://127.0.0.1:8202/health || curl -s http://127.0.0.1:8202/health | head -5",
        "for d in dev api ai; do echo \"--- $d.onreach.inovixora.fr ---\"; plesk bin subdomain --info ${d}.onreach.inovixora.fr 2>/dev/null | grep -E 'WWW Root|Hosting|nginx' || true; done",
        "for d in dev api ai; do echo \"--- nginx $d ---\"; cat /var/www/vhosts/system/${d}.onreach.inovixora.fr/conf/nginx.conf 2>/dev/null | head -5 || cat /var/www/vhosts/inovixora.fr/${d}.onreach.inovixora.fr/conf/vhost_nginx.conf 2>/dev/null; done",
        "bash /opt/onreach/app/deployments/scripts/plesk-proxy-onreach.sh",
        "nginx -t 2>&1",
        "systemctl reload nginx 2>&1 || service nginx reload 2>&1",
        "curl -sf https://api.onreach.inovixora.fr/health || echo api_fail",
        "curl -sf https://ai.onreach.inovixora.fr/health || echo ai_fail",
        "curl -sf -o /dev/null -w 'dev:%{http_code} api:%{http_code} ai:%{http_code}\\n' https://dev.onreach.inovixora.fr/ https://api.onreach.inovixora.fr/health https://ai.onreach.inovixora.fr/health",
    ]

    for cmd in cmds:
        run(client, cmd)

    client.close()
    return 0


if __name__ == "__main__":
    sys.exit(main())
