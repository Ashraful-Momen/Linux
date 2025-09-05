#First Access the ec2 console with pem key then change the /ect/ssh/sshd_conf and /etc/ssh/sshd_config.d/60-cloudimg-settings.conf to =>  PasswordAuthentication yes

Your main `/etc/ssh/sshd_config` says `PasswordAuthentication yes`, but AWS’s cloud-init dropped this file:

```
/etc/ssh/sshd_config.d/60-cloudimg-settings.conf
```

which contains:

```
PasswordAuthentication no
```

👉 That **overrides** your main config, so password logins are blocked.

---

### 🔧 Fix

1. Edit the cloud-init override file:

```bash
sudo nano /etc/ssh/sshd_config.d/60-cloudimg-settings.conf
```

2. Change:

```
PasswordAuthentication no
```

to:

```
PasswordAuthentication yes
```

3. Save & exit (`CTRL+O`, `Enter`, `CTRL+X` in nano).

---

### 🔄 Restart SSH

```bash
sudo systemctl restart ssh
```

---

### 🔑 Ensure `ubuntu` has a password

```bash
sudo passwd ubuntu
```

(set something strong, e.g. `MyStr0ngP@ssw0rd`).

---

### 🚀 Test login from your local

```bash
ssh ubuntu@52.90.128.202
```

👉 It should now prompt for the password instead of rejecting with `publickey`.

---

⚠️ **Security Tip**: Since password login makes brute-force attacks possible, go into your **AWS Security Group** and restrict port `22` to your own IP (not `0.0.0.0/0`).

---

Do you want me to give you the **exact AWS Security Group rule** setup (step by step in AWS console) so only your home IP can SSH in with a password?
