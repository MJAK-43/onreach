#!/usr/bin/env python3
"""Remove expired token from git remote and hard-sync develop."""
import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(
    "51.254.196.210",
    username="root",
    key_filename=str(__import__("pathlib").Path.home() / ".ssh" / "id_ed25519"),
    timeout=30,
)

script = """
set -e
cd /opt/onreach/app
git remote set-url origin https://github.com/MJAK-43/onreach.git
git checkout -f develop 2>/dev/null || true
git reset --hard HEAD
git clean -fd -e .env.dev
echo REMOTE_CLEANED
git remote -v | sed 's/x-access-token:[^@]*@/x-access-token:***@/g'
"""

_, o, e = client.exec_command(script, timeout=60)
print(o.read().decode("ascii", errors="replace"))
err = e.read().decode("ascii", errors="replace")
if err.strip():
    print(err)
client.close()
