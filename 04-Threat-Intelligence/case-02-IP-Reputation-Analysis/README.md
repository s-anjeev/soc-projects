# Case 02 — IP Reputation Analysis: AbuseIPDB
## Summary
This case demonstrates how to use AbuseIPDB to check the reputation of a suspicious IP address identified during a security investigation. A recently reported malicious IP was selected from the AbuseIPDB live feed and analysed in detail. This IP address has been reported a total of 43,374 times from 336 distinct sources and confirmed as actively engaged in abusive behaviour at the time of investigation.   

## Scenario
During a SOC investigation, unusual outbound connections were detected from a corporate system to an unknown external IP address. Before escalating a finding or blocking an IP it is important to check whether that IP has been reported by other organisations as malicious.   

## Objective
Use AbuseIPDB to investigate a reported malicious IP address, interpret the confidence score and report history, understand the attack categories, and document findings professionally.  

## IOC Investigated
Type: IP Address  
Value: 193.163.125.218  
Source: AbuseIPDB recently reported IPs live feed  

## Investigation Steps
## Step 1 — Search the IP on AbuseIPDB
The IP address 193.163.125.218 was searched on AbuseIPDB to retrieve its full report history and reputation score.  
**What to look for:** The first things to check are the confidence score and total report count. A high confidence score with many reports from multiple distinct sources is strong confirmation of malicious activity. Even a low confidence score with many recent reports warrants attention.   

**Finding:** The IP was found in the AbuseIPDB database with the following details:  
| **Field** | **Details** |
|---|---|
| **IP Address** | `193.163.125.218` |
| **Abuse Confidence Score** | 100% |
| **Total Reports** | 43,374 |
|**Distinct Sources**|336|
| **ISP** | Driftnet Ltd |
| **Usage Type** | Data Center/Web Hosting/Transit |
| **ASN** | AS211298 |
| **Hostname(s)** | `r1-218-da.census.internet-measurement.com` |
| **Domain Name** | `driftnet.io` |
| **Country** | United Kingdom |
| **City** | Salford, England |
|**Last Reported**|4 minutes|

The screenshot below shows the full AbuseIPDB report for 193.163.125.218 including the confidence score, report count and IP information.   

![Screenshot]()

## Step 2 — Interpret the Results
The confidence score for this IP address being malicious is 100%. The IP has been reported more than 4,300 times by 336 distinct sources, indicating a strong history of involvement in malicious activities. The results of this analysis strengthen the assessment that the presence of this IP address in the corporate network is malicious and requires appropriate actions.   

The Usage Type: The IP is classified as Data Center/Web Hosting rather than a residential connection. This is significant because:  
- Data center IPs are commonly used to host servers, applications.
- Attackers frequently use compromised or rented servers for command-and-control (C2), malware hosting, scanning, and other malicious activities.
- A data center IP with multiple abuse reports is a strong indicator of malicious infrastructure.   


The Attack Category: Most historical reports for this IP are related to port scanning, with some reports also indicating brute-force attacks.   

The Recent Activity The most recent report was filed just 4 minutes before this investigation. AbuseIPDB also displayed a warning stating the IP was potentially still actively engaged in abusive activities. This means the threat was live and ongoing at the time of investigation.   

## Findings Summary

| **Field** | **Details** |
|---|---|
| **IOC** | `193.163.125.218` |
| **IOC Type** | IP Address |
| **Abuse Confidence Score** | **100%** |
| **Total Reports** | **43,374** |
| **Distinct Reporting Sources** | **336** |
| **Attack Category** | Port Scanning; Brute Force |
| **ISP** | Driftnet Ltd |
| **Usage Type** | Data Center/Web Hosting/Transit |
| **ASN** | AS211298 |
| **Hostname** | `r1-218-da.census.internet-measurement.com` |
| **Domain Name** | `driftnet.io` |
| **Location** | Salford, England, United Kingdom |
| **Threat Status** | Strongly associated with abusive activity |
| **Overall Assessment** | **Malicious — high-confidence IP with extensive abuse history** |  



## MITRE ATT&CK Mapping

| **Technique** | **MITRE ID** | **Reasoning** |
|---|---|---|
| **Network Service Scanning** | **T1046** | The IP has a significant history of port-scanning activity, consistent with attackers identifying open ports and exposed services during reconnaissance. |
| **Brute Force** | **T1110** | Historical reports associated with the IP include brute-force activity, indicating attempts to gain access through repeated credential-guessing attempts. |     


## Conclusion

The investigation identified `193.163.125.218` as a high-confidence malicious IP address. Its **100% Abuse Confidence Score**, **43,374 abuse reports**, and **336 distinct reporting sources** indicate a significant history of abusive activity. The reported port-scanning and brute-force activity is consistent with reconnaissance and unauthorized access attempts. Based on the available threat intelligence, communication with this IP should be treated as **high risk**.    

## Key Takeaways

- A high Abuse Confidence Score and large number of reports are strong indicators of malicious activity.
- Multiple independent reporting sources increase confidence in the reputation assessment.
- Port scanning can indicate reconnaissance activity aimed at identifying exposed services.
- Brute-force reports may indicate attempts to gain unauthorized access.
- Data center IPs can be used to host scanning, attack, or other malicious infrastructure.