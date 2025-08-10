# Allow SSH only from your IP
sudo ufw allow from 192.168.3.64 to any port 22 proto tcp

# Deny SSH from all other IPs
sudo ufw deny 22/tcp

# Enable UFW if not already enabled
sudo ufw enable

# Check rules
sudo ufw status numbered
