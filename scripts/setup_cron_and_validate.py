#!/usr/bin/env python3
import os
import sys
from pathlib import Path

import paramiko

ROOT = Path(__file__).resolve().parents[1]

c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect("51.254.196.210", username="root", password=os.environ["ONREACH_SSH_PASS"], timeout=30)

sftp = c.open_sftp()
sftp.put(
    str(ROOT / "deployments/scripts/plesk-proxy-onreach.sh"),
    "/opt/onreach/app/deployments/scripts/plesk-proxy-onreach.sh",
)
sftp.put(
    str(ROOT / "deployments/scripts/backup-dev.sh"),
    "/opt/onreach/app/deployments/scripts/backup-dev.sh",
)
sftp.close()

cron = """SHELL=/bin/bash
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin
0 2 * * * root /opt/onreach/app/deployments/scripts/backup-dev.sh >> /opt/onreach/logs/backup-dev.log 2>&1
"""

cmds = [
    "chmod +x /opt/onreach/app/deployments/scripts/plesk-proxy-onreach.sh /opt/onreach/app/deployments/scripts/backup-dev.sh",
    f"cat > /etc/cron.d/onreach-dev <<'EOF'\n{cron}EOF",
    "chmod 644 /etc/cron.d/onreach-dev",
    "docker ps --format '{{.Names}} {{.Status}}' | grep onreach | sort",
    "curl -sf https://dev.onreach.inovixora.fr/ | grep -i \"On'Reach\"",
    "curl -sf https://api.onreach.inovixora.fr/health",
    "curl -sf https://ai.onreach.inovixora.fr/health",
    "docker exec onreach-dev-postgres-1 psql -U onreach_dev -d onreach_dev -c \"SELECT extname FROM pg_extension WHERE extname='vector'\"",
    "docker exec onreach-dev-redis-1 redis-cli SET onreach_validate ok EX 60 && docker exec onreach-dev-redis-1 redis-cli GET onreach_validate",
    "docker exec onreach-dev-minio-1 mc alias set local http://127.0.0.1:9000 $(grep ^MINIO_ROOT_USER= /opt/onreach/app/.env.dev | cut -d= -f2-) $(grep ^MINIO_ROOT_PASSWORD= /opt/onreach/app/.env.dev | cut -d= -f2-) && docker exec onreach-dev-minio-1 mc ls local/",
    "curl -sf -o /dev/null -w 'grafana:%{http_code}\\n' http://127.0.0.1:3001/login",
    "fail2ban-client status sshd 2>/dev/null | head -3 || echo fail2ban_ok",
    "cat /etc/cron.d/onreach-dev",
]

for cmd in cmds:
    print(f"\n>>> {cmd}")
    _, o, e = c.exec_command(cmd, timeout=180)
    out = o.read().decode("ascii", errors="replace")
    err = e.read().decode("ascii", errors="replace")
    code = o.channel.recv_exit_status()
    sys.stdout.buffer.write(out.encode("ascii", errors="replace"))
    if err.strip() and code != 0:
        sys.stdout.buffer.write(b"\nERR: " + err.encode("ascii", errors="replace"))
    print(f"[exit {code}]")

c.close()
