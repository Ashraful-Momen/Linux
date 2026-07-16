# Multi-User DevOps/GitOps Environment on Kali Linux

This guide provides a complete implementation for a secure, multi-user development environment on your Kali Linux VPS. Each section builds upon the previous, creating a layered security approach.

---

## Part 1 — Initial Server Preparation

### 1.1 Update System Packages

Start with a fully updated system to ensure all security patches are applied .

```bash
apt update && apt upgrade -y
```

**Explanation:** This command refreshes package lists and upgrades all installed packages to their latest versions. Security patches are critical before exposing any service to the network.

**Verification:**
```bash
apt list --upgradable
```
(Should show no upgradable packages)

### 1.2 Install Essential Tools

```bash
apt install -y openssh-server ufw fail2ban xfsprogs quota \
    git curl wget software-properties-common \
    apt-transport-https ca-certificates gnupg lsb-release
```

**Explanation:** Installs all required packages:
- `openssh-server` - SSH server for remote access
- `ufw` - Uncomplicated Firewall 
- `fail2ban` - Brute-force protection 
- `xfsprogs` and `quota` - Disk quota management
- `git` - Version control for DevOps
- Docker prerequisites

**Verification:**
```bash
dpkg -l openssh-server ufw fail2ban xfsprogs quota git | grep "^ii"
```

### 1.3 Configure Timezone and Hostname

```bash
timedatectl set-timezone UTC
hostnamectl set-hostname devops-server
echo "127.0.1.1 devops-server" >> /etc/hosts
```

---

## Part 2 — User Creation

### 2.1 Create Team Users

For each team member, create a separate Linux user account.

```bash
# Example user creation (repeat for each team member)
useradd -m -s /bin/bash -c "John Developer" johnd
useradd -m -s /bin/bash -c "Jane Engineer" janee
```

**Explanation:** `-m` creates home directory, `-s` sets login shell, `-c` adds full name.

### 2.2 Set Up User SSH Key Directories

```bash
for user in johnd janee; do
    mkdir -p /home/$user/.ssh
    chmod 700 /home/$user/.ssh
    chown $user:$user /home/$user/.ssh
done
```

**Explanation:** Creates the `.ssh` directory with proper permissions. `700` means only the owner can read, write, and execute.

### 2.3 Add Public Keys (Important - do this before disabling password login!)

Create a temporary directory for key files:

```bash
mkdir /tmp/ssh-keys
```

For each user, place their public key in `/tmp/ssh-keys/username.pub`, then:

```bash
for user in johnd janee; do
    cat /tmp/ssh-keys/${user}.pub >> /home/$user/.ssh/authorized_keys
    chmod 600 /home/$user/.ssh/authorized_keys
    chown $user:$user /home/$user/.ssh/authorized_keys
done
```

**Verification:**
```bash
ls -la /home/*/.ssh/authorized_keys
```

---

## Part 3 — SSH Security

### 3.1 Harden SSH Configuration

Edit `/etc/ssh/sshd_config`:

```bash
cp /etc/ssh/sshd_config /etc/ssh/sshd_config.backup
vim /etc/ssh/sshd_config
```

Apply these settings :

```
# Change default port to reduce automated attacks
Port 2222

# Disable root login
PermitRootLogin no

# Require SSH keys - disable passwords
PasswordAuthentication no
PubkeyAuthentication yes
ChallengeResponseAuthentication no

# Only allow specific users (add each team member)
AllowUsers johnd janee

# Additional hardening
MaxAuthTries 3
MaxSessions 2
ClientAliveInterval 300
ClientAliveCountMax 2
```

**Explanation of key settings:**
- **Port 2222:** Reduces automated SSH scanning 
- **PermitRootLogin no:** Prevents direct root access 
- **PasswordAuthentication no:** Keys only - passwords can be brute-forced 
- **AllowUsers:** Explicitly lists who can connect

