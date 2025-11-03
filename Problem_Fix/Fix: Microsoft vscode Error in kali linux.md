┌──(ashraful㉿kali)-[~]
└─$ sudo apt update         
Hit:1 https://packages.microsoft.com/repos/code stable InRelease
Hit:2 http://http.kali.org/kali kali-rolling InRelease
All packages are up to date.    
Warning: https://packages.microsoft.com/repos/code/dists/stable/InRelease: Policy will reject signature within a year, see --audit for details


### We have to fix this error: 
-------------------------------

#check the link in the file : 
>>> /etc/apt/sources.list.d/vscode.list

#remove the source list: 
>>> sudo rm /etc/apt/sources.list.d/vscode.list

## 2. Import Microsoft's GPG key securely
>>> curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | sudo gpg --dearmor -o /usr/share/keyrings/microsoft-archive-keyring.gpg

# 3. Add repo with explicit GPG verification (use your arch)
>>> echo "deb [arch=amd64 signed-by=/usr/share/keyrings/microsoft-archive-keyring.gpg] https://packages.microsoft.com/repos/code stable main" | sudo tee /etc/apt/sources.list.d/vscode.list

#To check your arch:
>>> dpkg --print-architecture

>>> deb [arch=amd64 signed-by=/usr/share/keyrings/microsoft-archive-keyring.gpg] https://packages.microsoft.com/repos/code stable main
>>> sudo apt update
