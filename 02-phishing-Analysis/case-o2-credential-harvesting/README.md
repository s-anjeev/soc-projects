# Case 02 — Suspicious Email Investigation (Credential Harvesting)

## Summary

A phishing email impersonating Microsoft was reported by an employee at SecureCode Ltd. The email leveraged typosquatting, urgency-themed social engineering, and a credential harvesting link to deceive the recipient into disclosing Microsoft account credentials. Investigation revealed that the email originated from a known malicious Tor exit node with a 100% abuse confidence score and over 6,500 abuse reports submitted by 595 organizations worldwide. The embedded phishing URL resolved to a Russian domain hosting a fraudulent Microsoft login page designed to steal user credentials. The evidence confirms this was a deliberate and sophisticated credential harvesting campaign targeting Microsoft accounts.


## Scenario

It is Tuesday morning at SecureCore Ltd. An employee named Sarah forwards a suspicious email to the security team with the subject line "Your Microsoft Account Has Been Suspended." She is concerned because the email looks convincing and is threatening to permanently delete her account if she does not act within 24 hours. As the SOC analyst on duty the task is to investigate whether this is a legitimate Microsoft email or a phishing attempt.


## Objective
Analyse the suspicious email to determine whether it is a phishing attempt, identify all malicious indicators, trace the sending infrastructure, check the reputation of identified IOCs, and document findings professionally.


## Tools Used
1. AbuseIPDB
2. VirusTotal
3. Manual email header analysis


## Investigation Steps
## Step 1 — Email Content Review

 - [Download pcap]()
 ![Screenshot]()