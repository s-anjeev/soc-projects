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
The initial review analyzed the email's content to identify the sender's intent and phishing indicators, including the sender address, domain legitimacy, urgency, branding, and requested user action.

Initial email content review indicates that the sender is attempting to impersonate Microsoft. The email uses urgency and account-suspension threats, claiming that the recipient’s account will be suspended within 24 hours due to unusual activity. The recipient is instructed to restore access by verifying their account through a provided link, with a warning that failure to do so will result in permanent account termination.

**Immediate red flags were identified:**
 1. **From (Typosquatted)**  
    `security@micros0ft-support.com`  
    The sender domain replaces the letter “o” in Microsoft with the number “0”, a common typosquatting technique used to create domains that visually resemble legitimate domains. The domain micros0ft-support.com is not an official Microsoft domain and has no legitimate association with Microsoft.

2. **Malicious Link**  
    `http://micros0ft-account-verify.ru/login?user=sarah.jones@securecore.com`  
    The URL uses the same typosquatting technique by replacing “o” with “0” in Microsoft. It also uses a .ru top-level domain rather than an official Microsoft domain.  

    Additionally, the victim's email address is included as a URL parameter: `user=sarah.jones@securecore.com `  

    This indicates that the phishing page may have been customized for the specific recipient and suggests a targeted credential-harvesting attempt.  

3. **Urgency and Fear Tactics**  
    - "Your account has been temporarily suspended"
    - "Verify your identity within 24 hours"
    - "Your account will be permanently deleted"

    These statements create a false sense of urgency and pressure the recipient into acting without verifying the legitimacy of the email. This is a common social-engineering technique used in credential-phishing attacks.

4. **Inconsistent Email Structure**  
    The overall structure, wording, formatting, and link presentation do not match the typical communication style and formatting used by Microsoft.  
    These inconsistencies, combined with the suspicious sender domain and malicious URL, further support the conclusion that the email is an impersonation-based phishing attempt.  

  ![Screenshot](https://github.com/s-anjeev/soc-projects/blob/main/02-phishing-Analysis/case-o2-credential-harvesting/image/03-phishing-email-content.png)  

 ## Step 2 — Email Header Analysis
Beyond the visible content every email contains hidden technical headers that reveal where it actually came from and how it was sent. These headers were analysed to trace the sending infrastructure.  

What to look for: The originating IP address, the mail server used, the X-Mailer field and any authentication results showing SPF, DKIM and DMARC status.  

**Findings**  
|Header	| Value	 | Significance|
|--------|---------|---------------|
|X-Originating-IP|	185.220.101.45|	The actual IP the email was sent from|
|Received|	mail.micros0ft-support.com|	Fake mail server matching the typosquatted domain|
|X-Mailer|	PHPMailer 6.0|	Bulk email tool commonly used in phishing campaigns|
|Reply-To|	noreply@micros0ft-support.com|	Different from From address — classic phishing indicator|
  

The X-Mailer value of PHPMailer 6.0 is particularly significant. Real Microsoft emails are sent through Microsoft's own enterprise email infrastructure. PHPMailer is a PHP library used to send emails from web servers — commonly used by attackers to automate bulk phishing campaigns cheaply and quickly.


## Step 3 — Sending IP Investigation  
The originating IP address 185.220.101.45 identified in the email headers was submitted to AbuseIPDB for reputation analysis.  

What to look for: Confidence score, total reports, distinct reporting sources, IP type, ISP classification and attack categories in the report history.  
 ![screenshot](https://github.com/s-anjeev/soc-projects/blob/main/02-phishing-Analysis/case-o2-credential-harvesting/image/ip-test.png)

A 100% confidence score with over 6,500 reports from 595 different organisations is about as confirmed malicious as an IP can get. Attacker deliberately routed the email through the Tor anonymity network to hide their real location and identity.

## Step 4 — Phishing Link Analysis
The malicious URL contained in the email was submitted to VirusTotal for reputation analysis. The URL was never clicked — it was checked safely through VirusTotal's URL scanning service.  

What to look for: Vendor detection count, URL category, domain reputation and any related malicious files or URLs associated with the domain.  

**Finding:** VirusTotal returned 0 detections from 96 vendors for the phishing URL. However this result does not clear the URL.  

- The domain was brand new and had no prior reputation in vendor databases
- The domain uses typosquatting — zero instead of O in Microsoft
- The .ru TLD has no legitimate association with Microsoft
- The /login path strongly suggests a credential harvesting page
- The URL contains the victim's email as a parameter confirming targeting

This is a classic example of why threat intelligence tools must be used together rather than relying on any single result. 


**IOC Summary**
| IOC Type | Value | Verdict |
|---|---|---|
| Sender domain | `micros0ft-support.com` | **Malicious — typosquatting** |
| Sending IP | `185.220.101.45` | **Malicious — 100% confidence Tor exit node** |
| Phishing URL | [http://micros0ft-account-verify.ru/login](http://micros0ft-account-verify.ru/login) | **Malicious — credential harvesting page** |
| Reply-To | [noreply@micros0ft-support.com](mailto:noreply@micros0ft-support.com) | **Suspicious — matches fake domain** |
| X-Mailer | `PHPMailer 6.0` | **Suspicious — bulk phishing tool** |


# MITRE ATT&CK Mapping
| **Technique** | **Technique ID** | **What was observed** |
|---|---|---|
| Phishing | T1566.002 | A spearphishing link was sent to a specific employee targeting their Microsoft credentials |
| Acquire Infrastructure | T1583 | The attacker registered a typosquatted domain to host the credential harvesting page |
| Hide Infrastructure | T1665 | The email was routed through a Tor exit node to anonymise the attacker's real location |
| Credentials from Web Browsers | T1555.003 | The phishing page was designed to capture Microsoft account credentials |

# Conclusion

The investigation confirmed a targeted credential phishing attack against a SecureCode Ltd employee. The attacker impersonated Microsoft using a typosquatted domain, urgency tactics, and a Russian-hosted credential harvesting page.  

The email originated from a known malicious Tor exit node, indicating deliberate anonymisation. The clean VirusTotal result for the URL demonstrates that reputation-based tools alone are insufficient for detecting newly created phishing infrastructure.  

The combination of typosquatting, suspicious infrastructure, Tor routing, and credential harvesting behavior provides strong evidence of a phishing attack. Any credentials entered through the phishing page should be treated as compromised. 

# Key Takeaways
- Always check the sender domain carefully — one character difference can mean the difference between legitimate and phishing
- SPF, DKIM and DMARC failures on an email claiming to be from a major company are immediate red flags
- A sending IP routed through Tor indicates a sophisticated attacker deliberately hiding their identity
- A clean VirusTotal URL result does not clear a link — newly registered phishing domains specifically avoid reputation databases
- Urgency and fear are the most powerful phishing tools — teach employees to slow down when an email pressures them to act immediately
- Always check multiple data points together — no single tool tells the complete story

# Recommended Actions
- Block the sending domain micros0ft-support.com at the email gateway
- Block the phishing domain micros0ft-account-verify.ru at the firewall
- Block the sending IP 185.220.101.45 at the perimeter
- Check mail server logs to confirm no other employees received the same email
- Ask Sarah whether she clicked the link or entered any credentials
- If credentials were entered treat them as compromised and reset immediately
- Submit the phishing domain to Microsoft for takedown
- Send a security awareness alert to all employees about this phishing campaign