To install the optimization script into `/usr/local/bin` so you can run it **anytime from the terminal as a command**, follow these steps:

---

### ✅ 1. **Create the script directly in `/usr/local/bin/optimize-parrot`**

Run the following command to open it in a text editor:

```bash
sudo nano /usr/local/bin/optimize-parrot
```

Paste this full script:

```bash
#!/bin/bash

echo "🔧 Starting Parrot OS optimization for old hardware..."

# 1. 🧠 Install zram for compressed RAM swap
echo "➡️ Installing zram-tools..."
apt update && apt install -y zram-tools

# 2. 🧠 Set swappiness to 10 (reduce swapping to disk)
echo "➡️ Lowering swappiness to 10..."
echo "vm.swappiness=10" > /etc/sysctl.d/99-swappiness.conf
sysctl -p /etc/sysctl.d/99-swappiness.conf

# 3. 🧠 Clear RAM cache now (you can run this anytime manually)
echo "➡️ Clearing RAM cache..."
sh -c 'echo 3 > /proc/sys/vm/drop_caches'

# 4. 🔁 Install Lightweight Desktop Environment (XFCE4)
echo "➡️ Installing XFCE4 Desktop Environment..."
#apt install -y xfce4

# 5. 💡 Disable unused services
echo "➡️ Disabling unused system services..."
#systemctl disable bluetooth.service
systemctl disable cups.service

# 6. 💽 Enable SSD TRIM
echo "➡️ Enabling SSD TRIM timer..."
systemctl enable fstrim.timer

echo "✅ Optimization complete. Please reboot your system."
```

Save and exit (`Ctrl+O`, `Enter`, then `Ctrl+X`).

---

### ✅ 2. **Make the script executable**

```bash
sudo chmod +x /usr/local/bin/optimize-parrot
```

---

### ✅ 3. **Run the script anytime with:**

```bash
sudo optimize-parrot
```

---

> 📝 **Note**: Since the script installs packages and edits system settings, you **must use `sudo`** to run it.

Let me know if you want a version that asks you which parts to enable (like a menu).
