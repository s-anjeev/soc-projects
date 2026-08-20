# Threat Intelligence
## Overview
Threat intelligence or cyber threat intelligence refers to the process of collecting, analyzing, and contextualizing information about cyber threats to understand threat actors, their infrastructure, capabilities, intentions, and TTPs, so an organization can prevent, detect, and respond to attacks.  

Threat intelligence enrichment is the process of enriching info/IOC using external and internal  databases. This helps confirm whether something is genuinely malicious, understand the wider context of an attack, and connect findings to known threat activity.  

### Why Threat Intelligence Matters
Threat Intelligence matters because it helps security teams understand threats, identify malicious activity, prioritize risks, and make better security decisions.  

Without threat intelligence, a SOC may see an IP address, domain, hash, or suspicious process but have little context about whether it is actually malicious.  

**With CTI, the SOC can enrich that data and determine:**
- What is it?  
    What does this IP, domain, hash, URL, or other indicator represent?
- Who is behind it?  
- What is their intent?  
- How likely is it to be malicious?  
- How dangerous is it? 
- Have we seen it before?
- Has anyone else seen it?

### Tools
1. VirusTotal — checks domains, IPs, URLs and file hashes against over 90 security vendors simultaneously and provides community-sourced intelligence about known threats.  

2. AbuseIPDB — a community database where security teams around the world report malicious IP addresses. Searching an IP here shows how many times it has been reported, what type of attacks it was involved in, and when it was last seen.  

3. URLhaus — is a community-driven threat intelligence platform operated by abuse.ch that collects and shares malicious URLs used for malware distribution.

# Cases
## Case 01 — Domain Analysis
The domain dl.myresult.co.za, identified during Wireshark traffic analysis, was submitted to VirusTotal for reputation analysis and further contextual enrichment.  
- [View case 1](https://github.com/s-anjeev/soc-projects/tree/main/04-Threat-Intelligence/case-01-Domain-analysis)

## Case 02 — IP Reputation Analysis
- [View case 2]()
## Case 03 — File Hash Investigation
- [View case 3]()
## Case 03 — URL Analysis
- [View case 4]()
