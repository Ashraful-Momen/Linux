To install **all Kali Linux tools and apps** in **WSL**, you can use the `kali-linux-everything` metapackage — but **this is not recommended inside WSL** unless you **really need all tools**, as it will:

* Download **tens of GBs** of tools (over 10,000+ packages)
* Include GUI tools (which won’t work well or at all in a standard WSL shell unless you set up WSLg or an X server)

---

### ✅ If You Still Want to Proceed

Run this command:

```bash
sudo apt update && sudo apt install -y kali-linux-everything
```

This will attempt to install the **full Kali toolset**.

---

### ⚠️ Important Notes for WSL Users

* GUI tools (like Wireshark, Burp Suite, etc.) **require a GUI environment** (use [WSLg](https://learn.microsoft.com/en-us/windows/wsl/tutorials/gui-apps) on Windows 11 or an external X server like X410/VcXsrv).
* Some tools requiring **raw socket access, USB access, or low-level network** may not work correctly in WSL (like `aircrack-ng`, `reaver`, etc.).
* **Better approach**: Only install tools you need using individual metapackages.

---

### 🧠 Recommended: Install by Category

If you want only specific categories of tools (e.g., wireless, web, forensics), use:

```bash
sudo apt install -y kali-tools-top10      # Top 10 Kali tools
sudo apt install -y kali-linux-wireless   # Wireless tools
sudo apt install -y kali-tools-web        # Web hacking tools
sudo apt install -y kali-tools-forensics  # Forensics tools
sudo apt install -y kali-tools-passwords  # Password attacks
```

You can view all tool categories with:

```bash
apt-cache search kali-linux
```

---

Would you like a script that installs only the most useful WSL-compatible tools instead of everything?
