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

========================================================= Same Note in BN Version ============================
# মাল্টি-ইউজার ডেভঅপ্স/গিটঅপ্স এনভায়রনমেন্ট সেটআপ গাইড (বাংলা)

এই গাইডটি আপনার Kali Linux VPS-এ একটি নিরাপদ মাল্টি-ইউজার ডেভেলপমেন্ট এনভায়রনমেন্ট তৈরির জন্য সম্পূর্ণ ধাপে ধাপে নির্দেশনা প্রদান করবে। প্রতিটি অংশ পূর্ববর্তী অংশের উপর ভিত্তি করে তৈরি, যা একটি স্তরবদ্ধ নিরাপত্তা ব্যবস্থা নিশ্চিত করে।

---

## পার্ট ১ — প্রাথমিক সার্ভার প্রস্তুতি

### ১.১ সিস্টেম প্যাকেজ আপডেট করুন

সর্বশেষ নিরাপত্তা প্যাচ নিশ্চিত করতে সম্পূর্ণ আপডেটেড সিস্টেম দিয়ে শুরু করুন।

```bash
apt update && apt upgrade -y
```

**ব্যাখ্যা:** এই কমান্ড প্যাকেজ লিস্ট রিফ্রেশ করে এবং সব ইনস্টল করা প্যাকেজকে সর্বশেষ ভার্সনে আপগ্রেড করে। নেটওয়ার্কে সার্ভার এক্সপোজ করার আগে নিরাপত্তা প্যাচ অত্যন্ত গুরুত্বপূর্ণ।

**ভেরিফিকেশন:**
```bash
apt list --upgradable
```
(আপগ্রেডযোগ্য কোনো প্যাকেজ দেখানো উচিত নয়)

### ১.২ প্রয়োজনীয় টুলস ইনস্টল করুন

```bash
apt install -y openssh-server ufw fail2ban xfsprogs quota \
    git curl wget software-properties-common \
    apt-transport-https ca-certificates gnupg lsb-release
```

**ব্যাখ্যা:** প্রয়োজনীয় সব প্যাকেজ ইনস্টল করে:
- `openssh-server` - রিমোট অ্যাক্সেসের জন্য SSH সার্ভার
- `ufw` - ইউনকমপ্লিকেটেড ফায়ারওয়াল
- `fail2ban` - ব্রুট-ফোর্স অ্যাটাক প্রতিরোধ
- `xfsprogs` এবং `quota` - ডিস্ক কোটা ম্যানেজমেন্ট
- `git` - ডেভঅপ্সের জন্য ভার্সন কন্ট্রোল
- ডকারের প্রাক-প্রয়োজনীয় প্যাকেজ

**ভেরিফিকেশন:**
```bash
dpkg -l openssh-server ufw fail2ban xfsprogs quota git | grep "^ii"
```

### ১.৩ টাইমজোন এবং হোস্টনেম কনফিগার করুন

```bash
timedatectl set-timezone UTC
hostnamectl set-hostname devops-server
echo "127.0.1.1 devops-server" >> /etc/hosts
```

---

## পার্ট ২ — ইউজার তৈরি

### ২.১ টিম মেম্বারদের জন্য ইউজার তৈরি করুন

প্রতিটি টিম মেম্বারের জন্য আলাদা লিনাক্স ইউজার অ্যাকাউন্ট তৈরি করুন।

```bash
# উদাহরণ ইউজার তৈরি (প্রতিটি টিম মেম্বারের জন্য পুনরাবৃত্তি করুন)
useradd -m -s /bin/bash -c "জন ডেভেলপার" johnd
useradd -m -s /bin/bash -c "জেইন ইঞ্জিনিয়ার" janee
```

**ব্যাখ্যা:** `-m` হোম ডিরেক্টরি তৈরি করে, `-s` লগিন শেল সেট করে, `-c` পূর্ণ নাম যোগ করে।

### ২.২ ইউজার SSH কী ডিরেক্টরি সেটআপ করুন

```bash
for user in johnd janee; do
    mkdir -p /home/$user/.ssh
    chmod 700 /home/$user/.ssh
    chown $user:$user /home/$user/.ssh
done
```

