I see the `apt-key` command is not found on your system. This is because newer versions of Kali Linux (and other Debian-based distributions) have deprecated the `apt-key` command. Let's use the newer, recommended method instead:

1. First, let's create the directory for trusted keys if it doesn't exist:

```bash
sudo mkdir -p /etc/apt/keyrings
```

2. Now download and add the Kali repository key:

```bash
wget -q -O - https://archive.kali.org/archive-key.asc | gpg --dearmor | sudo tee /etc/apt/keyrings/kali-archive-keyring.gpg > /dev/null
```

3. Create a source list file pointing to this key:

```bash
echo "deb [signed-by=/etc/apt/keyrings/kali-archive-keyring.gpg] http://http.kali.org/kali kali-rolling main non-free contrib" | sudo tee /etc/apt/sources.list.d/kali.list
```

4. Then update your package lists:

```bash
sudo apt update
```

If you're still having issues, you could try installing the kali-archive-keyring package directly:

```bash
sudo apt install kali-archive-keyring
```

And then update again:

```bash
sudo apt update
```

After resolving the key issue, you can proceed with upgrading your system:

```bash
sudo apt upgrade
```

These steps should help you fix the GPG key error using the current recommended approach in Kali Linux.
