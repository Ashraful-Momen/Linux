Perfect 👌 — here’s your **complete, clean, and professional hand note** for installing **Bangla fonts (like Noto Sans Bengali)** on **Parrot OS (Debian-based)**, including **all error handling** and **common permission fixes**.

---

# 🧠 **Hand Note: Install Bangla Fonts on Parrot OS (with Error Handling)**

---

## 🪜 Step 1: Update System Packages

```bash
sudo apt update && sudo apt upgrade -y
```

✅ Ensures latest font rendering libraries.

---

## 🪜 Step 2: Install Core Bangla & Shaping Libraries

```bash
sudo apt install fonts-beng-extra fonts-noto-core fonts-noto-ui-core fonts-noto-unhinted fonts-noto-extra \
                 libpango1.0-0 libharfbuzz0b -y
```

🧩

* `fonts-beng-extra`: adds traditional Bangla fonts
* `libharfbuzz` & `libpango`: fix Bangla letter-shaping and ligature rendering

---

## 🪜 Step 3: Manual Installation (if you downloaded the font)

Example: You have this folder
`~/Software/Bangla Font/Noto_Sans_Bengali`

### 🧩 3.1 Create local fonts directory

```bash
mkdir -p ~/.local/share/fonts
```

### 🧩 3.2 Fix any permission issues (very common)

```bash
sudo chown -R $USER:$USER ~/.local/share/fonts
chmod -R u+rwX ~/.local/share/fonts
```

### 🧩 3.3 Copy downloaded Bangla fonts

```bash
cp -r ~/Software/Bangla\ Font/Noto_Sans_Bengali/* ~/.local/share/fonts/
```

⚠️ **Important:**
👉 Don’t use `sudo` with `cp` here.
If you used `sudo` earlier, run:

```bash
sudo rm -rf /root/.local/share/fonts
sudo chown -R $USER:$USER ~/.local/share/fonts
```

---

## 🪜 Step 4: Rebuild Font Cache

```bash
fc-cache -f -v
```

✅ Output should show your fonts folder:

```
/home/shuvo/.local/share/fonts: caching, new cache contents: 1 fonts, 0 dirs
```

---

## 🪜 Step 5: Verify Font Installation

```bash
fc-list | grep -i "Bengali"
```

✅ Should show something like:

```
/home/shuvo/.local/share/fonts/NotoSansBengali-VariableFont_wdth,wght.ttf: Noto Sans Bengali:style=Regular
```

---

## 🪜 Step 6: (Optional) Set System Preference for Bangla Font

Create or edit `/etc/fonts/local.conf`:

```bash
sudo nano /etc/fonts/local.conf
```

Add:

```xml
<?xml version="1.0"?>
<!DOCTYPE fontconfig SYSTEM "fonts.dtd">
<fontconfig>
  <match target="pattern">
    <test name="lang" compare="contains">
      <string>bn</string>
    </test>
    <edit name="family" mode="prepend">
      <string>Noto Sans Bengali</string>
    </edit>
  </match>
</fontconfig>
```

Then rebuild cache again:

```bash
sudo fc-cache -f -v
```

---

## 🪜 Step 7: Restart Browser or App

Restart **Firefox / Chromium / VS Code / LibreOffice**,
then test with:
👉 [https://www.prothomalo.com](https://www.prothomalo.com)

✅ Bangla text should now render perfectly (no broken letters or boxes).

---

## 🪜 Step 8: (Optional) Enable Bangla Typing

```bash
sudo apt install ibus-m17n -y
ibus-setup
```

Then add:

> Bengali → `bn-probhat` or `bn-inscript`

Restart:

```bash
ibus restart
```

---

## ⚙️ Common Errors & Fixes

| 🧩 Error                                         | 🩹 Solution                                                                                                            |
| ------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------------- |
| `Permission denied` while copying                | Run → `sudo chown -R $USER:$USER ~/.local/share/fonts`                                                                 |
| `fc-cache` shows only `/root/.local/share/fonts` | You used `sudo cp` — remove `/root/.local/share/fonts` and reinstall fonts as normal user                              |
| Bangla text shows as boxes                       | Missing shaping libs → `sudo apt install libpango1.0-0 libharfbuzz0b -y`                                               |
| Bangla text broken or disconnected               | Add fontconfig (`/etc/fonts/local.conf`) fix                                                                           |
| Still not showing correctly in Firefox           | Firefox → Settings → Fonts → disable "Allow pages to choose their own fonts" and select **Noto Sans Bengali** manually |

---

## ✅ Quick Summary (One-Line Fix Script)

```bash
sudo apt update && sudo apt install -y fonts-beng-extra fonts-noto-core libpango1.0-0 libharfbuzz0b && \
mkdir -p ~/.local/share/fonts && sudo chown -R $USER:$USER ~/.local/share/fonts && \
cp -r ~/Software/Bangla\ Font/Noto_Sans_Bengali/* ~/.local/share/fonts/ && \
fc-cache -f -v && fc-list | grep -i Bengali
```

---

Would you like me to make this into a **PDF cheat sheet (for offline reference)** with commands and troubleshooting tables formatted nicely?