**ব্যাখ্যা:** `.ssh` ডিরেক্টরি সঠিক অনুমতি সহ তৈরি করে। `700` মানে শুধুমাত্র মালিক পড়তে, লিখতে এবং এক্সিকিউট করতে পারেন।

### ২.৩ পাবলিক কী যোগ করুন (পাসওয়ার্ড লগিন বন্ধ করার আগে এটি করুন!)

কী ফাইলের জন্য অস্থায়ী ডিরেক্টরি তৈরি করুন:

```bash
mkdir /tmp/ssh-keys
```

প্রতিটি ইউজারের জন্য তাদের পাবলিক কী `/tmp/ssh-keys/username.pub`-এ রাখুন, তারপর:

```bash
for user in johnd janee; do
    cat /tmp/ssh-keys/${user}.pub >> /home/$user/.ssh/authorized_keys
    chmod 600 /home/$user/.ssh/authorized_keys
    chown $user:$user /home/$user/.ssh/authorized_keys
done
```

**ভেরিফিকেশন:**
```bash
ls -la /home/*/.ssh/authorized_keys
```

---

## পার্ট ৩ — SSH নিরাপত্তা

### ৩.১ SSH কনফিগারেশন হার্ডেন করুন

`/etc/ssh/sshd_config` সম্পাদনা করুন:

```bash
cp /etc/ssh/sshd_config /etc/ssh/sshd_config.backup
vim /etc/ssh/sshd_config
```

এই সেটিংস প্রয়োগ করুন:

```
# স্বয়ংক্রিয় অ্যাটাক কমাতে ডিফল্ট পোর্ট পরিবর্তন
Port 2222

# রুট লগিন নিষ্ক্রিয় করুন
PermitRootLogin no

# SSH কী প্রয়োজন - পাসওয়ার্ড নিষ্ক্রিয় করুন
PasswordAuthentication no
PubkeyAuthentication yes
ChallengeResponseAuthentication no

# শুধুমাত্র নির্দিষ্ট ইউজার অনুমতি দিন (প্রতিটি টিম মেম্বার যোগ করুন)
AllowUsers johnd janee

# অতিরিক্ত হার্ডেনিং
MaxAuthTries 3
MaxSessions 2
ClientAliveInterval 300
ClientAliveCountMax 2
```

**মূল সেটিংসের ব্যাখ্যা:**
- **Port 2222:** স্বয়ংক্রিয় SSH স্ক্যানিং কমায়
- **PermitRootLogin no:** সরাসরি রুট অ্যাক্সেস প্রতিরোধ করে
- **PasswordAuthentication no:** শুধুমাত্র কী - পাসওয়ার্ড ব্রুট-ফোর্স করা যেতে পারে
- **AllowUsers:** স্পষ্টভাবে কে সংযোগ করতে পারে তা তালিকাভুক্ত করে

**ভেরিফিকেশন:**
```bash
sshd -t
```
(কোনো ত্রুটি দেখানো উচিত নয়)

### ৩.২ SSH রিস্টার্ট করুন

```bash
systemctl restart ssh
systemctl enable ssh
systemctl status ssh
```

### ৩.৩ ফায়ারওয়াল কনফিগার করুন (UFW)

```bash
ufw default deny incoming
ufw default allow outgoing
ufw allow 2222/tcp
ufw --force enable
ufw status verbose
```

**ব্যাখ্যা:** ডিফল্টভাবে সব ইনকামিং বন্ধ, শুধুমাত্র কাস্টম পোর্টে SSH অনুমতি দিন।

**ভেরিফিকেশন:**
```bash
ufw status
ss -tlnp | grep 2222
```

### ৩.৪ Fail2Ban কনফিগার করুন

```bash
cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
vim /etc/fail2ban/jail.local
```

`[sshd]` সেকশন খুঁজে এইভাবে কনফিগার করুন:

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

**ব্যাখ্যা:** 10 মিনিটের মধ্যে 3 বার ব্যর্থ চেষ্টার পর 1 ঘন্টার জন্য IP ব্যান করে।

```bash
systemctl restart fail2ban
systemctl enable fail2ban
```

**ভেরিফিকেশন:**
```bash
fail2ban-client status sshd
journalctl -u fail2ban -n 20
```

---

## পার্ট ৪ — ডিস্ক কোটা

### ৪.১ ফাইলসিস্টেমে কোটা সাপোর্ট সক্রিয় করুন

