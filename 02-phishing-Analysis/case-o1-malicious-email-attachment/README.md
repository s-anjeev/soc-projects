# Case 01 — Malicious Attachment (Malware Delivery)

## Summary
A phishing email impersonating amazonaws.com was reported by an employee. The email mimicked an AWS "Free Tier Usage Alert" notification and was sent using Emkei.cz, a publicly available email spoofing service. The attacker closely replicated the legitimate AWS email's branding, logo, formatting, font, and writing style to increase credibility. The email contained a deceptively named malicious attachment, AWS_Billing_Receipt.pdf.exe, intended to trick the recipient into executing it.


## Scenario
It is Monday morning at AlphaCore Ltd. An employee reports an email with the subject "AWS Free Tier limit alert", claiming that their AWS Free Tier usage has exceeded 85% of the monthly limit. The email appears highly convincing, closely replicating the legitimate AWS branding, logo, formatting, and writing style, and includes an attachment named AWS_Billing_Receipt.pdf.exe. As the SOC analyst on duty, your task is to determine whether the email is a legitimate AWS notification or a phishing attempt and assess the potential risk posed by the attachment.


## Objective
Analyse the suspicious email to determine whether it is a phishing attempt, identify all malicious indicators, trace the sending infrastructure, check the reputation of identified IOCs, and document findings professionally.

## Tools Used
1. AbuseIPDB
2. VirusTotal
3. Manual email header analysis
4. phishtool

