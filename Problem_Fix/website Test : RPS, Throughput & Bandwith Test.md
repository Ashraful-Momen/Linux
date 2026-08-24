**hands-on** with measuring **RPS**, **Throughput**, and **Bandwidth** using real tools.

We’ll cover 3 common load-testing tools:
1️⃣ **Apache Bench (ab)** — simplest CLI tool
2️⃣ **k6** — modern & developer-friendly
3️⃣ **JMeter** — GUI-based for complex test plans

---

## 🧩 1️⃣ Apache Bench (ab)

### 🔹 Installation

```bash
sudo apt install apache2-utils   # Linux
```

---

### 🔹 Example Command

```bash
ab -n 1000 -c 50 https://example.com/
```

**Parameters:**

* `-n 1000` → Total 1000 requests
* `-c 50` → 50 concurrent users

---

### 🔹 Sample Output

```
Concurrency Level:      50
Time taken for tests:   20.123 seconds
Complete requests:      1000
Failed requests:        0
Total transferred:      12800000 bytes
Requests per second:    49.71 [#/sec] (mean)
Transfer rate:          635.99 [Kbytes/sec] received
```

### 🔹 How to Calculate

| Metric              | Formula                                                           | Result |
| ------------------- | ----------------------------------------------------------------- | ------ |
| **RPS**             | Given → `49.71 req/sec`                                           | ✅      |
| **Throughput**      | Transfer rate = `635.99 KB/sec` = **0.62 MB/sec**                 | ✅      |
| **Bandwidth Usage** | If your network = 10 Mbps → `(0.62×8)/10×100 = 49.6% utilization` | ✅      |

---

## 🧩 2️⃣ k6 (modern & scriptable)

### 🔹 Installation

```bash
sudo apt install k6
```

---

### 🔹 Example Test Script (`test.js`)

```js
import http from 'k6/http';
import { check, sleep } from 'k6';

export let options = {
  vus: 50,         // Virtual users
  duration: '30s', // Test duration
};

export default function () {
  let res = http.get('https://example.com/');
  check(res, { 'status was 200': (r) => r.status == 200 });
  sleep(1);
}
```

---

### 🔹 Run Test

```bash
k6 run test.js
```

---

### 🔹 Sample Output

```
http_reqs................: 1500  50.00/s
data_received............: 15 MB  512.3 kB/s
data_sent................: 1.2 MB  42.1 kB/s
```

### 🔹 Metrics:

| Metric                    | Value                                                  |
| ------------------------- | ------------------------------------------------------ |
| **RPS**                   | `50 req/s`                                             |
| **Throughput (download)** | `512.3 KB/s`                                           |
| **Throughput (upload)**   | `42.1 KB/s`                                            |
| **Bandwidth Usage**       | If 10 Mbps line → `(0.512×8)/10×100 = 41%` utilization |

✅ k6 automatically shows RPS & throughput — super easy.

---

## 🧩 3️⃣ JMeter (for complex tests)

### 🔹 Use Case

If you need multi-endpoint tests, login flow, token-based auth, or distributed load.

---

### 🔹 Key Steps

1. Open **Apache JMeter** GUI
2. Add → *Thread Group*

   * Users: 100
   * Ramp-up: 10 sec
   * Loop Count: 10
3. Add → *HTTP Request Sampler*

   * URL: [https://example.com/api](https://example.com/api)
4. Add → *Summary Report* or *Aggregate Report*

---

### 🔹 Output Example

| Label | #Samples | Average (ms) | RPS | Throughput (KB/sec) |
| ----- | -------- | ------------ | --- | ------------------- |
| /api  | 1000     | 200          | 50  | 1024                |

✅ Directly shows both **RPS** and **Throughput**.

---

## 🔬 Quick Comparison

| Tool                  | Best For                    | Output             | Pros              |
| --------------------- | --------------------------- | ------------------ | ----------------- |
| **Apache Bench (ab)** | Quick & simple tests        | Text summary       | Lightweight       |
| **k6**                | Scriptable modern load test | JSON + CLI metrics | CI/CD friendly    |
| **JMeter**            | Complex workflows           | Detailed reports   | GUI visualization |

---

## 🧠 Summary Formulas Recap

| Metric                    | Formula                                | Example                |
| ------------------------- | -------------------------------------- | ---------------------- |
| **RPS**                   | Requests ÷ Seconds                     | 1000 ÷ 20 = 50 req/s   |
| **Throughput**            | Data ÷ Seconds                         | 12 MB ÷ 20 = 0.6 MB/s  |
| **Bandwidth Utilization** | (Throughput × 8 ÷ Max Bandwidth) × 100 | (0.6×8 ÷ 10)×100 = 48% |

---

Would you like me to show **a live example with sample output interpretation for a Laravel API endpoint** (like `/api/users`)?
It’ll include realistic RPS, latency, and throughput numbers you can expect.