প্রথমে আপনার ফাইলসিস্টেম টাইপ চেক করুন:

```bash
df -Th /home
```

যদি XFS ব্যবহার করেন (সাধারণ), `xfs_quota` ব্যবহার করুন। যদি ext4 ব্যবহার করেন, `edquota` ব্যবহার করুন।

**XFS-এর জন্য:**

```bash
# কোটা সক্রিয় কিনা চেক করুন
mount | grep /home

# যদি সক্রিয় না হয়, কোটা সহ রিমাউন্ট করুন
mount -o remount,uquota /home
```

**ext4-এর জন্য:**

```bash
# /etc/fstab-এ কোটা যোগ করুন
vim /etc/fstab
# /home-এর জন্য অপশনে 'usrquota,grpquota' যোগ করুন

# উদাহরণ:
# /dev/sda2 /home ext4 defaults,usrquota,grpquota 0 2

# রিমাউন্ট করুন
mount -o remount /home

# কোটা ফাইল তৈরি করুন
quotacheck -cug /home
quotaon /home
```

### ৪.২ প্রতি ইউজারে ১০ জিবি ডিস্ক কোটা সেট করুন

**XFS-এর জন্য:**

```bash
xfs_quota -x -c 'limit -u bsoft=10g bhard=10g johnd' /home
xfs_quota -x -c 'limit -u bsoft=10g bhard=10g janee' /home
```

**ext4-এর জন্য:**

```bash
edquota johnd
edquota janee
```

`soft` এবং `hard` কে `10485760` (10GB কিলোবাইটে) সেট করুন।

**ভেরিফিকেশন:**
```bash
xfs_quota -x -c 'report -u' /home
# অথবা ext4-এর জন্য:
quota -v johnd
```

---

## পার্ট ৫ — CPU এবং RAM সীমা

### ৫.১ systemd রিসোর্স কন্ট্রোল বোঝা

systemd cgroups v2 ব্যবহার করে রিসোর্স কন্ট্রোল করে। ইউজার সেশন `user.slice`-এর অধীনে সংগঠিত হয়।

### ৫.২ systemd-এর মাধ্যমে প্রতি-ইউজার রিসোর্স সীমা তৈরি করুন

**গুরুত্বপূর্ণ:** systemd প্রতি UID-এর জন্য cgroups তৈরি করে। সীমা সেট করতে আমরা `systemctl set-property` ব্যবহার করি ইউজারের slice-এ।

প্রতিটি ইউজারের UID খুঁজুন:

```bash
id johnd
id janee
```

উদাহরণ আউটপুট UID `1001` দেখায়। সীমা সেট করুন:

```bash
# CPU কোটা: 2 কোরে (একটি কোরের 200%)
systemctl set-property user-1001.slice CPUQuota=200%
systemctl set-property user-1001.slice CPUWeight=100

# মেমরি সীমা: 4GB
systemctl set-property user-1001.slice MemoryMax=4G
systemctl set-property user-1001.slice MemoryHigh=3.5G
```

**ব্যাখ্যা:**
- `CPUQuota=200%` - সর্বোচ্চ 2 CPU কোরে
- `MemoryMax=4G` - হার্ড লিমিট (সীমা অতিক্রম করলে প্রক্রিয়া বন্ধ হবে)
- `MemoryHigh=3.5G` - সফট লিমিট (বন্ধ হওয়ার আগে থ্রোটল হবে)

**সব ইউজারের জন্য প্রয়োগ করুন (স্ক্রিপ্টেড):**

```bash
for user in johnd janee; do
    uid=$(id -u $user)
    slice="user-${uid}.slice"
    systemctl set-property $slice CPUQuota=200%
    systemctl set-property $slice MemoryMax=4G
    systemctl set-property $slice MemoryHigh=3.5G
    echo "$user (UID: $uid)-এর জন্য $slice-এ সীমা সেট করা হয়েছে"
done
```

**ভেরিফিকেশন:**

```bash
systemd-cgtop
systemd-cgls /sys/fs/cgroup/user.slice/
systemctl show user-1001.slice | grep -E "(CPUQuota|MemoryMax)"
```

### ৫.৩ PAM-এর মাধ্যমে ulimit কনফিগার করুন

