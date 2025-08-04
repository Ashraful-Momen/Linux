Based on your Docker configuration, I can see that GitLab is using Docker Compose and is bound to all interfaces (0.0.0.0). The easiest solution is to use Nginx as a reverse proxy with IP restrictions. Here's the step-by-step solution:

## Solution: Nginx Reverse Proxy with IP Restrictions

### 1. First, modify your GitLab Docker to bind only to localhost

Navigate to your GitLab directory and edit the docker-compose.yml:

```bash
cd /home/dev/gitlab
sudo nano docker-compose.yml
```

Change the ports section from:
```yaml
ports:
  - "80:80"
  - "443:443"
  - "22:22"
```

To:
```yaml
ports:
  - "127.0.0.1:8080:80"    # Bind GitLab to localhost only
  - "127.0.0.1:4443:443"   # Bind HTTPS to localhost only
  - "127.0.0.1:2222:22"    # Bind SSH to localhost only
```

### 2. Restart GitLab container

```bash
cd /home/dev/gitlab
sudo docker-compose down
sudo docker-compose up -d
```

### 3. Install and configure Nginx

```bash
# Install Nginx
sudo apt update
sudo apt install nginx

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Create GitLab site configuration
sudo nano /etc/nginx/sites-available/gitlab
```

### 4. Add this Nginx configuration:

```nginx
server {
    listen 80;
    server_name 119.148.19.197;

    # Allow only specific IPs - ADD YOUR ALLOWED IPs HERE
    allow 119.148.19.196;
    allow 192.168.1.100;    # Add your other allowed IP
    allow 10.0.0.50;        # Add another allowed IP if needed
    deny all;               # Deny all other IPs

    # Security headers
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host $host;
        proxy_set_header X-Forwarded-Port $server_port;

        # GitLab specific headers
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_cache_bypass $http_upgrade;
        
        # Increase timeouts for GitLab
        proxy_connect_timeout 300;
        proxy_send_timeout 300;
        proxy_read_timeout 300;
        send_timeout 300;
    }

    # GitLab specific locations
    location ~ ^/(assets|uploads)/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

# HTTPS configuration (optional)
server {
    listen 443 ssl;
    server_name 119.148.19.197;

    # Add SSL certificate if you have one
    # ssl_certificate /path/to/cert.pem;
    # ssl_certificate_key /path/to/key.pem;

    # Allow only specific IPs
    allow 119.148.19.196;
    allow 192.168.1.100;    # Add your other allowed IPs
    deny all;

    location / {
        proxy_pass http://127.0.0.1:4443;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
        proxy_set_header X-Forwarded-Host $host;
        proxy_set_header X-Forwarded-Port $server_port;
    }
}
```

### 5. Enable the site and test configuration:

```bash
# Enable the site
sudo ln -s /etc/nginx/sites-available/gitlab /etc/nginx/sites-enabled/

# Test Nginx configuration
sudo nginx -t

# Start and enable Nginx
sudo systemctl enable nginx
sudo systemctl restart nginx
```

### 6. Update UFW rules:

```bash
# Remove old Docker rules
sudo ufw delete allow from 119.148.19.196 to any port 80

# Allow HTTP/HTTPS through Nginx only
sudo ufw allow 'Nginx Full'

# Or be more specific
sudo ufw allow from 119.148.19.196 to any port 80 proto tcp
sudo ufw allow from 119.148.19.196 to any port 443 proto tcp

# Add rules for other allowed IPs
sudo ufw allow from 192.168.1.100 to any port 80 proto tcp
sudo ufw allow from 192.168.1.100 to any port 443 proto tcp
```

### 7. Verify the setup:

```bash
# Check Nginx status
sudo systemctl status nginx

# Check GitLab container
sudo docker ps

# Check UFW status
sudo ufw status verbose

# Test from allowed IP (should work)
curl -I http://119.148.19.197

# Check Nginx access logs
sudo tail -f /var/log/nginx/access.log
```

### 8. Update GitLab external URL (Important!)

Since GitLab is now behind a proxy, update its configuration:

```bash
sudo nano /home/dev/gitlab/config/gitlab.rb
```

Add or modify:
```ruby
external_url 'http://119.148.19.197'

# Trust the reverse proxy
nginx['listen_addresses'] = ['127.0.0.1']
nginx['listen_port'] = 80

# Reverse proxy settings
gitlab_rails['trusted_proxies'] = ['127.0.0.1']
```

Then reconfigure GitLab:
```bash
sudo docker exec -it gitlab gitlab-ctl reconfigure
```

## Quick Test:

1. **From allowed IP** (119.148.19.196): Should access GitLab normally
2. **From any other IP**: Should get "403 Forbidden" error
3. **Check logs**: `sudo tail -f /var/log/nginx/access.log`

## Benefits of this approach:

- ✅ Simple to implement
- ✅ GitLab only accessible via localhost (Docker can't bypass UFW)
- ✅ Nginx handles IP restrictions efficiently
- ✅ Easy to add/remove allowed IPs
- ✅ Better logging and monitoring
- ✅ Can add SSL/HTTPS easily later