**Verification:**
```bash
sshd -t
```
(Should show no errors)

### 3.2 Restart SSH

```bash
systemctl restart ssh
systemctl enable ssh
systemctl status ssh
```

### 3.3 Configure Firewall (UFW)

```bash
ufw default deny incoming
ufw default allow outgoing
ufw allow 2222/tcp
ufw --force enable
ufw status verbose
```

**Explanation:** Deny all incoming by default, allow only SSH on custom port .

**Verification:**
```bash
ufw status
ss -tlnp | grep 2222
```

### 3.4 Configure Fail2Ban

```bash
cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
vim /etc/fail2ban/jail.local
```

Find the `[sshd]` section and configure :

```
[sshd]
enabled = true
port = 2222
filter = sshd
logpath = /var/log/auth.log
maxretry = 3
bantime = 3600
findtime = 600
```

**Explanation:** Bans IPs for 1 hour after 3 failed attempts within 10 minutes.

```bash
systemctl restart fail2ban
systemctl enable fail2ban
```

**Verification:**
```bash
fail2ban-client status sshd
journalctl -u fail2ban -n 20
```

---

## Part 4 — Disk Quotas

### 4.1 Enable Quota Support in Filesystem

First check your filesystem type:

```bash
df -Th /home
```

If using XFS (common), use `xfs_quota` . If using ext4, use `edquota`.

**For XFS:**

```bash
# Check if quota is enabled
mount | grep /home

# If not enabled, remount with quota
mount -o remount,uquota /home
```

**For ext4:**

```bash
# Add quota to /etc/fstab
vim /etc/fstab
# Add 'usrquota,grpquota' to options for /home

# Example:
# /dev/sda2 /home ext4 defaults,usrquota,grpquota 0 2

# Remount
mount -o remount /home

# Create quota files
quotacheck -cug /home
quotaon /home
```

### 4.2 Set 10GB Disk Quotas Per User

**For XFS :**

```bash
xfs_quota -x -c 'limit -u bsoft=10g bhard=10g johnd' /home
xfs_quota -x -c 'limit -u bsoft=10g bhard=10g janee' /home
```

**For ext4 :**

```bash
edquota johnd
edquota janee
```

Set `soft` and `hard` to `10485760` (10GB in kilobytes).

**Verification:**
```bash
xfs_quota -x -c 'report -u' /home
# or for ext4:
quota -v johnd
```

---

## Part 5 — CPU and RAM Limits

### 5.1 Understanding systemd Resource Controls

systemd uses cgroups v2 for resource management . User sessions are organized under `user.slice` .

### 5.2 Create Per-User Resource Limits via systemd

**Important:** systemd creates cgroups per UID. To set limits, we use `systemctl set-property` on the user's slice .

Find the UID for each user:

```bash
id johnd
id janee
```

Example output shows UID `1001`. Set limits:

```bash
# CPU Quota: 2 cores (200% of one core)
systemctl set-property user-1001.slice CPUQuota=200%
systemctl set-property user-1001.slice CPUWeight=100

# Memory limit: 4GB
systemctl set-property user-1001.slice MemoryMax=4G
systemctl set-property user-1001.slice MemoryHigh=3.5G
```

**Explanation :**
- `CPUQuota=200%` - Maximum of 2 CPU cores
- `MemoryMax=4G` - Hard limit (processes killed if exceeded)
- `MemoryHigh=3.5G` - Soft limit (throttled before kill)

**Apply to all users (scripted):**

```bash
for user in johnd janee; do
    uid=$(id -u $user)
    slice="user-${uid}.slice"
    systemctl set-property $slice CPUQuota=200%
    systemctl set-property $slice MemoryMax=4G
    systemctl set-property $slice MemoryHigh=3.5G
    echo "Set limits for $user (UID: $uid) on $slice"
done
```

**Verification :**

