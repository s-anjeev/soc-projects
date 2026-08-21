# Case 03 — File Hash Investigation: EICAR Test File

## Summary

This case demonstrates the process of investigating a file hash using VirusTotal to identify whether a file is malicious. The EICAR test file hash was submitted to VirusTotal and returned detections from 64 out of 66 security vendors. The Behavior tab revealed sandbox analysis results showing the file attempting to evade detection — demonstrating the complete file hash investigation workflow used in real SOC environments.    

## Scenario
During a security incident investigation, the analyst discovers a file that was dropped onto the victim's system by malicious actors. The hash of the file is generated and provided to the threat hunters to determine its history and identify whether it has been previously associated with malicious activity.   

In this scenario, the file, named payload.exe, was downloaded from an external malicious server and executed on the compromised workstation. The analyst should extract the file's hash and submit it to VirusTotal to determine what type of malware it is without executing the file.   

This case demonstrates that process using the EICAR test file — a well-known harmless file used by security professionals to test antivirus detection. While the file itself is not dangerous the investigation process is identical to analysing real malware.  

## Objective
Use VirusTotal to investigate a file hash, interpret vendor detection results, analyse sandbox behavior data, map findings to MITRE ATT&CK, and document findings professionally.   

## Tools Used
- VirusTotal

## IOC Investigated
- Type: File Hash (MD5)
- Value: 44d88612fea8a8f36de82e1278abb02f
- File Name: eicar.com
- File Size: 68 bytes
- Source: EICAR standard antivirus test file

## Investigation Steps
## Step 1 — Submit Hash to VirusTotal
The MD5 hash of the file was submitted to VirusTotal for analysis against security vendors.   

**What to look for:** The detection count is the first thing to check. A high number of vendor detections confirms the file is known malware. The threat categories and family labels tell you what type of malware it is. The community score shows how the wider security community rates the threat.   

**Finding:** 64 out of 66 security vendors flagged the file as malicious. The results showed:   
| **Field**            | **Details**                                      | 
|----------------------|--------------------------------------------------|
| **File Hash**        | `44d88612fea8a8f36de82e1278abb02f`               |
| **Detections**       | 64 out of 66 vendors                             |
| **Community Score**  | 3727                                             |
| **File Size**        | 68 bytes                                         |
| **Last Analysis**    | 7 minutes before investigation                   |
| **Threat Categories**| Virus, Trojan                                    |
| **Family Labels**    | EICAR, test, file                                |
| **Tags**             | `powershell`, `known-distributor`, `attachment`, `via-tor` |
    

