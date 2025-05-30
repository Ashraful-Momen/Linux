This issue happens because your **APT can't fetch the package metadata correctly**, due to the **missing GPG key**, which blocks trusted access to the Kali repositories — creating a **chicken-and-egg problem**.

We’ll fix this by manually downloading and placing the GPG key without using `gpg`.

---

### ✅ **Fix Without GPG**

Use `curl` and `tee` to save the key directly in the right format:

```bash
curl -fsSL https://archive.kali.org/archive-key.asc | sudo tee /etc/apt/trusted.gpg.d/kali-archive-key.asc
```

Then:

```bash
sudo apt update
```

---

### 🔍 Explanation:

* `tee` is used here to write the file directly to the trusted keys location without needing `gpg`.
* This workaround works because `apt` also accepts `.asc` key files in `/etc/apt/trusted.gpg.d/`.

---

### ✅ If That Fails — Use Raw Keyserver Method

As a backup, try importing the missing key via `apt-key` (even though deprecated):

```bash
sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 827C8569F2518CC677FECA1AED65462EC8D5E4C5
```

> If `apt-key` is also missing, install it with:

```bash
sudo apt install -y apt-transport-https
```

---

After this, run:

```bash
sudo apt update
```

Let me know if it still fails or if you'd like help switching to a mirror or alternative repo.
