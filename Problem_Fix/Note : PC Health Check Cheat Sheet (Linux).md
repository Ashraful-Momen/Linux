Sure! Here’s a concise **PC Health Check Cheat Sheet** you can keep handy for quick hardware and performance checks on your Linux system like Parrot OS:

---

# 🖥️ PC Health Check Cheat Sheet (Linux)

### 1. **CPU Info & Load**

```bash
lscpu
top                 # real-time CPU/memory load
htop                # interactive process viewer (install if needed)
```

### 2. **CPU Temperature & Sensors**

```bash
sudo apt install lm-sensors   # if not installed
sudo sensors-detect           # detect sensors (run once)
sensors                      # display temps & fan speeds
```

### 3. **CPU Frequency Scaling Mode**

```bash
cat /sys/devices/system/cpu/cpu*/cpufreq/scaling_governor
```

### 4. **Memory Usage**

```bash
free -h
vmstat 1 5
```

### 5. **Disk Space & Usage**

```bash
df -h
lsblk
```

### 6. **SSD/HDD Health & Info (SMART)**

```bash
sudo apt install smartmontools    # if not installed
sudo smartctl -a /dev/sda         # replace /dev/sda with your drive
sudo smartctl -x /dev/sda         # detailed info
```

### 7. **Disk Read/Write Speed**

```bash
sudo sync; dd if=/dev/zero of=/tmp/testfile bs=1G count=1 oflag=direct
sudo sync; dd if=/tmp/testfile of=/dev/null bs=1G count=1 iflag=direct
rm /tmp/testfile
```

### 8. **Check Filesystem Type**

```bash
df -Th
```

### 9. **Check & Enable TRIM (for SSD)**

```bash
sudo systemctl status fstrim.timer
sudo systemctl enable --now fstrim.timer
```

### 10. **Check System Uptime & Load Average**

```bash
uptime
```

### 11. **Network Status**

```bash
ip addr
ping -c 4 google.com
```

---

# ⚙️ Tips for Better Performance & Health

* Use **`performance`** governor for max CPU speed (may increase power and heat).
* Keep **TRIM enabled** on SSDs for longevity.
* Monitor temperatures regularly to avoid overheating.
* Clean up disk space often.
* Update your system regularly:

  ```bash
  sudo apt update && sudo apt upgrade
  ```

---

If you want, I can prepare a script that runs these checks and outputs a summary report for you!
