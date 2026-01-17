@echo off
echo ========================================
echo Akasa Teknik - Public Access Setup
echo ========================================
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: Please run this script as Administrator!
    echo Right-click the file and select "Run as administrator"
    pause
    exit /b 1
)

echo [1/4] Getting network information...
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /i "ipv4"') do set LOCAL_IP=%%a
set LOCAL_IP=%LOCAL_IP: =%

echo [2/4] Configuring Windows Firewall...
REM Allow Apache through firewall
netsh advfirewall firewall delete rule name="Apache HTTP" >nul 2>&1
netsh advfirewall firewall delete rule name="Apache HTTPS" >nul 2>&1

netsh advfirewall firewall add rule name="Apache HTTP" dir=in action=allow protocol=TCP localport=80 >nul
netsh advfirewall firewall add rule name="Apache HTTPS" dir=in action=allow protocol=TCP localport=443 >nul

if %errorLevel% equ 0 (
    echo    ✓ Firewall rules added successfully
) else (
    echo    ✗ Failed to add firewall rules
    pause
    exit /b 1
)

echo [3/4] Getting public IP address...
powershell -Command "(Invoke-WebRequest -Uri 'https://api.ipify.org').Content" > public_ip.txt
set /p PUBLIC_IP=<public_ip.txt
del public_ip.txt

echo [4/4] Creating access information...
echo.
echo ========================================
echo SETUP COMPLETED SUCCESSFULLY!
echo ========================================
echo.
echo Local Access:
echo    http://localhost/akasa_teknik
echo    http://%LOCAL_IP%/akasa_teknik
echo.
echo Public Access (after port forwarding):
echo    http://%PUBLIC_IP%/akasa_teknik
echo.
echo ========================================
echo NEXT STEPS:
echo ========================================
echo.
echo 1. ROUTER CONFIGURATION:
echo    - Open your router admin panel
echo    - Find Port Forwarding section
echo    - Forward port 80 to your computer (%LOCAL_IP%)
echo    - Forward port 443 to your computer (%LOCAL_IP%) for HTTPS
echo.
echo 2. TEST ACCESS:
echo    - Local: http://localhost/akasa_teknik
echo    - From other devices: http://%PUBLIC_IP%/akasa_teknik
echo.
echo 3. OPTIONAL - Get Free Domain:
echo    - Visit https://www.noip.com/
echo    - Register for free subdomain
echo    - Install their DUC client
echo.
echo 4. SECURITY RECOMMENDATIONS:
echo    - Change default XAMPP passwords
echo    - Keep software updated
echo    - Use HTTPS when possible
echo.
echo ========================================
echo.
echo Press any key to open your website locally...
pause >nul
start http://localhost/akasa_teknik

echo.
echo Press any key to open router setup guide...
pause >nul
start https://portforward.com/router.htm

echo.
echo Setup complete! Your website is ready for public access.
pause
