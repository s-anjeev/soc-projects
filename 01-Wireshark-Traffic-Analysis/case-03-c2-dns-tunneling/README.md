# Case 03 — DNS Tunneling command and control.

### Summary
Analysis of the captured network traffic revealed that a compromised Windows workstation was using DNS tunneling as a covert Command-and-Control (C2) channel. The infected host repeatedly sent DNS A record queries to a single attacker-controlled domain. In response, the DNS server returned both a legitimate A record and a TXT record containing a Base64-encoded Windows command. This behavior demonstrates how DNS was abused to bypass traditional security controls while enabling persistent remote command execution and communication with the attacker's infrastructure.

### Scenario
The SOC team at ApexSecure Technologies receives an alert for suspicious DNS activity from a Windows workstation. Network monitoring reveals repeated DNS A record queries to a single external domain. As the SOC analyst, your task is to analyze the provided PCAP, identify the attacker-controlled domain, decode the commands delivered through DNS, and document the relevant Indicators of Compromise (IOCs).

### Objective
Analyze the provided PCAP file to identify DNS-based Command-and-Control (C2) communication, determine the attacker-controlled infrastructure, decode the Base64-encoded commands delivered through DNS TXT records, extract indicators of compromise (IOCs), and assess the scope and impact of the incident.

### Tools Used
1. Wireshark
2. Virustotal
3. Abuseipdb
4. cyberchef

### Dataset
- File: dns-tunneling-c2.pcapng
- Capture Date: 31/07/2026
- Total Packets: 20
- Source: HomeLab

## Investigation Steps
## Step 1 — Initial Traffic Overview
The PCAP file was opened in Wireshark and raw traffic was reviewed to understand the scope of activity before applying any filters.

**Finding:** 304 total packets were present. The traffic included DNS protocol only. The source IP 192.168.56.104 appeared to be the infected workstation making connections to multiple external servers.

## Step 2 — DNS Traffic Analysis
