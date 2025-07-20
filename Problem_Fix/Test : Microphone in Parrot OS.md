The error message means your USB mic **doesn’t support stereo recording** (2 channels). Many headset mics only support **mono** (1 channel).

Let’s fix that by forcing mono input:

---

### ✅ Correct Command for Mono Mic

```bash
arecord -D hw:1,0 -f cd -c1 test-mic.wav
```

Explanation:

* `-c1`: Use **1 channel** (mono)
* `-f cd`: 16-bit, 44100Hz
* `-D hw:1,0`: Your USB mic device

---

### ▶️ Then Play It Back:

```bash
aplay test-mic.wav
```

---

If it works and you hear yourself: 🎉 mic is working fine — just needed correct settings.


