You're currently in `powersave` mode on all CPU cores. Let's switch your Parrot OS to full **performance mode**.

---

## 🔧 Quick One-Time Change (until reboot)

Run this command to switch **all cores** to performance mode **immediately**:

```bash
for CPU in /sys/devices/system/cpu/cpu*/cpufreq/scaling_governor; do
    echo performance | sudo tee $CPU
done
```

Then verify:

```bash
cat /sys/devices/system/cpu/cpu*/cpufreq/scaling_governor
```

You should now see:

```
performance
performance
performance
performance
```

---

## 🔄 Make It Permanent (Survive Reboot)

### ✅ 1. Install `cpufrequtils`

```bash
sudo apt install cpufrequtils
```

### ✅ 2. Create config file

```bash
sudo nano /etc/default/cpufrequtils
```

Paste:

```bash
GOVERNOR="performance"
```

Save with: `CTRL+O`, `ENTER`, then `CTRL+X`.

### ✅ 3. Enable the service

```bash
sudo systemctl enable cpufrequtils
sudo systemctl restart cpufrequtils
```

---

## ✅ Optional: Disable TLP (power management daemon)

If installed, it may override CPU settings.

Check if it's running:

```bash
systemctl status tlp
```

If active, disable it:

```bash
sudo systemctl disable --now tlp
```

---

## 🧪 Confirm CPU Frequency

Watch live CPU MHz to confirm it stays high:

```bash
watch -n 1 "cat /proc/cpuinfo | grep MHz"
```

---

Let me know if you'd like an **auto-performance script** for startup!
