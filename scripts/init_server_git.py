#!/usr/bin/env python3
"""Initialize git in /opt/onreach/app on DEV server."""
import paramiko

HOST = "51.254.196.210"
USER = "root"
KEY = __import__("pathlib").Path.home() / ".ssh" / "id_ed25519"
REPO = "https://github.com/MJAK-43/onreach.git"
APP = "/opt/onreach/app"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, username=USER, key_filename=str(KEY), timeout=30)

script = f"""
set -e
if [ -d {APP}/.git ]; then
  cd {APP}
  git checkout develop
  git pull origin develop
  echo ALREADY_GIT
else
  cd {APP}
  git init
  git remote add origin {REPO}
  git fetch origin develop
  git checkout -f develop
  git branch --set-upstream-to=origin/develop develop
  echo GIT_INIT_OK
fi
"""

_, o, e = client.exec_command(script, timeout=120)
print(o.read().decode())
err = e.read().decode()
if err.strip():
    print("ERR:", err)
client.close()
