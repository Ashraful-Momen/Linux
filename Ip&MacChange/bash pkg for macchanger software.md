Here's a Bash script that provides an interactive interface for managing MAC addresses using `macchanger`, including an option to make the change **permanent** by updating the `network interfaces` configuration file (Debian/Ubuntu-based systems):

---

### ✅ `mac_manager.sh` – A Script to Manage MAC Address (with Permanent Option)

```bash
#!/bin/bash

INTERFACE="eth0"

show_menu() {
  echo "========== MAC Manager =========="
  echo "1. Show current MAC address"
  echo "2. Show list of MAC address vendors"
  echo "3. Change MAC address (temporary)"
  echo "4. Change MAC address (permanent)"
  echo "5. Change IP address"
  echo "6. Show current IP and MAC address"
  echo "7. Restore original MAC address"
  echo "8. Exit"
  echo "=================================="
}

change_mac_temp() {
  read -p "Enter new MAC address: " NEW_MAC
  sudo ip link set $INTERFACE down
  sudo macchanger -m $NEW_MAC $INTERFACE
  sudo ip link set $INTERFACE up
}

change_mac_perm() {
  read -p "Enter new MAC address to set permanently: " NEW_MAC

  CONF_FILE="/etc/network/interfaces.d/$INTERFACE.cfg"
  BACKUP_FILE="${CONF_FILE}.bak"

  sudo cp $CONF_FILE $BACKUP_FILE

  # Remove any existing hwaddress line and append new one
  sudo sed -i '/hwaddress/d' $CONF_FILE
  echo "hwaddress ether $NEW_MAC" | sudo tee -a $CONF_FILE

  echo "Permanent MAC set in $CONF_FILE (Backup: $BACKUP_FILE)"
  echo "Reboot your system or restart networking service to apply."
}

change_ip() {
  read -p "Enter new IP address (e.g., 192.168.1.100/24): " NEW_IP
  sudo ip addr flush dev $INTERFACE
  sudo ip addr add $NEW_IP dev $INTERFACE
  sudo ip link set $INTERFACE up
}

while true; do
  show_menu
  read -p "Choose an option [1-8]: " CHOICE
  case $CHOICE in
    1) sudo macchanger -s $INTERFACE ;;
    2) sudo macchanger -l $INTERFACE ;;
    3) change_mac_temp ;;
    4) change_mac_perm ;;
    5) change_ip ;;
    6) ifconfig $INTERFACE ;;
    7) 
       sudo ip link set $INTERFACE down
       sudo macchanger -p $INTERFACE
       sudo ip link set $INTERFACE up
       ;;
    8) echo "Exiting..."; break ;;
    *) echo "Invalid option, please try again." ;;
  esac
  echo ""
done
```

---

### 🛠 Instructions:

1. Save the script:

   ```bash
   nano mac_manager.sh
   ```

2. Paste the script above.

3. Make it executable:

   ```bash
   chmod +x mac_manager.sh
   ```

4. Run it with sudo:

   ```bash
   sudo ./mac_manager.sh
   ```

---

### ⚠️ Notes:

* The "permanent" MAC change works by modifying the `/etc/network/interfaces.d/` config, which applies on boot or network restart. Not all systems use this, especially those with `NetworkManager`.
* For systems with `NetworkManager`, changes must be made via `nmcli`.

Let me know your system type if you want a version for `NetworkManager`.
