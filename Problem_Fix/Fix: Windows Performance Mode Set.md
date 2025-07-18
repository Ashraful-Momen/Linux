To activate **Windows "Performance Mode"**, you can use **Ultimate Performance Mode**, which is a power plan designed for high-end PCs to maximize performance (especially useful in development, gaming, or heavy tasks like video editing or Docker workloads).

Here’s how to enable it:

---

### 🟦 Step 1: Enable Ultimate Performance Mode via Command Prompt

1. Press `Windows + X` → select **Terminal (Admin)** or **Command Prompt (Admin)**
2. Paste and run this command:

```bash
powercfg -duplicatescheme e9a42b02-d5df-448d-aa00-03f14749eb61
```

This command will add the **Ultimate Performance** power plan.

---

### 🟦 Step 2: Activate the Power Plan

1. Press `Windows + R`, type: `powercfg.cpl` → hit **Enter**
2. You’ll see the **Ultimate Performance** plan appear
3. Select it to activate

> If it's not visible, click **“Show additional plans”**

---

### 🟨 Optional: Use PowerShell (Alternative Method)

```powershell
Add-WindowsCapability -Online -Name "Server-Performance-Feature"
```

Useful for Windows 11/10 Pro for Workstations or Server editions.

---

### 🛑 Notes

* Ultimate Performance mode is **only available by default on Workstation/Server editions**, but the above command works on most Windows 10/11 Pro & Home.
* If you're using a **laptop**, enabling this mode may **increase power consumption** and reduce battery life.
* For better performance, you can also disable background apps and startup programs from **Task Manager → Startup tab**.

---

