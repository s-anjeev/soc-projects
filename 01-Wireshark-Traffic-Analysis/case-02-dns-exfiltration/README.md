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
1. ### Conversation Analysis:
    Analysis of the conversations revealed that every DNS request sent from 10.0.3.15 to 103.165.206.238 had an identical packet size of 144 bytes. Each conversation consisted of only a single packet from the client to the server, with no response packets observed from 103.165.206.238 back to 10.0.3.15. This indicates completely unidirectional communication (Client → Server), which is unusual for normal DNS traffic.

2. ### Unanswered DNS Queries: 
    **Filter used:** ```dns.flags.response == 0 && !dns.response_in```
    A total of 90 unanswered DNS queries were identified. Most of these requests originated from 10.0.3.15 and were sent to 103.165.206.238 without receiving any DNS response.

3. ### Long DNS Query Names:
    **Filter used:** ```dns.qry.name.len > 50```
    A total of 76 DNS queries were identified with unusually long query names. Every one of these requests originated from 10.0.3.15 and was sent to 103.165.206.238.

4. ### High-Entropy Subdomains:
    Further examination of the long query names showed that all 76 queries contained very long, random-looking (high-entropy) subdomains. The subdomains appeared to consist of encoded strings rather than human-readable hostnames

5. ### Communication with 103.165.206.238:
    **Filter used:**  ```ip.addr == 103.165.206.238 && udp.port == 53```
    A total of 77 DNS packets were observed between 10.0.3.15 and 103.165.206.238. All queries requested A records for the domain testdomain.com.

6. ### Domain Analysis:
    **Filter used:** ```dns.qry.name contains "testdomain"```
    All 77 DNS queries targeted testdomain.com. Although the primary domain remained the same, every request used a different subdomain, each containing a long encoded string.

Based on the investigation, 10.0.3.15 generated a total of 77 DNS A-record queries to the DNS server 103.165.206.238.
![Screenshot]()

**Indicators of Compromise (IOCs)**

| IOC Type | Value |
|---------|--------|
|Source IP|10.0.3.15|
|Destination IP|103.165.206.238|
|Domain|alpha.testdomain.com|
|DNS Query Pattern| <random-encoded-string>.alpha.testdomain.com|
|DNS Record Type|A|
|Protocolv | DNS (UDP/53)|
|Number of Queries	| 77|

**Behavioral IOCs :**
- 77 DNS A-record queries sent to the same external DNS server.
- Every query used a different high-entropy subdomain.
- All DNS requests were exactly 144 bytes.
- Communication was unidirectional (client → server).
- No DNS responses were received.
- Repeated use of the same parent domain (alpha.testdomain.com).

## Step 3 — UDP Stream Analysis

UDP stream analysis was limited because each stream contained only a single DNS request, resulting in 77 separate UDP streams. After decoding and reconstructing the encoded subdomains, the extracted data was identified as a sensitive project file, confirming that the DNS queries were used for data exfiltration via DNS tunneling.
![Screenshot]()

## Step 4 — Threat Intelligence Investigation

**Investigation 1 — 103.165.206.238 on AbuseIPDB and VirusTotal**

The destination IP address 103.165.206.238 was analyzed using multiple threat intelligence sources. VirusTotal reported a detection score of 14/91, indicating that several security vendors classify the IP as malicious or suspicious.
![Screenshot]()
Additionally, AbuseIPDB shows that the IP has been reported multiple times for abusive activity.
![Screenshot]()

## Findings Summary

|Finding  |	Detail |
|--------------------|-----------------------------------------------------------------------------------|
|Affected Host |	10.0.3.15|
|Destination IP|	103.165.206.238|
|Protocol|	DNS (UDP/53)|
|Total DNS Queries|	77|
|DNS Record Type|	A|
|Primary Domain|	alpha.testdomain.com|
|Subdomain Pattern|	Long, unique, high-entropy encoded subdomains|
|Communication Pattern|	Unidirectional communication (client → server); no DNS responses received|
|Packet Size|	All DNS requests were exactly 144 bytes|
|Decoded Payload|	Sensitive project file reconstructed from encoded DNS subdomains|
|Threat Intelligence|	VirusTotal: 14/91 detections; AbuseIPDB: Destination IP reported multiple times 
for abusive activity|
|Attack Technique|	DNS tunneling used for data exfiltration|
|MITRE ATT&CK|	T1048.003 – Exfiltration Over Unencrypted/Obfuscated Non-C2 Protocol (DNS)|
|Severity	| High|

## Conclusion

The investigation confirmed that 10.0.3.15 was used to exfiltrate data through DNS tunneling. The host generated 77 DNS A-record queries containing long, high-entropy encoded subdomains directed to 103.165.206.238. After decoding and reconstructing the DNS payloads, the transmitted data was identified as a sensitive project file. Threat intelligence further supported the findings, with the destination IP receiving multiple abuse reports and malicious detections

## Recommended Actions

- Immediately isolate the affected host (10.0.3.15) from the network.
- Block communication with 103.165.206.238 and associated domains at the firewall and DNS resolver.
- Perform a full malware scan and forensic investigation on the affected host.
- Reset credentials if sensitive information may have been exposed.
- Review DNS logs across the environment for similar high-entropy queries or communication with the same infrastructure.
- Implement DNS monitoring and alerting for long or high-entropy subdomains and abnormal DNS query volumes.

