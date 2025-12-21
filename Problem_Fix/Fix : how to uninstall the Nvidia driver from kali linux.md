The **NVIDIA Settings** window shows that the **GPU** (NVIDIA GeForce GT 1030) is using **Mesa** as the vendor for EGL, which is indicative of the open-source **Nouveau** driver being active instead of the proprietary NVIDIA driver. This could be why your second display connected to the GPU isn't functioning as expected.

### How to Fix:

1. **Ensure the NVIDIA Driver is Installed Correctly**:
   The NVIDIA driver needs to be installed and properly configured, replacing the open-source **Nouveau** driver.

   To check if the Nouveau driver is still loaded, run:

   ```bash
   lsmod | grep nouveau
   ```

   If `nouveau` is listed, the open-source driver is active. You need to disable it.

2. **Blacklist the Nouveau Driver**:
   To ensure that the **Nouveau** driver is disabled, follow these steps:

   * Create a new blacklist configuration file for Nouveau:

     ```bash
     sudo nano /etc/modprobe.d/blacklist-nouveau.conf
     ```
   * Add the following lines to disable the Nouveau driver:

     ```
     blacklist nouveau
     options nouveau modeset=0
     ```
   * Save the file (`Ctrl + O`), then exit (`Ctrl + X`).

3. **Update the initramfs**:
   After blacklisting Nouveau, update the initramfs to apply the changes:

   ```bash
   sudo update-initramfs -u
   ```

4. **Reboot the System**:
   Now, reboot your machine to ensure the changes take effect:

   ```bash
   sudo reboot
   ```

5. **Reinstall the NVIDIA Driver**:
   After rebooting, reinstall the NVIDIA proprietary driver (if not done already):

   ```bash
   sudo apt install nvidia-driver
   ```

6. **Check if the NVIDIA Driver is Active**:
   After installation, verify the driver is working and check the GPU status again:

   ```bash
   nvidia-smi
   ```

   It should now show the NVIDIA GPU being used with the correct driver (not **Mesa**).

7. **Configure Displays**:
   Now you can configure your displays using `nvidia-settings`:

   ```bash
   sudo nvidia-settings
   ```

Check if both monitors are detected and configured properly.

Let me know if this resolves your issue!
