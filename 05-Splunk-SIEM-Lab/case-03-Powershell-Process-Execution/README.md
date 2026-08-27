## Summary
An attacker used PowerShell to compromise workstation WKSTN-01 at SecureCode infotec. Attackers used powershell encoding to avoid detection, created a secret administrator account to maintain persistence, downloaded malware from an external server, ran it, and then started mapping out the internal network. This was a structured and deliberate attack that went undetected because there was no PowerShell monitoring in place.   

## Scenario
It is Saturday Evening at SecureCode infotec. The SIEM fires an alert about suspicious PowerShell activity on workstation WKSTN-01. The alert shows PowerShell was launched with unusual parameters from a process. As the analyst on duty the task is to investigate whether this is a legitimate administrator running a script or an attacker using PowerShell to compromise the machine.

## Objective
Use Splunk to investigate suspicious PowerShell execution, identify all malicious commands and techniques used, understand the full scope of the attack, and produce a clear professional report of the findings.

## Tools Used
- Splunk Enterprise
- SPL (Search Processing Language)

## Background — Why Attackers Use PowerShell
PowerShell is built into every Windows machine and is trusted by the operating system. That is exactly why attackers love it. They do not need to bring any external tools because everything they need is already there. They can download files, run code, create accounts and explore the network all through PowerShell without triggering most antivirus tools.   

## Investigation Steps