`/etc/security/limits.conf` সম্পাদনা করুন:

```bash
vim /etc/security/limits.conf
```

যোগ করুন:

```
# প্রতি-ইউজার রিসোর্স সীমা
*        soft    nproc       4096
*        hard    nproc       8192
*        soft    nofile      4096
*        hard    nofile      8192
*        soft    memlock     4096000
*        hard    memlock     4096000
```

**ব্যাখ্যা:** অতিরিক্ত সুরক্ষা হিসেবে প্রতি ইউজারের প্রক্রিয়া এবং খোলা ফাইলের সংখ্যা সীমাবদ্ধ করে।

**ভেরিফিকেশন:**
```bash
su - johnd -c "ulimit -a"
```

---

## পার্ট ৬ — রুটলেস ডকার ইনস্টলেশন

### ৬.১ ডকার ইঞ্জিন ইনস্টল করুন

```bash
# Docker-এর অফিসিয়াল GPG কী যোগ করুন
curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# রিপোজিটরি যোগ করুন
echo "deb [arch=amd64 signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/debian bookworm stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null

# ডকার ইনস্টল করুন
apt update
apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin
```

### ৬.২ প্রতিটি ইউজারের জন্য রুটলেস ডকার কনফিগার করুন

রুটলেস ডকার সম্পূর্ণরূপে রুট সুবিধা ছাড়াই চলে, যা নিরাপত্তা উন্নত করে।

**প্রতিটি ইউজারের জন্য:**

```bash
# ইউজারে সুইচ করুন
su - johnd

# রুটলেস ডকার ইনস্টল করুন
dockerd-rootless-setuptool.sh install

# ইউজার সার্ভিস হিসেবে ডকার শুরু করুন
export PATH=/usr/bin:$PATH
systemctl --user start docker
systemctl --user enable docker

# লগআউটের পরেও ডকার চলমান রাখতে লিঙ্গারিং সক্রিয় করুন
sudo loginctl enable-linger johnd

# ডকার ক্লায়েন্ট কনফিগার করুন
docker context use rootless
# অথবা DOCKER_HOST সেট করুন
export DOCKER_HOST=unix://$XDG_RUNTIME_DIR/docker.sock
```

**ইউজারের bashrc-এ যোগ করুন:**

```bash
echo 'export DOCKER_HOST=unix://$XDG_RUNTIME_DIR/docker.sock' >> /home/johnd/.bashrc
echo 'export PATH=/usr/bin:$PATH' >> /home/johnd/.bashrc
```

**ব্যাখ্যা:**
- রুটলেস ডকার `$XDG_RUNTIME_DIR`-এ সকেট তৈরি করে
- স্টোরেজের জন্য `~/.local/share/docker` ব্যবহার করে
- লগআউটের পরেও চলমান রাখতে `loginctl enable-linger` প্রয়োজন

**ভেরিফিকেশন:**
```bash
su - johnd
docker run hello-world
docker ps
```

### ৬.৩ ডকার কম্পোজ ইনস্টল করুন (রুটলেস সামঞ্জস্যপূর্ণ)

ডকার কম্পোজ v2 অতিরিক্ত কনফিগারেশন ছাড়াই রুটলেস ডকারের সাথে কাজ করে।

```bash
# ইতিমধ্যে docker-compose-plugin হিসেবে ইনস্টল করা আছে
docker compose version
```

**প্রতিটি ইউজারের জন্য ভেরিফিকেশন:**
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

## পার্ট ৭ — নিরাপত্তা হার্ডেনিং

### ৭.১ ফাইল পারমিশন

নিশ্চিত করুন যে ইউজাররা অন্য ইউজারের হোম ডিরেক্টরিতে অ্যাক্সেস করতে পারে না:

```bash
chmod 750 /home/johnd
chmod 750 /home/janee
```

**ব্যাখ্যা:** `750` মানে মালিক পড়তে/লিখতে/এক্সিকিউট করতে পারেন, গ্রুপ পড়তে/এক্সিকিউট করতে পারেন, অন্যদের কোনো অ্যাক্সেস নেই।

**ভেরিফিকেশন:**
```bash
su - johnd -c "ls /home/janee 2>&1"
# Permission denied দেখাতে হবে
```

