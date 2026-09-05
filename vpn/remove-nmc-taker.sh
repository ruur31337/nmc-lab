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
TAKER_DIR="$VPN_DIR/takers/$NAME"

if [[ ! -d "$TAKER_DIR" ]]; then
  echo "[remove-nmc-taker] ERROR: taker '$NAME' not found."
  exit 1
fi

echo "[remove-nmc-taker] Removing taker: $NAME"

# ── Revoke cert ───────────────────────────────────────────────────────────────
cd "$EASYRSA_DIR"
echo "yes" | ./easyrsa revoke "$NAME" 2>/dev/null || true
./easyrsa gen-crl 2>/dev/null || true

# ── Remove ccd entry ──────────────────────────────────────────────────────────
rm -f "$VPN_DIR/ccd/$NAME"

# ── Remove taker directory ────────────────────────────────────────────────────
rm -rf "$TAKER_DIR"

echo "[remove-nmc-taker] Done. $NAME removed."
