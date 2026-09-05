#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# Northern Metro College Pentest Lab — VM provisioning script
# Runs as root inside the freshly installed Ubuntu 22.04 VM via Packer.
# Builds all Docker images from GitHub source — no DockerHub dependency.
# ─────────────────────────────────────────────────────────────────────────────
set -euo pipefail

LAB_IP="${LAB_IP:-192.168.111.101}"
LAB_DIR="/opt/nmc-lab"
GITHUB_REPO="https://github.com/ruur31337/nmc-lab.git"

echo "==> [nmc] Starting lab provisioning (lab IP: $LAB_IP)"

# ── 0. Force DNS (VMware NAT sometimes doesn't pass DNS during provisioning) ──
echo "nameserver 8.8.8.8" > /etc/resolv.conf
echo "nameserver 8.8.4.4" >> /etc/resolv.conf

# ── 1. System update ──────────────────────────────────────────────────────────
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get upgrade -y -qq
apt-get install -y -qq \
  ca-certificates curl gnupg lsb-release \
  net-tools iproute2 htop vim ufw git

# ── 2. Install Docker CE ──────────────────────────────────────────────────────
echo "==> [nmc] Installing Docker CE..."
install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg \
  | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
chmod a+r /etc/apt/keyrings/docker.gpg

echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
  https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" \
  > /etc/apt/sources.list.d/docker.list

apt-get update -qq
apt-get install -y -qq \
  docker-ce docker-ce-cli containerd.io \
  docker-buildx-plugin docker-compose-plugin

systemctl enable docker
systemctl start docker

usermod -aG docker lab

# ── 3. Clone repo and build images from source ────────────────────────────────
echo "==> [nmc] Cloning NMC lab from GitHub..."
git clone "$GITHUB_REPO" "$LAB_DIR"

# Lock down lab directory — RCE from web cannot read compose file
chown -R root:lab "$LAB_DIR"
chmod 750 "$LAB_DIR"
chmod 640 "$LAB_DIR/docker-compose.yml"

cd "$LAB_DIR"

# Patch docker-compose.yml: expose port 80 directly (no VPN routing on HTB)
sed -i 's|"${LAB_PORT:-9100}:80"|"80:80"|' docker-compose.yml

echo "==> [nmc] Building Docker images from source (this takes ~10 min)..."
docker compose build

# ── 4. Systemd service — auto-start on boot ───────────────────────────────────
echo "==> [nmc] Creating systemd service..."
cat > /etc/systemd/system/nmc-lab.service << 'EOF'
[Unit]
Description=Northern Metro College Pentest Lab
After=docker.service network-online.target
Wants=network-online.target
Requires=docker.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=/opt/nmc-lab
ExecStart=/usr/bin/docker compose up -d --remove-orphans
ExecStop=/usr/bin/docker compose down
TimeoutStartSec=300

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable nmc-lab.service

# ── 5. /etc/hosts — resolve NMC domains to localhost inside the VM ────────────
echo "==> [nmc] Configuring /etc/hosts..."
cat >> /etc/hosts << 'EOF'

# Northern Metro College Pentest Lab
127.0.0.1 www.nmc.local
127.0.0.1 academy.nmc.local
127.0.0.1 admission.nmc.local
127.0.0.1 registrar.nmc.local
EOF

# ── 6. Detect NICs ────────────────────────────────────────────────────────────
echo "==> [nmc] Detecting network interfaces..."

NAT_NIC=""
for iface in $(ls /sys/class/net | grep -v lo | sort); do
  if ip addr show "$iface" | grep -q "inet "; then
    NAT_NIC="$iface"
    break
  fi
done

HOSTONLY_NIC=""
for iface in $(ls /sys/class/net | grep -v lo | sort); do
  if ! ip addr show "$iface" | grep -q "inet "; then
    HOSTONLY_NIC="$iface"
    break
  fi
done

[ -z "$NAT_NIC"      ] && NAT_NIC=$(ls /sys/class/net | grep -v lo | sort | head -1)
[ -z "$HOSTONLY_NIC" ] && HOSTONLY_NIC=$(ls /sys/class/net | grep -v lo | sort | sed -n '2p')

echo "==> [nmc] NAT NIC: $NAT_NIC  |  Host-only NIC: $HOSTONLY_NIC"

# ── 7. Static IP on host-only NIC ─────────────────────────────────────────────
echo "==> [nmc] Configuring static IP $LAB_IP on $HOSTONLY_NIC..."
cat > /etc/netplan/99-hostonly.yaml << EOF
network:
  version: 2
  ethernets:
    ${HOSTONLY_NIC}:
      addresses:
        - ${LAB_IP}/24
EOF
chmod 600 /etc/netplan/99-hostonly.yaml

# ── 8. Firewall ───────────────────────────────────────────────────────────────
echo "==> [nmc] Configuring UFW firewall..."

ufw --force reset
ufw default deny incoming
ufw default allow outgoing

# SSH only via NAT (lab admin access)
ufw allow in on "$NAT_NIC" to any port 22 proto tcp comment 'SSH via NAT only'

# HTTP only on host-only (attacker side)
ufw allow in on "$HOSTONLY_NIC" to any port 80 proto tcp comment 'Lab HTTP'

ufw --force enable

echo "==> [nmc] Firewall rules:"
echo "    SSH (22)  — allowed on $NAT_NIC only"
echo "    HTTP (80) — allowed on $HOSTONLY_NIC only"

# ── 9. MOTD ───────────────────────────────────────────────────────────────────
cat > /etc/motd << 'EOF'

  ███╗   ██╗███╗   ███╗ ██████╗
  ████╗  ██║████╗ ████║██╔════╝
  ██╔██╗ ██║██╔████╔██║██║
  ██║╚██╗██║██║╚██╔╝██║██║
  ██║ ╚████║██║ ╚═╝ ██║╚██████╗
  ╚═╝  ╚═══╝╚═╝     ╚═╝ ╚═════╝
  Northern Metro College — Pentest Lab

  [ADMIN] VM login: lab / lab  (console or SSH via NAT only)

  Targets — add to attacker /etc/hosts → 192.168.111.101:
    http://www.nmc.local          Main site  (file upload → RCE)
    http://academy.nmc.local      Academy    (IDOR → zip download)
    http://admission.nmc.local    Admission  (auth chain)
    http://registrar.nmc.local    Registrar  (forgot password chain)

  Lab management:
    nmc-status    nmc-logs    nmc-restart    nmc-reset

  Firewall: SSH blocked from attacker NIC. HTTP (80) only on host-only.

EOF

# ── 10. Convenience aliases ───────────────────────────────────────────────────
cat >> /home/lab/.bashrc << 'EOF'

# NMC lab aliases
alias nmc-status='cd /opt/nmc-lab && docker compose ps'
alias nmc-logs='cd /opt/nmc-lab && docker compose logs -f'
alias nmc-restart='sudo systemctl restart nmc-lab'
alias nmc-reset='cd /opt/nmc-lab && sudo docker compose down -v && sudo docker compose up -d'
EOF

# ── 11. Cleanup ───────────────────────────────────────────────────────────────
echo "==> [nmc] Cleaning up..."
apt-get autoremove -y -qq
apt-get clean -qq
rm -rf /tmp/*

echo "==> [nmc] Provisioning complete."
echo "==> [nmc] Images built from GitHub source — no DockerHub used."
echo "==> [nmc] Lab auto-starts on boot via nmc-lab.service."
