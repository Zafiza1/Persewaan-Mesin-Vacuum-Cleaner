<?php
/**
 * Public Access Setup Guide
 * 
 * This file helps configure your website for public internet access
 */

// Get server information
$server_ip = $_SERVER['SERVER_ADDR'] ?? 'Unknown';
$server_name = $_SERVER['SERVER_NAME'] ?? 'Unknown';
$document_root = $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown';

// Get public IP
$public_ip = file_get_contents('https://api.ipify.org') ?: 'Unknown';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Public Access Setup - Akasa Teknik</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .info-box { background: #f0f8ff; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .warning { background: #fff3cd; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .step { margin: 15px 0; padding: 10px; border-left: 4px solid #007bff; }
        code { background: #f8f9fa; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🌐 Public Access Setup - Akasa Teknik</h1>
    
    <div class="info-box">
        <h3>Current Server Information:</h3>
        <ul>
            <li><strong>Local IP:</strong> <?php echo $server_ip; ?></li>
            <li><strong>Public IP:</strong> <?php echo $public_ip; ?></li>
            <li><strong>Server Name:</strong> <?php echo $server_name; ?></li>
            <li><strong>Document Root:</strong> <?php echo $document_root; ?></li>
        </ul>
    </div>

    <div class="warning">
        <h3>⚠️ Security Warning</h3>
        <p>Making your website public means anyone can access it. Make sure:</p>
        <ul>
            <li>Change default passwords</li>
            <li>Update all software</li>
            <li>Use HTTPS if possible</li>
            <li>Backup your data regularly</li>
        </ul>
    </div>

    <h2>🚀 Option 1: Cloudflare Tunnel (Recommended & Free)</h2>
    <div class="step">
        <h3>Step 1: Install Cloudflare Tunnel</h3>
        <p>Download and install Cloudflared:</p>
        <code>winget install --id Cloudflare.cloudflared</code>
    </div>
    
    <div class="step">
        <h3>Step 2: Login to Cloudflare</h3>
        <code>cloudflared tunnel login</code>
        <p>This will open browser for authentication</p>
    </div>
    
    <div class="step">
        <h3>Step 3: Create Tunnel</h3>
        <code>cloudflared tunnel create akasa-website</code>
    </div>
    
    <div class="step">
        <h3>Step 4: Configure Tunnel</h3>
        <p>Create config file: <code>%USERPROFILE%\.cloudflared\config.yml</code></p>
        <pre>
tunnel: akasa-website
credentials-file: %USERPROFILE%\.cloudflared\[TUNNEL_ID].json

ingress:
  - hostname: your-domain.com
    service: http://localhost:80
  - service: http_status:404
        </pre>
    </div>
    
    <div class="step">
        <h3>Step 5: Run Tunnel</h3>
        <code>cloudflared tunnel run akasa-website</code>
    </div>

    <h2>🌍 Option 2: Port Forwarding</h2>
    <div class="step">
        <h3>Step 1: Configure Apache</h3>
        <p>Edit <code>C:\xampp\apache\conf\httpd.conf</code>:</p>
        <pre>
Listen 0.0.0.0:80
ServerName 0.0.0.0:80
        </pre>
    </div>
    
    <div class="step">
        <h3>Step 2: Port Forwarding</h3>
        <p>Access your router admin panel and forward:</p>
        <ul>
            <li>External Port: 80 → Internal Port: 80 (your computer IP)</li>
            <li>External Port: 443 → Internal Port: 443 (for HTTPS)</li>
        </ul>
    </div>
    
    <div class="step">
        <h3>Step 3: Windows Firewall</h3>
        <p>Allow Apache through Windows Firewall:</p>
        <code>netsh advfirewall firewall add rule name="Apache" dir=in action=allow protocol=TCP localport=80</code>
    </div>
    
    <div class="step">
        <h3>Step 4: Access Your Website</h3>
        <p>Use your public IP: <code>http://<?php echo $public_ip; ?>/akasa_teknik</code></p>
    </div>

    <h2>🎯 Option 3: Free Dynamic DNS</h2>
    <div class="step">
        <h3>Step 1: Get Free Domain</h3>
        <p>Register at:</p>
        <ul>
            <li><a href="https://www.noip.com/" target="_blank">No-IP</a> - Free subdomain</li>
            <li><a href="https://freedns.afraid.org/" target="_blank">FreeDNS</a> - Free domains</li>
        </ul>
    </div>
    
    <div class="step">
        <h3>Step 2: Install DUC Client</h3>
        <p>Download and install Dynamic Update Client from your DNS provider</p>
    </div>
    
    <div class="step">
        <h3>Step 3: Configure with Port Forwarding</h3>
        <p>Combine with Option 2 (Port Forwarding) but use your domain instead of IP</p>
    </div>

    <h2>📱 Testing Access</h2>
    <div class="success">
        <h3>Test Your Website:</h3>
        <ul>
            <li>Local: <code>http://localhost/akasa_teknik</code></li>
            <li>Network: <code>http://<?php echo $server_ip; ?>/akasa_teknik</code></li>
            <li>Public: <code>http://<?php echo $public_ip; ?>/akasa_teknik</code></li>
        </ul>
    </div>

    <h2>🔧 Quick Setup Script</h2>
    <div class="step">
        <h3>Automated Setup (Windows)</h3>
        <p>Save this as <code>setup_public.bat</code> and run as Administrator:</p>
        <pre>
@echo off
echo Configuring Apache for public access...

REM Allow Apache through firewall
netsh advfirewall firewall add rule name="Apache HTTP" dir=in action=allow protocol=TCP localport=80
netsh advfirewall firewall add rule name="Apache HTTPS" dir=in action=allow protocol=TCP localport=443

echo Firewall rules added successfully!
echo.
echo Next steps:
echo 1. Configure port forwarding in your router
echo 2. Access your website via: http://%PUBLIC_IP%/akasa_teknik
echo.
pause
        </pre>
    </div>

    <div class="info-box">
        <h3>📞 Need Help?</h3>
        <p>For technical support, contact your hosting provider or IT administrator.</p>
    </div>

    <script>
        // Copy IP to clipboard functionality
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('IP Address copied to clipboard!');
            });
        }
    </script>
</body>
</html>