### ৭.২ স্বয়ংক্রিয় নিরাপত্তা আপডেট

```bash
apt install -y unattended-upgrades
dpkg-reconfigure --priority=low unattended-upgrades
```

"হ্যাঁ" নির্বাচন করুন স্বয়ংক্রিয় আপডেট সক্রিয় করতে।

`/etc/apt/apt.conf.d/20auto-upgrades` কনফিগার করুন:

```
APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Download-Upgradeable-Packages "1";
APT::Periodic::AutocleanInterval "7";
APT::Periodic::Unattended-Upgrade "1";
```

**ভেরিফিকেশন:**
```bash
systemctl status unattended-upgrades
cat /var/log/unattended-upgrades/unattended-upgrades.log | tail -20
```

### ৭.৩ অডিটিং এবং লগিং

**auditd ইনস্টল করুন:**

```bash
apt install -y auditd audispd-plugins
```

**অডিট রুলস কনফিগার করুন:**

```bash
vim /etc/audit/rules.d/audit.rules
```

যোগ করুন:

```bash
# SSH কনফিগারেশন পরিবর্তন মনিটর করুন
-w /etc/ssh/sshd_config -p wa -k ssh_config

# ইউজার তৈরি/মুছে ফেলা মনিটর করুন
-w /etc/passwd -p wa -k user_mgmt
-w /etc/shadow -p wa -k user_mgmt

# sudoers মনিটর করুন (যদিও sudo নেই, তবুও গুরুত্বপূর্ণ)
-w /etc/sudoers -p wa -k sudoers

# ডকার মনিটর করুন
-w /usr/bin/docker -p x -k docker
```

**auditd শুরু করুন:**

```bash
systemctl enable auditd
systemctl start auditd
```

**ভেরিফিকেশন:**
```bash
auditctl -l
ausearch -k ssh_config
```

### ৭.৪ লগ রোটেশন কনফিগার করুন

`/etc/logrotate.d/secure-audit` তৈরি করুন:

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

**ভেরিফিকেশন:**
```bash
logrotate -d /etc/logrotate.conf
```

---

## পার্ট ৮ — টেস্টিং এবং ভেরিফিকেশন

### ৮.১ SSH অ্যাক্সেস টেস্ট

```bash
# SSH সংযোগ টেস্ট করুন
ssh -p 2222 johnd@<server-ip> "echo 'SSH works!'"

# শুধুমাত্র কী-ভিত্তিক অথেনটিকেশন ভেরিফাই করুন
ssh -p 2222 johnd@<server-ip> -o PreferredAuthentications=password
# ব্যর্থ হওয়া উচিত
```

**ভেরিফিকেশন:**
```bash
# SSH লগ চেক করুন
tail -20 /var/log/auth.log

# সক্রিয় SSH সেশন চেক করুন
ss -tnp | grep 2222
who
```

### ৮.২ রিসোর্স লিমিট টেস্টিং

**CPU লিমিট টেস্ট:**

```bash
su - johnd
# CPU-ইনটেনসিভ প্রক্রিয়া চালান
stress --cpu 4 --timeout 30 &
# CPU ব্যবহার দেখুন
top -u johnd
# 200% (2 কোরে) এর বেশি হওয়া উচিত নয়
```

**মেমরি লিমিট টেস্ট:**

```bash
su - johnd
# 5GB মেমরি বরাদ্দ করার চেষ্টা করুন
python3 -c "import sys; [sys.stdout.write('.' * (5*1024**3)) for _ in range(1)]"
# প্রক্রিয়া বন্ধ বা থ্রোটল হওয়া উচিত
```

**ভেরিফিকেশন:**
```bash
systemd-cgtop
journalctl -u user-1001.slice -n 20
```

### ৮.৩ ডকার টেস্ট

```bash
su - johnd
docker run --rm alpine:latest echo "Docker works!"
docker run --rm --memory=2g stress --cpu 1 --timeout 10
docker compose version
```

### ৮.৪ ডিস্ক কোটা টেস্ট

```bash
su - johnd
# 5GB ফাইল তৈরি করুন (সফল হওয়া উচিত)
dd if=/dev/zero of=~/largefile bs=1M count=5000

# 10GB এর বেশি করার চেষ্টা করুন (ব্যর্থ হওয়া উচিত)
dd if=/dev/zero of=~/too_large bs=1M count=11000
```

