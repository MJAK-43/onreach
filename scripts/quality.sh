#!/usr/bin/env bash
# Contrôles qualité locaux — à lancer avant chaque push vers develop.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "=== Preflight (LF + syntaxe shell) ==="
found=0
while IFS= read -r -d '' file; do
  if grep -q $'\r' "$file"; then
    echo "CRLF: $file"
    found=1
  fi
done < <(find deployments backend/docker -name '*.sh' -print0 2>/dev/null || true)
if [ "$found" -ne 0 ]; then
  echo "Corrigez avec: git add --renormalize ."
  exit 1
fi
for file in deployments/scripts/*.sh backend/docker/entrypoint.sh; do
  [ -f "$file" ] || continue
  bash -n "$file"
done
echo "Preflight OK"

echo "=== Backend ==="
cd "$ROOT/backend"
if [ ! -f .env ]; then cp .env.test .env; fi
composer quality

echo "=== Frontend ==="
cd "$ROOT/frontend"
npm run lint
npm run typecheck
npm run test
npm run build

echo "=== Tous les contrôles sont OK — push autorisé ==="
