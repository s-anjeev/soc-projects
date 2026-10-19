# Case 01 — Suspicious Process Detection on WKSTN-07
## Executive Summary
A malware infection was detected on workstation WKSTN-07 at SecureCore Ltd. Sysmon logs revealed that a malicious file called update.exe was created by Chrome after the user likely clicked a malicious link. The malware connected to an external command and control server, dropped a second malicious file disguised as a legitimate Windows process, created a registry persistence mechanism, conducted reconnaissance across the system and attempted lateral movement to the domain controller.   
The attack was fully mapped using Sysmon process creation, network connection and registry modification events.  

## Scenario
A SOC alert fires at SecureCore Ltd showing unusual process activity on workstation WKSTN-07. Sysmon logs show a process called update.exe started running from an unusual location and immediately made a network connection to an external IP. It then created a new file and launched another process. As the SOC analyst on duty the task is to investigate what happened on this machine, trace the full attack chain and determine the scope of the compromise.   

## Objective
Use Sysmon logs in Splunk to investigate suspicious process activity, trace the complete attack chain from initial infection to lateral movement, identify all malicious indicators and document findings professionally.  

## Investigation Steps
## Step 1 — Load and Review Raw Logs
