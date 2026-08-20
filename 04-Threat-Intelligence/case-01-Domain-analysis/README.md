# Case 01 — Domain Analysis: dl.myresult.co.za

## Summary
During the Wireshark malware traffic investigation, a system was observed downloading files from dl.myresult.co.za over HTTP. The domain was submitted to VirusTotal for reputation analysis; however, 5 out of 91 security vendors flagged it as malicious. Further investigation revealed that the domain had been associated with malware distribution and was used to host or distribute malicious files. This finding confirmed that the suspicious download activity observed in the network capture was consistent with known malware distribution activity.   

## Scenario
During a Wireshark investigation, a host was found downloading files from dl.myresult.co.za over HTTP. The domain was considered suspicious due to the observed file-download activity.  

As part of the investigation, the domain was submitted to public threat intelligence sources to determine its reputation and gather additional context about its potential involvement in malware distribution.  


## Objective
To investigate the suspicious domain using public threat intelligence sources, assess its reputation, identify any association with malware distribution, determine the risk posed to the affected host and document findings professionally.     

## Tools Used
1. VirusTotal
2. URLhaus

## IOC Investigated
- Type: Domain
- Value: dl.myresult.co.za
- Source: Wireshark malware traffic analysis

## Investigation Steps
## Step 1 — Submit Domain to VirusTotal
The domain dl.myresult.co.za was submitted to VirusTotal for reputation analysis against 94 security vendors.  

What VirusTotal does: VirusTotal checks the submitted indicator against over 90 security vendors simultaneously and returns a verdict from each one. A high number of detections confirms malicious activity. Zero detections does not automatically mean the indicator is safe — it means it has not been widely reported yet.  

Finding: 5 out of 91 security vendors flagged dl.myresult.co.za as malicious. 3 out of 91 security vendors flagged it as suspecious. The last analysis was performed approximately 28 minutes before this investigation.  

The screenshot below shows the full detection results across all 94 vendors with every vendor returning a clean verdict.   

![Screenshot](https://github.com/s-anjeev/soc-projects/blob/main/04-Threat-Intelligence/case-01-Domain-analysis/image/virustotal-1.png)

## Step 2 — Investigate Further Using the Relations Tab
A clean VirusTotal result does not end the investigation. The Relations tab was checked for additional context.  
**Finding:** The Relations tab reveals that the domain is associated with multiple suspicious files, including .lnk, .vbs, .bat, and .zip files. Some of these files were flagged as malicious by multiple security vendors, providing further evidence of the domain's association with malware activity.   

Communicating files indicate observed network communication between a file and the domain, whereas referring files indicate that the domain was referenced or embedded within the file.  

This provides strong evidence that the domain is involved in malware distribution.   

![Screenshot](https://github.com/s-anjeev/soc-projects/blob/main/04-Threat-Intelligence/case-01-Domain-analysis/image/virustotal-2.png)


##  Step 3 — Submit Domain to URLHouse
URLHouse is a community-driven threat intelligence platform operated by abuse.ch that collects and shares malicious URLs used for malware distribution.  

For further clarification and confirmation, the domain was submitted to URLhaus. The results showed that the domain had been used to distribute malicious files, including .bat, .ps1, and .vbs files.   

The screenshot below shows the URLHouse scan results.   

![Screenshot](https://github.com/s-anjeev/soc-projects/blob/main/04-Threat-Intelligence/case-01-Domain-analysis/image/url-hause.png)


## Findings Summary

| **Field** | **Details** |
|---|---|
| **IOC** | `dl.myresult.co.za` |
| **IOC Type** | Domain |
| **VirusTotal Detections** | 5 / 91 (malicious)|
| **VirusTotal Detections** | 3 / 91 (malicsuspeciousious)|
| **Context Found** | Associated with malware distribution |
|**IP** | 82.165.179.225|
| **Associated Files** | `.lnk`, `.vbs`, `.bat`, `.zip` |
| **URLhaus Finding** | Domain associated with malicious file distribution |
| **Behaviour Observed** | Files downloaded from the domain over HTTP |
| **Overall Assessment** | **Suspicious — strong evidence of association with malware distribution** |   

## MITRE ATT&CK Mapping

| **Technique** | **MITRE ID** | **Reasoning** |
|---|---|---|
| **Application Layer Protocol: Web Protocols** | **T1071.001** | The host communicated with `dl.myresult.co.za` using HTTP. |
| **Ingress Tool Transfer** | **T1105** | The host downloaded files from the external domain, consistent with the transfer of potentially malicious files. |   

## Conclusion
The investigation identified dl.myresult.co.za as a suspicious domain associated with malware distribution. VirusTotal reported 5/91 detections, further CTI enrichment through VirusTotal Relations and URLhaus revealed associations with multiple malicious files. The observed HTTP file downloads were consistent with the domain's known malicious activity, providing strong evidence that the domain was involved in malware delivery.   

## Key Takeaways
- IOC enrichment using multiple CTI sources provides additional context.
- VirusTotal Relations can reveal files associated with a suspicious domain.
- URLhaus can provide valuable information about domains involved in malware distribution.
- Multiple sources should be correlated before making a final assessment.
- Threat intelligence helps turn a suspicious IOC into actionable security information.