```bash
systemd-cgtop
systemd-cgls /sys/fs/cgroup/user.slice/
systemctl show user-1001.slice | grep -E "(CPUQuota|MemoryMax)"
```

### 5.3 Configure ulimit via PAM

Edit `/etc/security/limits.conf`:

```bash
vim /etc/security/limits.conf
```

Add:

```
# Per-user resource limits
*        soft    nproc       4096
*        hard    nproc       8192
*        soft    nofile      4096
*        hard    nofile      8192
*        soft    memlock     4096000
*        hard    memlock     4096000
```

**Explanation:** Limits number of processes and open files per user as additional safeguards.

**Verification:**
```bash
su - johnd -c "ulimit -a"
```

---

## Part 6 — Rootless Docker Installation

### 6.1 Install Docker Engine

```bash
# Add Docker's official GPG key
curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# Add the repository
echo "deb [arch=amd64 signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/debian bookworm stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker
apt update
apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin
```

### 6.2 Configure Rootless Docker for Each User

Rootless Docker runs entirely without root privileges, improving security .

**For each user:**

```bash
# Switch to user
su - johnd

# Install rootless Docker
dockerd-rootless-setuptool.sh install

# Start Docker as user service
export PATH=/usr/bin:$PATH
systemctl --user start docker
systemctl --user enable docker

# Enable lingering to keep Docker running after logout
sudo loginctl enable-linger johnd

# Configure Docker client
docker context use rootless
# Or set DOCKER_HOST
export DOCKER_HOST=unix://$XDG_RUNTIME_DIR/docker.sock
```

**Add to user's bashrc:**

```bash
echo 'export DOCKER_HOST=unix://$XDG_RUNTIME_DIR/docker.sock' >> /home/johnd/.bashrc
echo 'export PATH=/usr/bin:$PATH' >> /home/johnd/.bashrc
```

**Explanation :**
- Rootless Docker creates socket in `$XDG_RUNTIME_DIR`
- Uses `~/.local/share/docker` for storage
- Requires `loginctl enable-linger` to persist after logout

**Verification :**
```bash
su - johnd
docker run hello-world
docker ps
```

### 6.3 Install Docker Compose (Rootless Compatible)

Docker Compose v2 works with rootless Docker without additional configuration.

```bash
# Already installed as docker-compose-plugin
docker compose version
```

**Verification for each user:**
```bash
su - johnd
echo 'version: "3.8"' > test-compose.yml
echo 'services:' >> test-compose.yml
echo '  test:' >> test-compose.yml
echo '    image: alpine' >> test-compose.yml
echo '    command: echo "Compose works"' >> test-compose.yml
docker compose -f test-compose.yml up
```

---

## Part 7 — Security Hardening

### 7.1 File Permissions

Ensure users cannot access each other's home directories:

```bash
chmod 750 /home/johnd
chmod 750 /home/janee
```

**Explanation:** `750` means owner (user) can read/write/execute, group can read/execute, others have no access.

**Verification:**
```bash
su - johnd -c "ls /home/janee 2>&1"
# Should show permission denied
```

### 7.2 Automatic Security Updates

```bash
apt install -y unattended-upgrades
dpkg-reconfigure --priority=low unattended-upgrades
```

Select "Yes" to enable automatic updates.

Configure `/etc/apt/apt.conf.d/20auto-upgrades`:

```
APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Download-Upgradeable-Packages "1";
APT::Periodic::AutocleanInterval "7";
APT::Periodic::Unattended-Upgrade "1";
```

**Verification:**
```bash
systemctl status unattended-upgrades
cat /var/log/unattended-upgrades/unattended-upgrades.log | tail -20
```

### 7.3 Auditing and Logging

**Install auditd:**

```bash
apt install -y auditd audispd-plugins
```

**Configure audit rules:**

```bash
vim /etc/audit/rules.d/audit.rules
```

Add:

