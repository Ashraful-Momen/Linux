The issue you're describing — **no noise during playback, but noise when idle** — is a **common audio interference or power-saving bug** in Linux systems, including Parrot OS (which is Debian-based). This typically happens when the audio device enters a **power-saving mode** when idle, causing unwanted electrical noise ("hissing", "buzzing", or "hum") through the speakers or sound box.

---

### ✅ Step-by-Step Fix

#### **1. Disable Audio Power Saving**

PulseAudio and ALSA can power down the audio card when idle. You can disable this behavior.

##### ✅ Method 1: Disable power saving for Intel HDA (common)

Edit or create this config file:

```bash
sudo nano /etc/modprobe.d/audio_disable_powersave.conf
```

Paste this:

```conf
options snd_hda_intel power_save=0 power_save_controller=N
```

Save and exit with `CTRL+O`, `ENTER`, then `CTRL+X`.

---

#### **2. Restart or Reboot**

Then either reboot:

```bash
sudo reboot
```

Or reload the module (less preferred):

```bash
sudo modprobe -r snd_hda_intel && sudo modprobe snd_hda_intel
```

---

#### **3. Optional: PulseAudio tweak (if still noisy)**

Create or edit this file:

```bash
sudo nano /etc/pulse/default.pa
```

Find and **comment out** (add `#` at start of line):

```
# load-module module-suspend-on-idle
```

This prevents PulseAudio from suspending the sound card when idle.

Then restart PulseAudio:

```bash
pulseaudio -k
pulseaudio --start
```

---

#### **4. Check Volume Levels**

Use `alsamixer` to fine-tune:

```bash
alsamixer
```

* Press `F6` to select your sound card.
* Lower unnecessary channels like `Mic Boost`, `Line In`, or `Auto-Mute`, which can sometimes cause noise.
* If “Auto-Mute” is available, try toggling it on/off.

---

### 🔁 Test again

1. Reboot after the changes.
2. Play a video, stop it, and check if the buzzing still happens.

---

### 🛠️ Still have noise?

Let me know:

* Your **sound card/chipset** (`lspci | grep -i audio`)
* Are you using **3.5mm jack**, **Bluetooth**, or **HDMI** output?
* Is the noise constant or only when the system is fully idle?

I’ll guide you further based on that.
