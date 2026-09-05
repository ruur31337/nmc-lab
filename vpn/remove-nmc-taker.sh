#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# Northern Metro College Pentest Lab — remove an exam taker
# Usage: bash remove-nmc-taker.sh <name>
# ─────────────────────────────────────────────────────────────────────────────
set -euo pipefail

NAME="${1:-}"
if [[ -z "$NAME" ]]; then
  echo "Usage: $0 <taker-name>"
  exit 1
fi

VPN_DIR="/opt/vpn-nmc"
EASYRSA_DIR="/opt/easyrsa-nmc"
LAB_DIR="/opt/nmc-lab"
TAKER_DIR="$VPN_DIR/takers/$NAME"

if [[ ! -d "$TAKER_DIR" ]]; then
  echo "[remove-nmc-taker] ERROR: taker '$NAME' not found."
  exit 1
fi

VPN_IP=$(cat "$TAKER_DIR/vpn-ip")
LAB_PORT=$(cat "$TAKER_DIR/lab-port")

echo "[remove-nmc-taker] Removing taker: $NAME ($VPN_IP, port $LAB_PORT)"

# ── Revoke cert ───────────────────────────────────────────────────────────────
cd "$EASYRSA_DIR"
echo "yes" | ./easyrsa revoke "$NAME" 2>/dev/null || true
./easyrsa gen-crl 2>/dev/null || true

# ── Remove ccd entry ──────────────────────────────────────────────────────────
rm -f "$VPN_DIR/ccd/$NAME"

# ── Stop and remove lab stack + volumes ───────────────────────────────────────
cd "$LAB_DIR"
LAB_PORT=$LAB_PORT docker compose -f "$LAB_DIR/docker-compose.yml" -p "$NAME" down -v 2>/dev/null || true
echo "[remove-nmc-taker] Lab stack stopped and volumes removed."

# ── Remove iptables rules ─────────────────────────────────────────────────────
iptables -t nat -D PREROUTING -i tun1 -s "$VPN_IP" -p tcp --dport 80 -j REDIRECT --to-ports "$LAB_PORT" 2>/dev/null || true
iptables -D INPUT -i tun1 -s "$VPN_IP" -p tcp --dport "$LAB_PORT" -j ACCEPT 2>/dev/null || true
iptables-save > /etc/iptables/rules.v4
echo "[remove-nmc-taker] iptables rules removed."

# ── Remove taker directory ────────────────────────────────────────────────────
rm -rf "$TAKER_DIR"

echo "[remove-nmc-taker] Done. $NAME removed."
