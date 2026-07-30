# Case 02 — DNS Tunneling Data Exfiltration Investigation

## Summary
Analysis of the captured network traffic revealed that a compromised Windows workstation was using DNS tunneling to exfiltrate sensitive data. The host generated repeated DNS queries containing Base64-encoded fragments of a confidential project file and sent them to an attacker-controlled DNS server. This activity indicates a successful data exfiltration attempt using DNS as a covert communication channel to evade traditional network security controls.


## Scenario
The SOC team at TechNova Solutions receives an alert indicating an unusually high volume of outbound DNS traffic from a Windows workstation. Network monitoring tools identify repeated DNS queries to an unfamiliar external domain.
Suspecting covert data exfiltration, the security team captures the network traffic and provides the PCAP file for investigation. As the SOC analyst on duty, your task is to analyze the packet capture, determine whether DNS tunneling was used, identify the attacker-controlled domain, extract the exfiltrated data, and document all relevant indicators of compromise (IOCs).

## Objective
Analyze the provided PCAP file to identify DNS tunneling activity, confirm data exfiltration, determine the attacker-controlled infrastructure, extract indicators of compromise (IOCs), and assess the scope and impact of the incident.

## Tools Used
1. Wireshark
2. Virustotal
3. Abuseipdb

## Dataset
- File: dns-tunneling-exfiltration.pcap
- Capture Date: 30/07/2026
- Total Packets: 12812
- Source: HomeLab

## Investigation Steps
## Step 1 — Initial Traffic Overview
The PCAP file was opened in Wireshark and raw traffic was reviewed to understand the scope of activity before applying any filters.

**Finding:** 12812 total packets were present. The traffic included a mix of DNS, TCP, ARP, TLS, and QUIC protocols. The source IP 10.0.3.15 appeared to be the infected workstation making connections to multiple external servers.

## Step 2 — DNS Traffic Analysis
The DNS filter was applied to filter out all DNS queries made by host.

**Filter used:**
```dns```


