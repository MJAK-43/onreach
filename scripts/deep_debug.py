#!/usr/bin/env python3
import os
import sys

import paramiko

PASSWORD = os.environ.get("ONREACH_SSH_PASS", "")


def main() -> int:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect("51.254.196.210", username="root", password=PASSWORD, timeout=30)

    cmds = [
        "docker logs onreach-dev-backend-1 2>&1 | tail -30",
        "docker exec onreach-dev-backend-1 ps aux",
        "docker exec onreach-dev-backend-1 cat /var/www/html/.env",
        "docker exec onreach-dev-backend-1 php bin/console about 2>&1 | head -15",
        "dig +short dev.onreach.inovixora.fr api.onreach.inovixora.fr ai.onreach.inovixora.fr",
        "grep -r 'proxy_pass' /var/www/vhosts/system/dev.onreach.inovixora.fr/ 2>/dev/null | head -5",
        "grep -r 'proxy_pass' /var/www/vhosts/system/api.onreach.inovixora.fr/ 2>/dev/null | head -5",
        "grep -r 'proxy_pass' /var/www/vhosts/system/ai.onreach.inovixora.fr/ 2>/dev/null | head -5",
        "grep -r '8201\\|8202\\|8203' /var/www/vhosts/system/dev.onreach.inovixora.fr/ 2>/dev/null",
        "grep -r '8201\\|8202\\|8203' /var/www/vhosts/system/api.onreach.inovixora.fr/ 2>/dev/null",
        "ls -la /var/www/vhosts/inovixora.fr/dev.onreach.inovixora.fr/conf/",
        "ls -la /var/www/vhosts/inovixora.fr/api.onreach.inovixora.fr/conf/",
        "plesk bin site -info dev.onreach.inovixora.fr 2>/dev/null | head -20",
        "plesk bin site -info api.onreach.inovixora.fr 2>/dev/null | head -20",
        "curl -s -H 'Host: api.onreach.inovixora.fr' http://127.0.0.1/health -o /dev/null -w '%{http_code}\\n'",
        "curl -s -H 'Host: dev.onreach.inovixora.fr' http://127.0.0.1/ -o /dev/null -w '%{http_code}\\n'",
    ]

    for cmd in cmds:
        print(f"\n{'='*70}\n{cmd}\n{'='*70}")
        _, stdout, stderr = client.exec_command(cmd, timeout=120)
        out = stdout.read().decode("ascii", errors="replace")
        err = stderr.read().decode("ascii", errors="replace")
        sys.stdout.buffer.write(out.encode("ascii", errors="replace"))
        if err.strip():
            sys.stdout.buffer.write(b"\nERR: " + err.encode("ascii", errors="replace"))

    client.close()
    return 0


if __name__ == "__main__":
    if not os.environ.get("ONREACH_SSH_PASS"):
        print("ONREACH_SSH_PASS required", file=sys.stderr)
        sys.exit(1)
    sys.exit(main())
