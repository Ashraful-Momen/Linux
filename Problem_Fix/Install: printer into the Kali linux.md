# How to Find and Install a Network Printer on Linux

## Step 1: Find the Printer's IP Address

**Check your own IP first** to know which subnet to search:

```bash
ip addr show | grep "inet " | grep -v "127.0.0.1"
```

**Ping to verify the printer is reachable:**

```bash
ping -c 3 172.17.15.100
```

**Identify the printer model** via its web interface:

```bash
curl -s -L http://172.17.15.100 | grep -i "<title>"
```

This returns the printer model name (e.g., `iR C3226`).

## Step 2: Install CUPS (Print Server)

```bash
sudo apt install -y cups cups-client printer-driver-cups-pdf
```

## Step 3: Start the CUPS Service

```bash
sudo systemctl enable --now cups
```

## Step 4: Add the Printer

```bash
# General format:
sudo lpadmin -p <PrinterName> -E -v "ipp://<PRINTER_IP>/ipp/print" -m everywhere

# Example for Canon iR C3226 at 172.17.15.100:
sudo lpadmin -p Canon-iRC3226 -E -v "ipp://172.17.15.100/ipp/print" -m everywhere
```

- `-p` = printer name (your choice)
- `-E` = enable the printer
- `-v` = device URI (IPP URL of the printer)
- `-m everywhere` = auto-detect driver via IPP Everywhere

## Step 5: Set as Default Printer

```bash
sudo lpadmin -d Canon-iRC3226
```

## Step 6: Test Print

```bash
echo "Test page" | lp
```

## Common Print Commands

| Command | Purpose |
|---|---|
| `lp document.pdf` | Print a file |
| `lp -n 3 document.pdf` | Print 3 copies |
| `lp -o sides=two-sided-long-edge doc.pdf` | Double-sided print |
| `lpstat -p` | Show printer status |
| `lpq` | View print queue |
| `lprm <job-id>` | Cancel a print job |
| `lpstat -p -d` | Show all printers + default |

## CUPS Web Interface

Manage printers visually at: **http://localhost:631**
