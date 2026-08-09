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
 1. From (Typosquatted)
    `security@micros0ft-support.com`
    The sender domain replaces the letter “o” in Microsoft with the number “0”, a common typosquatting technique used to create domains that visually resemble legitimate domains. The domain micros0ft-support.com is not an official Microsoft domain and has no legitimate association with Microsoft.

2. Malicious Link
    `http://micros0ft-account-verify.ru/login?user=sarah.jones@securecore.com`
    The URL uses the same typosquatting technique by replacing “o” with “0” in Microsoft. It also uses a .ru top-level domain rather than an official Microsoft domain.

    Additionally, the victim's email address is included as a URL parameter: user=sarah.jones@securecore.com 

    This indicates that the phishing page may have been customized for the specific recipient and suggests a targeted credential-harvesting attempt.

3. Urgency and Fear Tactics
    - "Your account has been temporarily suspended"
    - "Verify your identity within 24 hours"
    - "Your account will be permanently deleted"

    These statements create a false sense of urgency and pressure the recipient into acting without verifying the legitimacy of the email. This is a common social-engineering technique used in credential-phishing attacks.

4. Inconsistent Email Structure
    The overall structure, wording, formatting, and link presentation do not match the typical communication style and formatting used by Microsoft. 
    These inconsistencies, combined with the suspicious sender domain and malicious URL, further support the conclusion that the email is an impersonation-based phishing attempt.

  ![Screenshot](https://github.com/s-anjeev/soc-projects/blob/main/02-phishing-Analysis/case-o2-credential-harvesting/image/03-phishing-email-content.png)

 ## Step 2 — Email Header Analysis



 - [screenshot]()