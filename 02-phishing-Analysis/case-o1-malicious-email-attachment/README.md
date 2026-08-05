# Case 01 — Malicious Attachment (Malware Delivery)

### Summary
A phishing email impersonating amazonaws.com was reported by an employee. The email mimicked an AWS "Free Tier Usage Alert" notification and was sent using Emkei.cz, a publicly available email spoofing service. The attacker closely replicated the legitimate AWS email's branding, logo, formatting, font, and writing style to increase credibility. The email contained a deceptively named malicious attachment, AWS_Billing_Receipt.pdf.exe, intended to trick the recipient into executing it.


### Scenario
It is Monday morning at AlphaCore Ltd. An employee reports an email with the subject "AWS Free Tier limit alert", claiming that their AWS Free Tier usage has exceeded 85% of the monthly limit. The email appears highly convincing, closely replicating the legitimate AWS branding, logo, formatting, and writing style, and includes an attachment named AWS_Billing_Receipt.pdf.exe. As the SOC analyst on duty, your task is to determine whether the email is a legitimate AWS notification or a phishing attempt and assess the potential risk posed by the attachment.


### Objective
Analyse the suspicious email to determine whether it is a phishing attempt, identify all malicious indicators, trace the sending infrastructure, check the reputation of identified IOCs, and document findings professionally.

### Tools Used
1. AbuseIPDB
2. VirusTotal
3. Manual email header analysis

### Email Sample
- [The full email sample](https://github.com/s-anjeev/soc-projects/tree/main/02-phishing-Analysis/case-o1-malicious-email-attachment/email)


## Investigation Steps
### Step 1 — Initial Email Review

 - [Download pcap]()
 ![Screenshot]()