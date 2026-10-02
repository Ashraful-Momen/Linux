# Oracle VirtualBox — Install Commands (Kali Linux)

> Working method on Kali GNU/Linux Rolling 2026.3 (kernel 7.1.5+kali-amd64).
> Installed version: **VirtualBox 7.2.16** from Kali repositories.
> Date: 2026-10-02

## 1. Essential install (3 commands)

```bash
# Refresh package lists
sudo apt update

# Install kernel headers for the running kernel (needed to build vbox modules)
sudo apt install -y linux-headers-$(uname -r)

# Install VirtualBox + GUI + kernel modules
sudo apt install -y virtualbox virtualbox-qt virtualbox-dkms
```

**One-liner (everything above in one go):**

```bash
sudo apt update && sudo apt install -y linux-headers-$(uname -r) virtualbox virtualbox-qt virtualbox-dkms
```

## 2. Post-install setup

```bash
# Add yourself to vboxusers group (USB passthrough) — takes effect after next login
sudo usermod -aG vboxusers $USER

# Load the kernel modules immediately (auto-loads on future boots)
sudo modprobe vboxdrv
sudo modprobe vboxnetflt
sudo modprobe vboxnetadp

# Launch VirtualBox
virtualbox
```

## 3. Verification commands

```bash
VBoxManage --version          # prints version, no warnings
dkms status                   # "virtualbox/7.2.16, <kernel>: installed"
lsmod | grep vbox             # vboxdrv, vboxnetflt, vboxnetadp listed
ls -la /dev/vboxdrv           # device node exists
```

## 4. What did NOT work (reference)

Oracle's official `.deb` (built for frozen Debian trixie) **fails on Kali rolling**:

```bash
# Download (kept in this folder)
curl -LO "https://download.virtualbox.org/virtualbox/7.2.20/virtualbox-7.2_7.2.20-175154~Debian~trixie_amd64.deb"

# Install attempt — FAILED: unsatisfied dependencies
# Oracle trixie build needs libvpx9 + libxml2, Kali rolling ships libvpx12
sudo apt install -y "./virtualbox-7.2_7.2.20-175154~Debian~trixie_amd64.deb"
```

Checksum of that download (verified authentic):

```
04b25e10058a4e0e561498db05c456f9705b8315e3535522d30af5bc9cc06cd7  virtualbox-7.2_7.2.20-175154~Debian~trixie_amd64.deb
```

## TL;DR

On Kali, always install VirtualBox from the **Kali repos** — no Oracle download needed:

```bash
sudo apt update && sudo apt install -y linux-headers-$(uname -r) virtualbox virtualbox-qt virtualbox-dkms
```
