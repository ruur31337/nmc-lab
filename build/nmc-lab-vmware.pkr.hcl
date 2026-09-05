packer {
  required_plugins {
    vmware = {
      version = ">= 1.0.0"
      source  = "github.com/hashicorp/vmware"
    }
  }
}

# ── variables ──────────────────────────────────────────────────────────────────

variable "vm_name"   { default = "NorthernMetroCollege-Lab" }
variable "disk_size" { default = 30000 }   # MB
variable "memory"    { default = 4096 }    # MB
variable "cpus"      { default = 2 }
variable "lab_ip"    { default = "192.168.111.101" }   # host-only IP (VMnet1)

# ── source ─────────────────────────────────────────────────────────────────────

source "vmware-iso" "nmc_lab" {
  vm_name       = var.vm_name
  disk_size     = var.disk_size
  memory        = var.memory
  cpus          = var.cpus
  headless      = false
  guest_os_type = "ubuntu-64"

  # Local ISO — update path if needed
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
  ssh_timeout  = "60m"

  shutdown_command = "echo 'lab' | sudo -S shutdown -P now"

  # NIC 1 — NAT (internet for git clone + Docker base image pulls)
  network              = "nat"
  network_adapter_type = "vmxnet3"

  # NIC 2 — Host-Only (attacker connects here)
  vmx_data = {
    "ethernet1.present"        = "TRUE"
    "ethernet1.connectionType" = "hostonly"
    "ethernet1.virtualDev"     = "vmxnet3"
    "ethernet1.startConnected" = "TRUE"
  }

  output_directory = "output-vmware"
  skip_compaction  = false
}

# ── build ──────────────────────────────────────────────────────────────────────

build {
  sources = ["source.vmware-iso.nmc_lab"]

  # Provision: clone GitHub, build images, systemd, static IP, firewall
  provisioner "shell" {
    environment_vars = [
      "LAB_IP=${var.lab_ip}",
    ]
    script          = "scripts/setup.sh"
    execute_command = "echo 'lab' | sudo -S bash '{{.Path}}'"
  }
}
