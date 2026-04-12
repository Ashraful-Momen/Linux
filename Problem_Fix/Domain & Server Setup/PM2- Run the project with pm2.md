
---

# 🔧 PM2 Command Cheat Sheet (Updated with Laravel)

---

## 📦 Install PM2

```bash
npm install -g pm2
```

---

## ▶️ Start Applications

```bash
pm2 start app.js
pm2 start app.js --name my-app
pm2 start app.js --watch
pm2 start npm --name "nextjs-app" -- start
```

---

## 🟣 Run Laravel Project with PM2

### ✅ Option 1: Run Laravel Dev Server

```bash
pm2 start "php artisan serve --host=0.0.0.0 --port=8000" --name laravel-app
```

---

### ✅ Option 2: Run Laravel Queue Worker (Recommended for production)

```bash
pm2 start "php artisan queue:work" --name laravel-queue
```

For better stability:

```bash
pm2 start "php artisan queue:work --sleep=3 --tries=3" --name laravel-queue
```

---

### ✅ Option 3: Run Laravel Scheduler (Cron Alternative)

```bash
pm2 start "php artisan schedule:work" --name laravel-scheduler
```

---

### ⚠️ Important Notes

* PM2 does **NOT replace a proper web server** like Nginx or Apache
* For production Laravel:

  * Use **Nginx + PHP-FPM**
  * Use PM2 only for:

    * Queue workers
    * Scheduler
    * Background jobs

---

## 📋 List & Status

```bash
pm2 list
pm2 status
pm2 show <app-name>
```

---

## ⏹️ Stop / Restart / Delete

```bash
pm2 stop <app-name>
pm2 restart <app-name>
pm2 delete <app-name>

pm2 stop all
pm2 restart all
pm2 delete all
```

---

## 🔄 Reload (Zero Downtime)

```bash
pm2 reload <app-name>
pm2 reload all
```

---

## 📊 Monitoring

```bash
pm2 monit
```

---

## 📜 Logs

```bash
pm2 logs
pm2 logs <app-name>
pm2 logs --lines 100
```

---

## ⚙️ Process Management

```bash
pm2 scale <app-name> 4
pm2 restart <app-name> --update-env
```

---

## 💾 Save & Startup (Auto Start on Boot)

```bash
pm2 save
pm2 startup
```

---

## 🧠 Ecosystem File (Advanced)

```bash
pm2 init simple
pm2 start ecosystem.config.js
```

Example:

```js
module.exports = {
  apps: [
    {
      name: "my-app",
      script: "app.js",
      instances: "max",
      exec_mode: "cluster",
      watch: true,
      env: {
        NODE_ENV: "development"
      },
      env_production: {
        NODE_ENV: "production"
      }
    }
  ]
};
```

---

## 🌍 Useful Flags

```bash
--watch
--ignore-watch
--env production
--instances max
```

---

## 🧹 Cleanup

```bash
pm2 flush
pm2 kill
```

---

## 🚀 Next.js (Production)

```bash
npm run build
pm2 start npm --name "next-app" -- start
```

---

## 🔥 Pro Tips

* Always use `--name`
* Use cluster mode:

```bash
pm2 start app.js -i max
```

* Combine with Nginx for production

---

