# Network Traffic Analysis Using Wireshark
This section contains multiple network traffic investigations performed using Wireshark.

## Cases
### Case 01 — HTTP Malware Traffic Analysis
A compromised workstation was found downloading multiple malicious RAR archives including VNC remote access tools from external servers using a fake browser identity to evade detection.

- [View Case 01](https://github.com/s-anjeev/soc-projects/tree/main/01-Wireshark-Traffic-Analysis/case-01-http-malware)
    - [Download pcap](https://github.com/s-anjeev/soc-projects/blob/main/01-Wireshark-Traffic-Analysis/case-01-http-malware/log/2020-12-31-traffic-analysis-quiz-01.pcap)

### Case 02 — DNS Tunneling Data Exfiltration Investigation.
The analysis involved examining DNS queries, communication patterns, encoded subdomains, and threat intelligence to confirm the exfiltration of a sensitive project file.

- [View Case 02](https://github.com/s-anjeev/soc-projects/tree/main/01-Wireshark-Traffic-Analysis/case-02-dns-exfiltration)
    - [Download pcap](https://github.com/s-anjeev/soc-projects/blob/main/01-Wireshark-Traffic-Analysis/case-02-dns-exfiltration/logs/dns-tunneling-exfiltration.pcapng)


### Case 03 — DNS Tunneling command and control.