## Email Sample
- [The full email sample](https://github.com/s-anjeev/soc-projects/tree/main/02-phishing-Analysis/case-o1-malicious-email-attachment/email)


## Investigation Steps
## Step 1 — Email Content Review

The initial review analyzed the email's content to identify the sender's intent and phishing indicators, including the sender address, domain legitimacy, urgency, branding, and requested user action. 

The attacker precisely replicated a legitimate AWS Free Tier Usage Alert email, copying its logo, formatting, writing style, account details, service usage information, warnings, instructions, and links without any spelling or grammar errors. 

![Inbox image](https://github.com/s-anjeev/soc-projects/blob/main/02-phishing-Analysis/case-o1-malicious-email-attachment/images/email-view.jpg)

The email appeared almost identical to a genuine AWS notification, with the primary goal of convincing the recipient to open the malicious attachment AWS_Billing_Receipt.pdf.exe.

**Suspecious:**
The strongest phishing indicator is the attachment itself. Legitimate AWS Free Tier Usage Alert emails do not include attachments, including invoices or billing documents.


## Step 2 — Security Header Analysis (SPF, DKIM, and DMARC)

The email failed DMARC (dmarc=fail) and had no valid SPF (spf=none) or DKIM (dkim=none) authentication. These results confirm that the sending server was not authorized to send emails on behalf of amazonaws.com, and the integrity and authenticity of the message could not be verified. Together, these authentication failures provide strong evidence of domain impersonation and indicate that the email is a phishing attempt.

![Screenshot](https://github.com/s-anjeev/soc-projects/blob/main/02-phishing-Analysis/case-o1-malicious-email-attachment/images/security-headers.jpg)


## Step 3 — Email Header Analysis (sender, receiver, reply-to, )

The email claims to originate from "FreeTier" freetier@costalerts.amazonaws.com, but the Reply-To address is bobtheattacker@proton.me, which is completely unrelated to AWS. This mismatch indicates that any replies would be directed to an attacker-controlled email address rather than AWS, making it a strong indicator of email impersonation.

Additionally, the email was received from emkei.cz (114.29.236.247) instead of AWS mail infrastructure. emkei.cz is a publicly available email spoofing/testing service that allows users to send emails with forged sender addresses. In this case, it appears to have been used to impersonate the AWS domain (costalerts.amazonaws.com), further reinforcing that the email is fraudulent.

![Screenshot](https://github.com/s-anjeev/soc-projects/blob/main/02-phishing-Analysis/case-o1-malicious-email-attachment/images/email-headers.jpg)


## Step 4 — Email Attachment

The attachment headers identify the file as a Windows executable (application/x-msdownload) named AWS_Billing_receipt.pdf.exe. The attacker uses a double extension (.pdf.exe) to disguise the executable as a PDF, increasing the likelihood that the victim will open it. This is a high-confidence indicator of a malware delivery attempt.

![Screenshot](https://github.com/s-anjeev/soc-projects/blob/main/02-phishing-Analysis/case-o1-malicious-email-attachment/images/attachment.jpg)

**Indicator Of Compromise IOCs**
|IOCs |	Value|
|------------|-----------------------------------------------|
|Sender Email|	freetier@costalerts.amazonaws.com (spoofed)|
|Reply-To	|bobtheattacker@proton.me|
|Source IP	|114.29.236.247|
|Source Host|	emkei.cz|
|Attachment Name|	AWS_Billing_receipt.pdf.exe|
|MIME Type|	application/x-msdownload|
|File Extension|	.exe|


## Step 4 — Threat Intelligence Investigation

**Sending IP/Domain Investigation**
- **Sender IP Address:** Investigation using AbuseIPDB showed 0% confidence of abuse with no reported malicious activity.
- **Domain (emkei.cz):** Investigation in VirusTotal showed that 9 out of 91 security vendors classified the domain as suspicious.
![Screenshot](https://github.com/s-anjeev/soc-projects/blob/main/02-phishing-Analysis/case-o1-malicious-email-attachment/images/domain.jpg)
- **Attachment MD5 Hash:** The MD5 hash `d41d8cd98f00b204e9800998ecf8427e` had no detection history in VirusTotal. However, this alone should not be interpreted as evidence that the file is benign.
- **Manual File Analysis:** Despite the absence of threat intelligence detections, manual analysis confirmed that the attachment is a malicious executable. This demonstrates that newly created or customized malware can evade reputation-based detection services.


## Findings Summary
| Category | Findings |
|----------|----------|
| **Phishing Assessment** | Confirmed phishing email impersonating **Amazon Web Services (AWS)** to deliver malware. |
| **Social Engineering** | The attacker accurately replicated AWS branding, formatting, writing style, and account details to make the email appear legitimate. |
| **Primary Indicator** | The email contained a malicious attachment (`AWS_Billing_Receipt.pdf.exe`). Legitimate AWS Free Tier alerts do not include attachments. |
| **Email Authentication** | **SPF: none**, **DKIM: none**, **DMARC: fail** confirmed the sender was not authorized to send emails on behalf of `amazonaws.com`. |
| **Header Analysis** | The **Reply-To** address (`bobtheattacker@proton.me`) did not match the claimed AWS sender, confirming email impersonation. |
| **Sending Infrastructure** | The message originated from **emkei.cz**, a public email spoofing service, rather than legitimate AWS mail servers. |
| **Attachment Analysis** | The attachment was a Windows executable (`application/x-msdownload`) disguised as a PDF using a double extension (`.pdf.exe`). |
| **Threat Intelligence** | AbuseIPDB reported no abuse for the sender IP, while VirusTotal flagged `emkei.cz` by **9/91** security vendors. The attachment hash had no prior reputation or detection history. |
| **Manual Analysis** | Manual analysis confirmed the attachment was malicious despite the absence of reputation-based detections, indicating a potentially new or customized malware sample. |

## MITRE ATT&CK Mapping

| Tactic | Technique | ID | Justification |
|--------|-----------|----|---------------|
| Initial Access | Phishing: Spearphishing Attachment | T1566.001 | The attacker attempted to gain initial access by delivering a malicious executable disguised as a PDF attachment. |
| Initial Access | Phishing | T1566 | The attack relied on a phishing email impersonating AWS to deceive the recipient. |
| Defense Evasion | Masquerading | T1036 | The executable used a double extension (`.pdf.exe`) to appear as a legitimate PDF document. |
| Defense Evasion | Match Legitimate Name or Location | T1036.005 | The attachment name (`AWS_Billing_Receipt.pdf.exe`) was chosen to resemble a legitimate AWS billing document. |
| Resource Development | Acquire Infrastructure | T1583 | The attacker utilized external infrastructure (Emkei email spoofing service) to distribute the phishing email. |
| Resource Development | Compromise or Spoof Accounts/Identity | T1585 / T1585.001* | The email spoofed the AWS sender identity (`freetier@costalerts.amazonaws.com`) to establish trust with the victim. |

## Conclusion
The investigation confirmed a high-confidence phishing email impersonating AWS to deliver a malicious executable. Multiple indicators—including failed email authentication, spoofed infrastructure, a deceptive attachment, and manual analysis confirmed the email was malicious.

## Key Takeaways
- Do not rely solely on threat intelligence or reputation scores.
- Verify SPF, DKIM, and DMARC when investigating suspicious emails.
- Inspect email headers to identify sender and Reply-To mismatches.
- Treat executable attachments with double extensions (e.g., `.pdf.exe`) as highly suspicious.
- Combine threat intelligence with manual analysis for accurate detection.

## Recommended Actions
- Block the sender, source IP, Domain.
- Quarantine or delete the phishing email from all mailboxes.
- Submit the IOCs to security monitoring and threat intelligence platforms.
- Educate users to verify unexpected attachments and report suspicious emails.