The screenshot below shows the full detection results across all 66 vendors with 64 returning malicious verdicts.   
![Screenshot](https://github.com/s-anjeev/soc-projects/blob/main/04-Threat-Intelligence/case-03-File-Hash-Investigation/images/v-1.png)

## Step 2 — Review File Details
The Details tab was checked for additional information about the file including its properties, known names and any related files or URLs.   

**What to look for:** The Details tab shows the full file metadata including all hash types, file type, creation date and any names the file has been seen under. This helps confirm the file identity and find related threats.   

**Finding:** The Details tab confirmed the file properties and showed additional hash values for cross-referencing across different threat intelligence platforms. The file was identified as a standard EICAR test string distributed by Offensive Security for antivirus testing purposes.   

The screenshot below shows the file details including hash values and file properties.   
![Screenshot](https://github.com/s-anjeev/soc-projects/blob/main/04-Threat-Intelligence/case-03-File-Hash-Investigation/images/v-2.png)

## Step 3 — Analyse Sandbox Behavior
The Behavior tab was reviewed to understand what the file actually does when executed. Multiple sandboxes ran the file in isolated environments and recorded every action it performed.   

What to look for: The sandbox detections, behavior tags, dropped files and network communications sections all reveal what the file does when it runs. Evasion techniques are particularly important — files that detect sandbox environments and behave differently are more sophisticated threats that are harder to analyse.   

Finding: The Behavior tab revealed detailed sandbox analysis from 8 different environments:  
**Sandbox Detections:**
| **Sandbox**   | **Verdict**              |
|---------------|--------------------------|
| Zenbox        | MALWARE TROJAN           |
| Lastline      | MALWARE TROJAN           |
| OS X Sandbox  | MALWARE TROJAN EVADER    |    

The EVADER classification from OS X Sandbox is significant — it means the file detected it was being analysed and attempted to behave differently to avoid detection.   

**Behavior Tags:**
| **Tag**                  | **What It Means**                                                       |
|--------------------------|-------------------------------------------------------------------------|
| `checks-cpu-name`        | Checks the processor name, which can be used to detect virtual machines. |
| `detect-debug-environment` | Checks whether the file is being analyzed by a debugger or security tool. |
| `direct-cpu-clock-access` | Accesses the CPU clock directly to detect sandbox timing or analysis environments. |
| `long-sleep`             | Delays execution to potentially wait for automated sandbox analysis to finish. |
| `sets-process-name`      | Changes its process name to make identification or monitoring more difficult. |

**Activity Summary:**
| **Category**            | **Count**        |
|-------------------------|------------------|
| **MITRE Signatures**    | 6 Low, 46 High   |
| **Dropped Files**       | 9 total          |
| **Network Communications** | 30 DNS, 24 IP, 2 URL |

The 46 high severity MITRE signature matches indicate the file performs a wide range of techniques associated with malware behaviour across multiple attack categories.   


## Findings Summary

| **Field**              | **Details**                                      |
|------------------------|--------------------------------------------------|
| **IOC**                | `44d88612fea8a8f36de82e1278abb02f`               |
| **IOC Type**           | File Hash (MD5)                                  |
| **File Name**          | `eicar.com`                                       |
| **Vendor Detections**  | 64 out of 66                                      |
| **Community Score**    | 3727                                              |
| **Threat Categories**  | Virus, Trojan                                     |
| **Sandbox Verdicts**   | Malware Trojan, Malware Trojan Evader            |
| **Evasion Techniques** | Sandbox detection, long sleep, CPU clock access  |
| **Network Activity**   | 30 DNS queries, 24 IP connections, 2 URLs        |
| **Overall Assessment** | EICAR test file — not actual malware             |


## MITRE ATT&CK Mapping

| **Tactic**           | **Technique ID** | **Technique**                         | **What Was Observed**                                      |
|----------------------|------------------|---------------------------------------|------------------------------------------------------------|
| **Execution**        | `T1059`          | Command and Scripting Interpreter     | Code execution was observed during sandbox analysis.       |
| **Defense Evasion**  | `T1497`          | Virtualization/Sandbox Evasion        | Sandbox detection, CPU checks, and long sleep delays were observed. |
| **Discovery**        | `T1082`          | System Information Discovery          | The file checked system information, including CPU details. |
| **Command and Control** | `T1071`       | Application Layer Protocol             | DNS queries and IP connections were observed during sandbox execution. |
| **Persistence**      | `T1547`          | Boot or Logon Autostart Execution      | Persistence-related behavior was reported during sandbox analysis. |


## Conclusion
The investigation identified the file eicar.com using the MD5 hash 44d88612fea8a8f36de82e1278abb02f. Although VirusTotal reported a high number of detections and sandbox engines classified the file as malicious, the file was identified as the EICAR Antivirus Test File, which is intentionally designed to trigger antivirus and security products.   

## Key Takeaways
- File hashes let you identify known malware without running the file — always hash suspicious files before doing anything else with them
- A high detection count across many vendors is strong confirmation of malicious activity
- The Behavior tab is more powerful than the Detection tab — it shows what the file actually does not just whether vendors flag it
- Sandbox evasion techniques indicate a more sophisticated threat — files that detect analysis environments require more careful handling
- Always check all three tabs on VirusTotal — Detection, Details and Behavior together give the complete picture
- File hash analysis connects directly to incident response — knowing what a file does helps you understand the full scope of a breach