```bash
# Monitor SSH configuration changes
-w /etc/ssh/sshd_config -p wa -k ssh_config

# Monitor user creation/deletion
-w /etc/passwd -p wa -k user_mgmt
-w /etc/shadow -p wa -k user_mgmt

# Monitor sudoers (though no sudo, still important)
-w /etc/sudoers -p wa -k sudoers

# Monitor Docker
-w /usr/bin/docker -p x -k docker
```

**Start auditd:**

```bash
systemctl enable auditd
systemctl start auditd
```

**Verification:**
```bash
auditctl -l
ausearch -k ssh_config
```

### 7.4 Configure Log Rotation

Create `/etc/logrotate.d/secure-audit`:

```
/var/log/auth.log
/var/log/fail2ban.log
/var/log/audit/audit.log
/var/log/unattended-upgrades/*.log
{
    weekly
    rotate 4
    compress
    missingok
    notifempty
    create 0640 root adm
}
```

**Verification:**
```bash
logrotate -d /etc/logrotate.conf
```

---

## Part 8 — Testing and Verification

### 8.1 SSH Access Test

```bash
# Test SSH connection
ssh -p 2222 johnd@<server-ip> "echo 'SSH works!'"

# Verify key-only authentication
ssh -p 2222 johnd@<server-ip> -o PreferredAuthentications=password
# Should fail
```

**Verification:**
```bash
# Check SSH logs
tail -20 /var/log/auth.log

# Check active SSH sessions
ss -tnp | grep 2222
who
```

### 8.2 Resource Limit Testing

**Test CPU limit:**

```bash
su - johnd
# Run CPU-intensive process
stress --cpu 4 --timeout 30 &
# Watch CPU usage
top -u johnd
# Should not exceed 200% (2 cores)
```

**Test Memory limit:**

```bash
su - johnd
# Try to allocate 5GB memory
python3 -c "import sys; [sys.stdout.write('.' * (5*1024**3)) for _ in range(1)]"
# Process should be killed or throttled
```

**Verification:**
```bash
systemd-cgtop
journalctl -u user-1001.slice -n 20
```

### 8.3 Docker Test

```bash
su - johnd
docker run --rm alpine:latest echo "Docker works!"
docker run --rm --memory=2g stress --cpu 1 --timeout 10
docker compose version
```

### 8.4 Disk Quota Test

```bash
su - johnd
# Create 5GB file (should succeed)
dd if=/dev/zero of=~/largefile bs=1M count=5000

# Try to exceed 10GB (should fail)
dd if=/dev/zero of=~/too_large bs=1M count=11000
```

**Verification:**
```bash
quota -v johnd
# or XFS:
xfs_quota -x -c 'report -u' /home
```

### 8.5 Isolation Test

```bash
# Try to access other user's files
su - johnd -c "ls /home/janee"
# Should return permission denied

# Check process visibility
ps aux | grep janee
# Should only show processes owned by johnd
```

### 8.6 Firewall Test

```bash
# Test from external machine
nmap -p 2222 <server-ip>
nmap -p 22 <server-ip>

# Test fail2ban
# After 3 failed SSH attempts, IP should be banned
ssh -p 2222 invalid@<server-ip>
# Check ban
fail2ban-client status sshd
```

---

## Part 9 — Backup and Rollback

### 9.1 Configuration Backup Script

Create `/usr/local/bin/backup-config.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/root/config-backups"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Backup critical configurations
tar -czf $BACKUP_DIR/config_$DATE.tar.gz \
    /etc/ssh/ \
    /etc/fail2ban/ \
    /etc/ufw/ \
    /etc/security/ \
    /etc/audit/ \
    /etc/systemd/system/ \
    /etc/logrotate.d/ \
    /etc/apt/apt.conf.d/20auto-upgrades \
    /etc/fstab

# Backup user SSH keys
tar -czf $BACKUP_DIR/ssh_keys_$DATE.tar.gz \
    /home/*/.ssh/authorized_keys

# Database of users and UIDs
getent passwd | grep -E "^(johnd|janee)" > $BACKUP_DIR/users_$DATE.txt

echo "Backup completed: $BACKUP_DIR/config_$DATE.tar.gz"
```

