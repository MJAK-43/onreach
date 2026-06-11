#!/usr/bin/env python3
"""Reset and fix broken git init on DEV server."""
import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect("51.254.196.210", username="root", key_filename=str(__import__("pathlib").Path.home() / ".ssh" / "id_ed25519"), timeout=30)

_, o, e = client.exec_command("rm -rf /opt/onreach/app/.git && echo GIT_REMOVED", timeout=30)
print(o.read().decode(), e.read().decode())
client.close()