**ভেরিফিকেশন:**
```bash
quota -v johnd
# অথবা XFS-এর জন্য:
xfs_quota -x -c 'report -u' /home
```

### ৮.৫ আইসোলেশন টেস্ট

```bash
# অন্য ইউজারের ফাইল অ্যাক্সেস করার চেষ্টা করুন
su - johnd -c "ls /home/janee"
# Permission denied দেখানো উচিত

# প্রক্রিয়া দৃশ্যমানতা চেক করুন
ps aux | grep janee
# শুধুমাত্র johnd-এর মালিকানাধীন প্রক্রিয়া দেখানো উচিত
```

### ৮.৬ ফায়ারওয়াল টেস্ট

```bash
# বাহ্যিক মেশিন থেকে টেস্ট করুন
nmap -p 2222 <server-ip>
nmap -p 22 <server-ip>

# fail2ban টেস্ট করুন
# 3 বার ব্যর্থ SSH চেষ্টার পর IP ব্যান হওয়া উচিত
ssh -p 2222 invalid@<server-ip>
# ব্যান চেক করুন
fail2ban-client status sshd
```

---

## পার্ট ৯ — ব্যাকআপ এবং রোলব্যাক

### ৯.১ কনফিগারেশন ব্যাকআপ স্ক্রিপ্ট

`/usr/local/bin/backup-config.sh` তৈরি করুন:

```bash
#!/bin/bash
BACKUP_DIR="/root/config-backups"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# গুরুত্বপূর্ণ কনফিগারেশন ব্যাকআপ
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

# ইউজার SSH কী ব্যাকআপ
tar -czf $BACKUP_DIR/ssh_keys_$DATE.tar.gz \
    /home/*/.ssh/authorized_keys

# ইউজার এবং UID-এর ডেটাবেজ
getent passwd | grep -E "^(johnd|janee)" > $BACKUP_DIR/users_$DATE.txt

echo "ব্যাকআপ সম্পন্ন: $BACKUP_DIR/config_$DATE.tar.gz"
```

**এক্সিকিউটেবল করুন এবং শিডিউল করুন:**

```bash
chmod +x /usr/local/bin/backup-config.sh
(crontab -l 2>/dev/null; echo "0 2 * * * /usr/local/bin/backup-config.sh") | crontab -
```

### ৯.২ রোলব্যাক পদ্ধতি

**SSH কনফিগারেশন পুনরুদ্ধার:**

```bash
# SSH বন্ধ করুন
systemctl stop ssh

# ব্যাকআপ পুনরুদ্ধার করুন
cp /etc/ssh/sshd_config.backup /etc/ssh/sshd_config

# SSH পুনরায় শুরু করুন
systemctl start ssh
systemctl status ssh
```

**ইউজার কোটা পুনরুদ্ধার:**

```bash
# ব্যাকআপ থেকে পুনরুদ্ধার করুন
edquota -p johnd janee
# অথবা XFS-এর জন্য:
xfs_quota -x -c 'limit -u bsoft=10g bhard=10g janee' /home
```

**systemd রিসোর্স লিমিট পুনরুদ্ধার:**

```bash
# ডিফল্টে রিসেট করুন
systemctl set-property user-1001.slice CPUQuota=
systemctl set-property user-1001.slice MemoryMax=
# অথবা ফাইল থেকে পুনরুদ্ধার:
systemctl import-environment
```

### ৯.৩ ডিজাস্টার রিকভারি প্ল্যান

1. **SSH লকআউট রিকভারি:**
   - VPS কনসোল অ্যাক্সেস ব্যবহার করুন (KVM/IPMI)
   - অথবা আউট-অফ-ব্যান্ড অ্যাক্সেসের জন্য হোস্টিং প্রদানকারীর সাথে যোগাযোগ করুন
   - ব্যাকআপ থেকে SSH কনফিগারেশন পুনরুদ্ধার করুন

2. **ইউজার ডেটা রিকভারি:**
   - `/home` ডিরেক্টরির নিয়মিত ব্যাকআপ
   - ব্যাকআপ সার্ভারের বাইরে সংরক্ষণ করুন (S3, ইত্যাদি)

