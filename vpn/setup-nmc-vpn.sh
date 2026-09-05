#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# Northern Metro College Pentest Lab — OpenVPN server setup (run once as root)
# ─────────────────────────────────────────────────────────────────────────────
set -euo pipefail

VPN_DIR="/opt/vpn-nmc"
EASYRSA_DIR="/opt/easyrsa-nmc"
SERVER_IP=$(curl -4 -s ifconfig.me)
VPN_PORT=1195
VPN_SUBNET="10.9.0.0"
VPN_MASK="255.255.255.0"
NMC_LAB_PORT=9100

echo "[nmc-vpn-setup] Server public IP: $SERVER_IP"

# ── 1. Install packages ───────────────────────────────────────────────────────
apt-get update -qq
apt-get install -y -qq openvpn easy-rsa iptables-persistent curl

# ── 2. PKI ────────────────────────────────────────────────────────────────────
make-cadir "$EASYRSA_DIR" 2>/dev/null || true
cd "$EASYRSA_DIR"

cat > vars << 'EOF'
set_var EASYRSA_ALGO   ec
set_var EASYRSA_CURVE  prime256v1
set_var EASYRSA_DN     cn_only
set_var EASYRSA_REQ_CN "NMC-CA"
set_var EASYRSA_CA_EXPIRE  3650
set_var EASYRSA_CERT_EXPIRE 3650
EOF

./easyrsa init-pki 2>/dev/null
echo "NMC-CA" | ./easyrsa build-ca nopass 2>/dev/null
./easyrsa gen-req server nopass 2>/dev/null <<< "server"
echo "yes" | ./easyrsa sign-req server server 2>/dev/null
./easyrsa gen-dh 2>/dev/null
./easyrsa gen-crl 2>/dev/null
openvpn --genkey secret "$EASYRSA_DIR/pki/ta.key"

# ── 3. Server config ──────────────────────────────────────────────────────────
mkdir -p "$VPN_DIR/ccd"

cat > /etc/openvpn/nmc-server.conf << EOF
port $VPN_PORT
proto udp
dev tun1

ca   $EASYRSA_DIR/pki/ca.crt
cert $EASYRSA_DIR/pki/issued/server.crt
key  $EASYRSA_DIR/pki/private/server.key
dh   $EASYRSA_DIR/pki/dh.pem
tls-auth $EASYRSA_DIR/pki/ta.key 0

topology net30
server $VPN_SUBNET $VPN_MASK
ifconfig-pool-persist /var/log/openvpn/nmc-ipp.txt

client-config-dir $VPN_DIR/ccd
crl-verify $EASYRSA_DIR/pki/crl.pem

push "route 10.9.0.0 255.255.255.0"

keepalive 10 120
cipher AES-256-GCM
persist-key
persist-tun
status /var/log/openvpn/nmc-status.log
verb 3
EOF

mkdir -p /var/log/openvpn

# ── 4. IP forwarding (already set by NSD, but ensure) ────────────────────────
echo 1 > /proc/sys/net/ipv4/ip_forward
sysctl -p /etc/sysctl.d/99-openvpn.conf 2>/dev/null || true

# ── 5. Firewall (iptables only — no ufw) ─────────────────────────────────────
# Allow OpenVPN port
iptables -I INPUT -p udp --dport $VPN_PORT -j ACCEPT

# Allow NMC lab port from VPN subnet only
iptables -I INPUT -i tun1 -p tcp --dport $NMC_LAB_PORT -j ACCEPT

# Redirect tun1:80 → 9100 so takers use plain http:// URLs without a port
iptables -t nat -A PREROUTING -i tun1 -p tcp --dport 80 -j REDIRECT --to-port $NMC_LAB_PORT

# Masquerade NMC VPN traffic
iptables -t nat -A POSTROUTING -s 10.9.0.0/24 -j MASQUERADE

netfilter-persistent save 2>/dev/null || iptables-save > /etc/iptables/rules.v4

# ── 6. State directory ────────────────────────────────────────────────────────
mkdir -p "$VPN_DIR/takers"
echo "1" > "$VPN_DIR/takers/.next-index"

# ── 7. Start OpenVPN ──────────────────────────────────────────────────────────
systemctl enable openvpn@nmc-server
systemctl start  openvpn@nmc-server

echo ""
echo "[nmc-vpn-setup] Done."
echo "[nmc-vpn-setup] OpenVPN running on UDP $VPN_PORT"
echo "[nmc-vpn-setup] VPN subnet: $VPN_SUBNET/24 — server is 10.9.0.1"
echo "[nmc-vpn-setup] NMC lab accessible at 10.9.0.1:$NMC_LAB_PORT"
echo ""
echo "[nmc-vpn-setup] Add takers with:"
echo "  bash /opt/nmc-lab/vpn/add-nmc-taker.sh <name>"
