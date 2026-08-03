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
1. Conversation Analysis:
    During the 3-minute capture, a total of 10 DNS A record queries were sent from 192.168.56.104 to the DNS server 192.168.56.102 for testdomain.com. Every query was successfully resolved with an A record. However, each successful response also included an additional TXT record, which is not typical for standard DNS resolution and may indicate abnormal DNS behavior requiring further investigation.

2. Query Timing:
    The DNS requests were generated at a highly consistent interval of approximately 12 seconds between each query. Such precise periodicity is uncommon in normal user-driven DNS activity, where requests are typically irregular and depend on user actions or application behavior. Regular, fixed-interval DNS traffic can indicate automated communication, such as beaconing, or command-and-control (C2) activity.

3. TXT record:
    Analysis of the DNS responses shows that every successful A record lookup also included an additional TXT record, even though the client requested only an A record. This behavior is unusual because standard DNS A record queries typically return only the requested record type unless there is a specific configuration or purpose. Furthermore, the returned TXT records do not resemble typical TXT domain verification records. Instead, they contain non-standard content, making them suspicious.

4. Query Name Analysis:
    No evidence of long DNS query names or high-entropy subdomains was detected. The DNS queries were short, consistent, and targeted the same domain throughout the capture.

5. Repeted DNS Queries:
    Over a period of 3 minutes, the client repeatedly queried testdomain.com for an A record, despite each previous request being successfully resolved with the same result. This pattern is unusual because DNS clients typically use cached records until the record's TTL expires.

![Screenshot](https://github.com/s-anjeev/soc-projects/blob/main/01-Wireshark-Traffic-Analysis/case-03-c2-dns-tunneling/images/dns.jpg)

**aTOMIC Indicators of Compromise (IOCs)**

| IOC Type | Value |
|---------|--------|
|Source IP|192.168.56.104|
|Destination IP|192.168.56.102|
|Domain|testdomain.com|
|DNS Record Type|A|
|DNS Response Type|A and TXT|
|Protocolv | DNS (UDP/53)|
|Number of Queries	| 20|

**Behavioral IOCs Indicators of Compromise (IOCs):**
Repeated DNS queries to the same domain (testdomain.com) despite successful previous resolutions.
Periodic DNS beaconing with requests generated at consistent 12-second intervals.
Unsolicited TXT records included in responses to A record queries.
Non-standard TXT record content that does not resemble legitimate DNS TXT records

## Step 3 — TXT Rrecord Analysis
Analysis of the TXT records returned in the DNS responses revealed several suspicious characteristics. The TXT records did not follow the format or content typically associated with legitimate DNS TXT records, such as SPF, DKIM, DMARC, or domain verification entries. Additionally, the length of the TXT records varied across responses for the same domain, suggesting dynamically generated content rather than static DNS metadata. The values also resembled Base64-encoded strings. 

Upon decoding the TXT record contents using CyberChef, the decoded data was identified as Base64-encoded Windows command-line instructions. This strongly suggests that the TXT records were being used as a covert mechanism to deliver commands to the client, a technique commonly associated with DNS-based command-and-control (C2) communication.

![Screenshot](https://github.com/s-anjeev/soc-projects/blob/main/01-Wireshark-Traffic-Analysis/case-03-c2-dns-tunneling/images/cyberchef.jpg)

## Conclusion
This investigation revealed that seemingly legitimate DNS traffic was being used as a covert Command-and-Control (C2) channel. While the client issued standard A record queries, each successful response contained an unsolicited TXT record carrying Base64-encoded Windows commands. The combination of repeated DNS requests at fixed intervals, non-standard TXT records, and decoded command data confirmed that DNS was being abused as a covert communication channel between the compromised host and the C2 server, allowing commands to be delivered while blending into normal network traffic.


## Recommended Actions
- Block the malicious domain and associated DNS server.
- Isolate the affected host from the network.
- Perform endpoint forensic analysis to identify the initial compromise.
- Hunt for similar DNS beaconing and unsolicited TXT record activity across the environment.