3. **সিস্টেম রোলব্যাক:**
   - পূর্ববর্তী কনফিগারেশন সংরক্ষণ করুন
   - systemd ইউনিটের জন্য `systemctl revert` ব্যবহার করুন

**রোলব্যাকের পর ভেরিফিকেশন:**
```bash
# সব সিস্টেম টেস্ট করুন
ssh -p 2222 johnd@<server-ip> "echo OK"
docker ps
quota -v johnd
systemd-cgtop
```

---

## গুরুত্বপূর্ণ নিরাপত্তা নিয়ন্ত্রণের সারাংশ

| নিয়ন্ত্রণ | বাস্তবায়ন | ভেরিফিকেশন |
|-----------|------------|-------------|
| SSH নিরাপত্তা | পোর্ট 2222, রুট নয়, শুধুমাত্র কী | `sshd -t`, `ufw status` |
| ডিস্ক কোটা | প্রতি ইউজার 10GB | `quota -v user` |
| CPU সীমা | 2 কোরে (200% কোটা) | `systemd-cgtop` |
| মেমরি সীমা | প্রতি ইউজার 4GB | `systemd-cgtop` |
| ইউজার আইসোলেশন | 750 হোম পারমিশন, cgroups | `ls /home/other` |
| ডকার নিরাপত্তা | প্রতি ইউজার রুটলেস | `docker info` |
| ব্রুট ফোর্স | Fail2Ban | `fail2ban-client status` |
| ফায়ারওয়াল | UFW ডিফল্ট ডেনি | `ufw status` |
| অডিটিং | auditd | `auditctl -l` |
| আপডেট | অনঅ্যাটেন্ডেড-আপগ্রেড | `systemctl status unattended-upgrades` |

---

## সম্পূর্ণ ভেরিফিকেশনের জন্য চূড়ান্ত কমান্ড

```bash
#!/bin/bash
echo "=== SSH নিরাপত্তা ==="
sshd -t && echo "SSH কনফিগ OK"
ufw status | grep 2222
fail2ban-client status sshd

echo -e "\n=== রিসোর্স লিমিট ==="
for user in johnd janee; do
    uid=$(id -u $user)
    echo "$user (UID $uid):"
    systemctl show user-${uid}.slice | grep -E "(CPUQuota|MemoryMax|MemoryHigh)"
done

echo -e "\n=== ডিস্ক কোটা ==="
quota -v johnd
quota -v janee

echo -e "\n=== ইউজার আইসোলেশন ==="
su - johnd -c "ls /home/janee 2>&1 | head -1"
su - janee -c "ls /home/johnd 2>&1 | head -1"

echo -e "\n=== ডকার স্ট্যাটাস ==="
su - johnd -c "docker info | grep -E '(Server Version|Rootless)'"
su - janee -c "docker info | grep -E '(Server Version|Rootless)'"

echo -e "\n=== অডিটিং ==="
auditctl -l | wc -l
systemctl status auditd --no-pager | grep Active

echo -e "\n=== অটো আপডেট ==="
systemctl status unattended-upgrades --no-pager | grep Active
```

**আউটপুট সব সিস্টেম সঠিকভাবে কাজ করছে তা নিশ্চিত করবে।** যদি কোনো ভেরিফিকেশন ধাপ ব্যর্থ হয়, রোলব্যাক পদ্ধতি এবং ট্রাবলশুটিং বিভাগ দেখুন।

---

## ট্রাবলশুটিং টিপস

1. **SSH সংযোগ করতে না পারলে:**
   - VPS কনসোল ব্যবহার করে লগিন করুন
   - `systemctl status ssh` চেক করুন
   - `ufw status` চেক করুন

2. **ডকার কাজ না করলে:**
   - `systemctl --user status docker` চেক করুন
   - `loginctl enable-linger username` ভেরিফাই করুন
   - `$XDG_RUNTIME_DIR` সেট আছে কিনা চেক করুন

3. **কোটা কাজ না করলে:**
   - ফাইলসিস্টেম কোটা সাপোর্ট করে কিনা চেক করুন
   - `quotaon -v /home` চালান

4. **রিসোর্স লিমিট কাজ না করলে:**
   - cgroups v2 সক্রিয় কিনা চেক করুন
   - `systemctl show user-*.slice` চালান
