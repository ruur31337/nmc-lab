#!/bin/bash
# deploy.sh — Build and start the registrar app + DB
# Run from: /home/leonel/school/registrar/
set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

NETWORK=nmc-school
DB_CONTAINER=nmc-registrar-db
APP_CONTAINER=nmc-registrar
DB_IMAGE=mysql:8.0
APP_IMAGE=nmc-registrar

# ── 1. MySQL DB ─────────────────────────────────────────────────────────────
echo "[1/5] Starting MySQL container..."
docker rm -f "$DB_CONTAINER" 2>/dev/null || true
docker run -d \
  --name "$DB_CONTAINER" \
  --network "$NETWORK" \
  -e MYSQL_ROOT_PASSWORD=nmcroot2025 \
  -e MYSQL_DATABASE=nmc_registrar \
  -e MYSQL_USER=reguser \
  -e MYSQL_PASSWORD=regpass2025 \
  "$DB_IMAGE"

# ── 2. Wait for MySQL to be ready ───────────────────────────────────────────
echo "[2/5] Waiting for MySQL to accept connections..."
for i in $(seq 1 30); do
  if docker exec "$DB_CONTAINER" mysqladmin ping -u root -pnmcroot2025 --silent 2>/dev/null; then
    echo "    MySQL ready."
    break
  fi
  echo "    Waiting... ($i/30)"
  sleep 2
done

# ── 3. Import schema + seed ─────────────────────────────────────────────────
echo "[3/5] Importing schema and seed data..."
docker exec -i "$DB_CONTAINER" mysql -u root -pnmcroot2025 nmc_registrar < sql/schema.sql
docker exec -i "$DB_CONTAINER" mysql -u root -pnmcroot2025 nmc_registrar < sql/seed.sql
echo "    Done."

# ── 4. Build registrar PHP image ────────────────────────────────────────────
echo "[4/5] Building registrar PHP image..."
docker build -t "$APP_IMAGE" .

# ── 5. Start registrar app container ────────────────────────────────────────
echo "[5/5] Starting registrar app..."
docker rm -f "$APP_CONTAINER" 2>/dev/null || true
docker run -d \
  --name "$APP_CONTAINER" \
  --network "$NETWORK" \
  -p 5400:80 \
  -e DB_HOST="$DB_CONTAINER" \
  -e DB_NAME=nmc_registrar \
  -e DB_USER=reguser \
  -e DB_PASS=regpass2025 \
  "$APP_IMAGE"

# ── Reload nginx ─────────────────────────────────────────────────────────────
echo "[+] Reloading nginx..."
docker exec nmc-nginx nginx -s reload 2>/dev/null || true

echo ""
echo "=== Registrar portal ready ==="
echo "  Direct:  http://localhost:5400"
echo "  Via nginx: http://registrar.nmc.local  (add to /etc/hosts if needed)"
echo ""
echo "Student login:  2021-00042 / nmc@2025"
echo "Staff login:    dmercado   / registrar2025"
echo "Staff login:    acruz      / staff2025"
