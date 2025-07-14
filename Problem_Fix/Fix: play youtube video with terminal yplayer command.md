Perfect — your `ytplay.sh` looks good! Now let’s turn this script into a **command** (`yplayer`) you can run from anywhere.

---

### ✅ Step-by-Step: Make `ytplay.sh` a global command `yplayer`

#### 🔧 1. **Move the script to a system-wide bin folder**

We’ll place it in `/usr/local/bin`, which is in your system’s `$PATH`.

Run:

```bash
sudo cp ytplay.sh /usr/local/bin/yplayer
```

#### 🔧 2. **Make sure it's executable**

```bash
sudo chmod +x /usr/local/bin/yplayer
```

---

### ✅ Now use it like this:

```bash
yplayer https://www.youtube.com/watch?v=GK351yoTViQ
```

It will instantly stream the video using `yt-dlp` and `mpv`.

---

### 📌 Optional Enhancements (Recommended)

To improve `yplayer` if no URL is passed, edit it like this:

```bash
sudo nano /usr/local/bin/yplayer
```

Replace content with:

```bash
#!/bin/bash

if [ -z "$1" ]; then
    echo "❗ Usage: yplayer <YouTube-URL>"
    exit 1
fi

yt-dlp -o - "$1" | mpv -
```

Save and exit.

---

You're done ✅

Let me know if you want:

* audio-only mode
* download support
* playlist streaming support

I'll update the script accordingly.