**Make executable and schedule:**

```bash
chmod +x /usr/local/bin/backup-config.sh
(crontab -l 2>/dev/null; echo "0 2 * * * /usr/local/bin/backup-config.sh") | crontab -
```

### 9.2 Rollback Procedures

**Restore SSH configuration:**

```bash
# Stop SSH
systemctl stop ssh

# Restore backup
cp /etc/ssh/sshd_config.backup /etc/ssh/sshd_config

# Restart SSH
systemctl start ssh
systemctl status ssh
```

**Restore User Quotas:**

```bash
# Restore from backup
edquota -p johnd janee
# Or for XFS:
xfs_quota -x -c 'limit -u bsoft=10g bhard=10g janee' /home
```

**Restore systemd Resource Limits:**

```bash
# Reset to default
systemctl set-property user-1001.slice CPUQuota=
systemctl set-property user-1001.slice MemoryMax=
# Or restore from file:
systemctl import-environment
```

### 9.3 Disaster Recovery Plan

1. **SSH Lockout Recovery:**
   - Use VPS console access (KVM/IPMI)
   - Or contact hosting provider for out-of-band access
   - Restore SSH configuration from backup

2. **User Data Recovery:**
   - Regular backups of `/home` directory
   - Store backups off-server (S3, etc.)

3. **System Rollback:**
   - Maintain previous configurations
   - Use `systemctl revert` for systemd units

**Verification after rollback:**
```bash
# Test all systems
ssh -p 2222 johnd@<server-ip> "echo OK"
docker ps
quota -v johnd
systemd-cgtop
```

---

## Summary of Key Security Controls

| Control | Implementation | Verification |
|---------|---------------|--------------|
| SSH Security | Port 2222, no root, keys only | `sshd -t`, `ufw status` |
| Disk Quotas | 10GB per user | `quota -v user` |
| CPU Limit | 2 cores (200% quota) | `systemd-cgtop` |
| Memory Limit | 4GB per user | `systemd-cgtop` |
| User Isolation | 750 home perms, cgroups | `ls /home/other` |
| Docker Security | Rootless per user | `docker info` |
| Brute Force | Fail2Ban | `fail2ban-client status` |
| Firewall | UFW default deny | `ufw status` |
| Auditing | auditd | `auditctl -l` |
| Updates | Unattended-upgrades | `systemctl status unattended-upgrades` |

---

## Final Commands for Full Verification

```bash
#!/bin/bash
echo "=== SSH Security ==="
sshd -t && echo "SSH config OK"
ufw status | grep 2222
fail2ban-client status sshd

echo -e "\n=== Resource Limits ==="
for user in johnd janee; do
    uid=$(id -u $user)
    echo "$user (UID $uid):"
    systemctl show user-${uid}.slice | grep -E "(CPUQuota|MemoryMax|MemoryHigh)"
done

echo -e "\n=== Disk Quotas ==="
quota -v johnd
quota -v janee

echo -e "\n=== User Isolation ==="
su - johnd -c "ls /home/janee 2>&1 | head -1"
su - janee -c "ls /home/johnd 2>&1 | head -1"

echo -e "\n=== Docker Status ==="
su - johnd -c "docker info | grep -E '(Server Version|Rootless)'"
su - janee -c "docker info | grep -E '(Server Version|Rootless)'"

echo -e "\n=== Auditing ==="
auditctl -l | wc -l
systemctl status auditd --no-pager | grep Active

echo -e "\n=== Auto Updates ==="
systemctl status unattended-upgrades --no-pager | grep Active
```

**Output should confirm all systems are operating as expected.** If any verification step fails, refer to the rollback procedures and troubleshooting section.
