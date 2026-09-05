packer {
  required_plugins {
    virtualbox = {
      version = ">= 1.0.5"
      source  = "github.com/hashicorp/virtualbox"
    }
  }
}

# ── variables ──────────────────────────────────────────────────────────────────

variable "vm_name"   { default = "NorthernMetroCollege-Lab" }
variable "disk_size" { default = 30000 }   # MB — larger than NSD (builds from source)
variable "memory"    { default = 4096 }    # MB — 4 GB for Docker build
variable "cpus"      { default = 2 }
variable "lab_ip"    { default = "192.168.56.101" }

# Windows: "VirtualBox Host-Only Ethernet Adapter"
# Linux/Mac: "vboxnet0"
variable "hostonly_adapter" { default = "VirtualBox Host-Only Ethernet Adapter" }

# ── source ─────────────────────────────────────────────────────────────────────

source "virtualbox-iso" "nmc_lab" {
  vm_name       = var.vm_name
  disk_size     = var.disk_size
  memory        = var.memory
  cpus          = var.cpus
  headless      = true
  guest_os_type = "Ubuntu_64"

  iso_url      = "file:///C:/Users/Leonel Obina/Documents/nmc-lab/build/ubuntu-22.04.5-live-server-amd64.iso"
  iso_checksum = "none"

  http_directory = "http"
  boot_wait      = "5s"
  boot_command   = [
    "c<wait>",
    "linux /casper/vmlinuz --- autoinstall ds='nocloud-net;seedfrom=http://{{.HTTPIP}}:{{.HTTPPort}}/'<enter><wait>",
    "initrd /casper/initrd<enter><wait>",
    "boot<enter>"
  ]

  ssh_username = "lab"
  ssh_password = "lab"
  ssh_timeout  = "60m"   # longer — build from source takes time

  shutdown_command = "echo 'lab' | sudo -S shutdown -P now"

  # NIC 1 — NAT (internet for git clone + Docker base image pulls)
  # NIC 2 — Host-Only (attacker reachability at 192.168.56.101)
  vboxmanage = [
    ["modifyvm", "{{.Name}}", "--nic2", "hostonly",
     "--hostonlyadapter2", var.hostonly_adapter],
    ["modifyvm", "{{.Name}}", "--audio", "none"],
    ["modifyvm", "{{.Name}}", "--usb", "off"],
  ]

  format = "ova"
  export_opts = [
    "--manifest",
    "--vsys", "0",
    "--description", "Northern Metro College Pentest Lab — deliberately vulnerable Philippine college web environment. Four apps: main site (file upload/RCE via Chankro), academy portal (IDOR inbox → zip download), admission portal (auth chain), registrar (forgot password chain). All accessible on port 80 via nginx.",
    "--version", "1.0",
  ]

  output_directory = "output"
  output_filename  = "NorthernMetroCollege-Lab"
}

# ── build ──────────────────────────────────────────────────────────────────────

build {
  sources = ["source.virtualbox-iso.nmc_lab"]

  # Provision: clone GitHub, build images, systemd, static IP, firewall
  provisioner "shell" {
    environment_vars = [
      "LAB_IP=${var.lab_ip}",
    ]
    script          = "scripts/setup.sh"
    execute_command = "echo 'lab' | sudo -S bash '{{.Path}}'"
  }
}
