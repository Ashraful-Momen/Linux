You're encountering an error when trying to change your IP address using `macchanger`:

```bash
sudo macchanger -a 192.168.3.35
[ERROR] Set device name: No such device
```

### ❌ What's wrong?

The command you're using is incorrect for two reasons:

---

## 🔧 1. **Misuse of `macchanger`**

- `macchanger` is used to **change the MAC address** of a network interface, not the **IP address**.
- The syntax is:  
  ```bash
  sudo macchanger [options] <interface>
  ```
  Example:
  ```bash
  sudo macchanger -a eth0
  ```

So this part:
```bash
sudo macchanger -a 192.168.3.35
```
is trying to treat an **IP address as a network interface**, which doesn't exist — hence the error:
> `[ERROR] Set device name: No such device`

---

## 🛑 2. You Can't Just Assign Any IP Address

Even if you were trying to set an IP address (which you're not doing correctly), you can't just assign yourself `192.168.3.35` unless:

- That IP is **not already in use**
- It's within the **same subnet** as your network
- Your network allows it (i.e., no DHCP restrictions or MAC filtering)

From your ARP scan output, it seems like `192.168.3.35` is **already assigned** to a device:
```
192.168.3.35    b4:2e:99:11:22:5c      1      60  GIGA-BYTE TECHNOLOGY CO.,LTD.
```

Trying to assign that same IP manually will cause a **conflict**.

---

## ✅ How to Change Your IP Address Correctly

To change your IP address on Kali Linux (or any Debian-based system), follow these steps:

### Option A: Using `ip` command (temporary)

1. **Find your network interface**:
   ```bash
   ip a
   ```
   Look for something like `eth0`, `enp0s3`, or `wlan0`.

2. **Bring the interface down**:
   ```bash
   sudo ip link set <interface> down
   ```

3. **Assign a new IP address**:
   ```bash
   sudo ip addr add <new-ip>/24 dev <interface>
   ```

4. **Bring the interface up**:
   ```bash
   sudo ip link set <interface> up
   ```

Example:
```bash
sudo ip link set eth0 down
sudo ip addr add 192.168.3.200/24 dev eth0
sudo ip link set eth0 up
```

> ⚠️ Make sure `192.168.3.200` is **not already taken** and is within the correct range.

---

### Option B: Use DHCP to get a new IP

If you're on a DHCP-enabled network, you can just request a new IP:

```bash
sudo dhclient -r  # release current IP
sudo dhclient     # request a new one
```

---

## 📝 Summary

| Task | Command |
|------|---------|
| View interfaces | `ip a` |
| Change MAC address | `sudo macchanger -a <interface>` |
| Change IP address | `sudo ip addr add <ip>/24 dev <interface>` |
| Renew IP via DHCP | `sudo dhclient -r && sudo dhclient` |

---

## ✅ Final Tip

If you're testing in a controlled lab environment:
- Consider setting up a static IP range.
- Or configure your router/DHCP server to give specific IPs based on MAC addresses.

Let me know if you want help writing a script or configuring a static IP!
