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

===============================================================================================================================================
# Install the GPG key in Kali 
===============================================================================================================================================
I see the issue has returned after removing the sources.list.d file. Let's fix this properly:

1. First, let's check the content of your current sources.list:

```bash
cat /etc/apt/sources.list
```

It seems your sources.list may not be using the correct format with the signed-by directive. Let's make sure the key is installed properly and update your sources.list file correctly.

2. Let's work with the keyring file directly:

```bash
sudo wget -O /etc/apt/trusted.gpg.d/kali-archive-keyring.gpg https://archive.kali.org/archive-key.asc
```

3. Install the kali-archive-keyring package to make sure all necessary keys are present:

```bash
sudo apt install kali-archive-keyring --allow-unauthenticated
```

4. Now let's update your sources.list with the correct format:

```bash
sudo nano /etc/apt/sources.list
```

5. Replace the contents with:

```
# See https://www.kali.org/docs/general-use/kali-linux-sources-list-repositories/
deb http://http.kali.org/kali kali-rolling main contrib non-free non-free-firmware

# Additional line for source packages
# deb-src http://http.kali.org/kali kali-rolling main contrib non-free non-free-firmware
```

Notice that we're not using the signed-by directive here, as we'll rely on the system-wide keyring instead.

6. Save and exit (Ctrl+O, Enter, Ctrl+X)

7. Update again:

```bash
sudo apt update
```

If you're still having issues, let's try to import the specific missing key:

```bash
wget -qO - https://archive.kali.org/archive-key.asc | gpg --import
gpg --export 827C8569F2518CC677FECA1AED65462EC8D5E4C5 | sudo tee /usr/share/keyrings/kali-archive-keyring.gpg > /dev/null
```

Then modify your sources.list file:

```bash
sudo nano /etc/apt/sources.list
```

And update to:

```
# See https://www.kali.org/docs/general-use/kali-linux-sources-list-repositories/
deb [signed-by=/usr/share/keyrings/kali-archive-keyring.gpg] http://http.kali.org/kali kali-rolling main contrib non-free non-free-firmware

# Additional line for source packages
# deb-src [signed-by=/usr/share/keyrings/kali-archive-keyring.gpg] http://http.kali.org/kali kali-rolling main contrib non-free non-free-firmware
```

Then update again:

```bash
sudo apt update
```

This should finally resolve the GPG key issue.
