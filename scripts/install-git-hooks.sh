#!/usr/bin/env bash
# Installe un hook pre-push qui lance les contrôles qualité avant push vers develop/main.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HOOK="${ROOT}/.git/hooks/pre-push"

cat > "$HOOK" <<'EOF'
#!/usr/bin/env bash
set -euo pipefail

while read -r local_ref local_sha remote_ref remote_sha; do
  case "$remote_ref" in
    refs/heads/develop|refs/heads/main|refs/heads/preprod)
      echo "pre-push: contrôles qualité obligatoires pour ${remote_ref#refs/heads/}..."
      exec "$(git rev-parse --show-toplevel)/scripts/quality.sh"
      ;;
  esac
done
EOF

chmod +x "$HOOK"
echo "Hook pre-push installé: $HOOK"
