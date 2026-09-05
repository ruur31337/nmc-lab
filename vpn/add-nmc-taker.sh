#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# Northern Metro College Pentest Lab — add an exam taker (per-taker stack)
# Usage: bash add-nmc-taker.sh <name>
# ─────────────────────────────────────────────────────────────────────────────
set -euo pipefail

NAME="${1:-}"
if [[ -z "$NAME" ]]; then
  echo "Usage: $0 <taker-name>"
  exit 1
fi

if [[ ! "$NAME" =~ ^[a-zA-Z0-9_-]+$ ]]; then
  echo "[add-nmc-taker] ERROR: invalid name '$NAME'. Letters, numbers, hyphens, underscores only."
  exit 1
fi

VPN_DIR="/opt/vpn-nmc"
EASYRSA_DIR="/opt/easyrsa-nmc"
LAB_DIR="/opt/nmc-lab"
TAKER_DIR="$VPN_DIR/takers/$NAME"
SERVER_IP=$(curl -4 -s ifconfig.me)

if [[ -d "$TAKER_DIR" ]]; then
  echo "[add-nmc-taker] ERROR: taker '$NAME' already exists. Run remove-nmc-taker.sh first."
  exit 1
fi

# ── Assign index, VPN IP, and lab port ───────────────────────────────────────
IDX=$(cat "$VPN_DIR/takers/.next-index")
echo $((IDX + 1)) > "$VPN_DIR/takers/.next-index"

VPN_IP="10.9.0.$((IDX * 4 + 2))"
VPN_PEER="10.9.0.$((IDX * 4 + 1))"
LAB_PORT=$((9200 + IDX))

mkdir -p "$TAKER_DIR"
echo "$VPN_IP"   > "$TAKER_DIR/vpn-ip"
echo "$VPN_PEER" > "$TAKER_DIR/vpn-peer"
echo "$LAB_PORT" > "$TAKER_DIR/lab-port"

echo "[add-nmc-taker] Name:    $NAME"
echo "[add-nmc-taker] VPN IP:  $VPN_IP (peer: $VPN_PEER)"
echo "[add-nmc-taker] Port:    $LAB_PORT"

# ── Generate client cert ──────────────────────────────────────────────────────
cd "$EASYRSA_DIR"
rm -f "$EASYRSA_DIR/pki/reqs/$NAME.req"
rm -f "$EASYRSA_DIR/pki/private/$NAME.key"
rm -f "$EASYRSA_DIR/pki/issued/$NAME.crt"
rm -f "$EASYRSA_DIR/pki/inline/private/$NAME.inline"
./easyrsa gen-req "$NAME" nopass 2>/dev/null <<< "$NAME"
echo "yes" | ./easyrsa sign-req client "$NAME" 2>/dev/null

# ── Fixed VPN IP via ccd ──────────────────────────────────────────────────────
echo "ifconfig-push $VPN_IP $VPN_PEER" > "$VPN_DIR/ccd/$NAME"

# ── Generate .ovpn ────────────────────────────────────────────────────────────
OVPN="$TAKER_DIR/$NAME.ovpn"
cat > "$OVPN" << OVPNEOF
client
dev tun
proto udp
remote $SERVER_IP 1195
resolv-retry infinite
nobind
persist-key
persist-tun
remote-cert-tls server
cipher AES-256-GCM
verb 3
key-direction 1

<ca>
$(cat "$EASYRSA_DIR/pki/ca.crt")
</ca>
<cert>
$(openssl x509 -in "$EASYRSA_DIR/pki/issued/$NAME.crt")
</cert>
<key>
$(cat "$EASYRSA_DIR/pki/private/$NAME.key")
</key>
<tls-auth>
$(cat "$EASYRSA_DIR/pki/ta.key")
</tls-auth>
OVPNEOF

# ── Start per-taker NMC lab stack ─────────────────────────────────────────────
echo "[add-nmc-taker] Starting NMC lab stack for $NAME on port $LAB_PORT..."
cd "$LAB_DIR"

# Start DB + app containers first (no nginx yet — nginx depends on all being ready)
LAB_PORT=$LAB_PORT docker compose -f "$LAB_DIR/docker-compose.yml" -p "$NAME" \
  up -d --no-deps registrar-db academy admission main-web

# Wait for registrar-db to be healthy before starting registrar + nginx
echo "[add-nmc-taker] Waiting for registrar-db to be healthy..."
for i in $(seq 1 24); do
  STATUS=$(docker inspect --format='{{.State.Health.Status}}' "${NAME}-registrar-db-1" 2>/dev/null || echo "missing")
  if [[ "$STATUS" == "healthy" ]]; then
    echo "[add-nmc-taker] registrar-db is healthy."
    break
  fi
  echo "[add-nmc-taker] ($i/24) registrar-db status: $STATUS — waiting 5s..."
  sleep 5
done

# Bring up everything (registrar, registrar-bot, nginx)
LAB_PORT=$LAB_PORT docker compose -f "$LAB_DIR/docker-compose.yml" -p "$NAME" up -d
echo "[add-nmc-taker] Stack started."

# ── iptables: route taker VPN IP:80 → their lab port ─────────────────────────
iptables -t nat -I PREROUTING 1 -i tun1 -s "$VPN_IP" -p tcp --dport 80 -j REDIRECT --to-ports "$LAB_PORT"
# REDIRECT sends packets to INPUT — must explicitly allow the lab port
iptables -I INPUT -i tun1 -s "$VPN_IP" -p tcp --dport "$LAB_PORT" -j ACCEPT
iptables-save > /etc/iptables/rules.v4
echo "[add-nmc-taker] iptables: $VPN_IP:80 → localhost:$LAB_PORT (INPUT ACCEPT added)"

echo ""
echo "══════════════════════════════════════════════════════"
echo " Taker:  $NAME"
echo " .ovpn:  $OVPN"
echo ""
echo " Step 1 — Add to /etc/hosts on taker machine:"
echo "   10.9.0.1  www.nmc.local"
echo "   10.9.0.1  academy.nmc.local"
echo "   10.9.0.1  admission.nmc.local"
echo "   10.9.0.1  registrar.nmc.local"
echo ""
echo " Step 2 — Connect:"
echo "   sudo openvpn $NAME.ovpn"
echo "══════════════════════════════════════════════════════"
