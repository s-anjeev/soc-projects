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
![Screenshot]()

## Step 2 — Review File Details


