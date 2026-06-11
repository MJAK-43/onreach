#!/usr/bin/env python3
"""Deploy On'Reach DEV to remote server via SFTP + SSH."""
from __future__ import annotations

import os
import secrets
import sys
import tarfile
import tempfile
from pathlib import Path

import paramiko

ROOT = Path(__file__).resolve().parents[1]
HOST = os.environ.get("ONREACH_SSH_HOST", "51.254.196.210")
USER = os.environ.get("ONREACH_SSH_USER", "root")
PASSWORD = os.environ.get("ONREACH_SSH_PASS", "")
REMOTE_APP = "/opt/onreach/app"

EXCLUDE_DIRS = {
    "node_modules",
    "vendor",
    "var",
    ".git",
    "dist",
    ".phpunit.cache",
    "__pycache__",
    ".pytest_cache",
    ".vite",
}


def generate_env_dev() -> str:
    def token(n: int = 32) -> str:
        return secrets.token_urlsafe(n)

    return f"""# Generated — On'Reach DEV
APP_ENV=prod
APP_SECRET={token(48)}
POSTGRES_DB=onreach_dev
POSTGRES_USER=onreach_dev
POSTGRES_PASSWORD={token(24)}
REDIS_URL=redis://redis:6379
MINIO_ROOT_USER=onreach_dev
MINIO_ROOT_PASSWORD={token(24)}
JWT_PASSPHRASE={token(24)}
CORS_ALLOW_ORIGIN='^https://dev\\.onreach\\.inovixora\\.fr$$'
DEFAULT_URI=https://api.onreach.inovixora.fr
MESSENGER_TRANSPORT_DSN=redis://redis:6379/messages
OPENAI_API_KEY=
GRAFANA_ADMIN_USER=admin
GRAFANA_ADMIN_PASSWORD={token(20)}
VITE_API_URL=https://api.onreach.inovixora.fr
OTEL_EXPORTER_OTLP_ENDPOINT=http://otel-collector:4317
"""


def create_archive() -> Path:
    tmp = tempfile.NamedTemporaryFile(suffix=".tar.gz", delete=False)
    tmp.close()
    archive = Path(tmp.name)
    with tarfile.open(archive, "w:gz") as tar:
        for path in ROOT.rglob("*"):
            if not path.is_file():
                continue
            rel = path.relative_to(ROOT)
            if any(part in EXCLUDE_DIRS for part in rel.parts):
                continue
            if rel.name == ".env" or rel.name.endswith(".pem"):
                continue
            tar.add(path, arcname=str(rel).replace("\\", "/"))
    return archive


def run_ssh(client: paramiko.SSHClient, cmd: str, timeout: int = 3600) -> tuple[int, str, str]:
    print(f"$ {cmd}")
    _, stdout, stderr = client.exec_command(cmd, timeout=timeout)
    out = stdout.read().decode()
    err = stderr.read().decode()
    code = stdout.channel.recv_exit_status()
    if out:
        print(out.encode("ascii", errors="replace").decode("ascii"))
    if err:
        print(err.encode("ascii", errors="replace").decode("ascii"), file=sys.stderr)
    return code, out, err


def main() -> int:
    if not PASSWORD:
        print("ONREACH_SSH_PASS required", file=sys.stderr)
        return 1

    print("Creating archive...")
    archive = create_archive()

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, username=USER, password=PASSWORD, timeout=30)
    sftp = client.open_sftp()

    print("Preparing server directories...")
    run_ssh(
        client,
        "mkdir -p /opt/onreach/{app,logs,backups,docker/{postgres,redis,minio,prometheus,loki,grafana},deployments} "
        "&& chmod 700 /opt/onreach/backups",
    )

    print("Uploading application...")
    sftp.put(str(archive), "/tmp/onreach-deploy.tar.gz")
    archive.unlink(missing_ok=True)

    run_ssh(client, f"rm -rf {REMOTE_APP}/* && tar -xzf /tmp/onreach-deploy.tar.gz -C {REMOTE_APP} && rm /tmp/onreach-deploy.tar.gz")

    env_content = generate_env_dev()
    env_path = Path(tempfile.gettempdir()) / "onreach.env.dev"
    env_path.write_text(env_content, encoding="utf-8")
    sftp.put(str(env_path), f"{REMOTE_APP}/.env.dev")
    env_path.unlink(missing_ok=True)
    run_ssh(client, f"chmod 600 {REMOTE_APP}/.env.dev")

    run_ssh(client, f"chmod +x {REMOTE_APP}/deployments/scripts/*.sh {REMOTE_APP}/backend/docker/entrypoint.sh")

    print("Building and starting Docker stack (may take several minutes)...")
    code, _, _ = run_ssh(
        client,
        f"cd {REMOTE_APP} && bash deployments/scripts/deploy-dev.sh",
        timeout=3600,
    )

    print("Installing backup cron...")
    cron_line = "0 2 * * * root /opt/onreach/app/deployments/scripts/backup-dev.sh >> /opt/onreach/logs/backup.log 2>&1"
    run_ssh(client, f"grep -q backup-dev.sh /etc/cron.d/onreach-dev 2>/dev/null || echo '{cron_line}' > /etc/cron.d/onreach-dev")

    print("Validation...")
    run_ssh(client, "docker compose -f /opt/onreach/app/deployments/docker-compose.dev.yml --env-file /opt/onreach/app/.env.dev ps")

    client.close()
    sftp.close()
    return code


if __name__ == "__main__":
    sys.exit(main())
