# Case 03 — Payment Fraud

## Summary
An unexpected bank account change request was received from a major client. The email appeared to originate from a legitimate business employee and passed initial email authentication checks. However, investigation of the sender's communication history revealed no previous financial discussions or banking-change requests. Further verification with the client's finance team and senior leadership confirmed that the legitimate business email account had been compromised and the request was initiated by an attacker.


## Scenario
It is Monday morning at oliva info. tec. An employee receives an email from a business employee of one of the company's major clients requesting a change to the client's bank account details for future payments. The sender is a legitimate client employee, and the email appears to come from the client's legitimate mail infrastructure. However, the sender's previous communications with XYZ.com are primarily related to normal business activities, with no history of financial requests or banking-information changes. As the SOC analyst on duty, your task is to determine whether the request is legitimate, identify signs of account compromise or Business Email Compromise (BEC), and assess the risk of potential payment fraud.

## Objective
Analyse the suspicious email and communication history to determine whether the banking-change request is legitimate, identify indicators of potential account compromise and BEC, investigate the sender's activity and email authentication, validate the request through independent channels, and document the findings and recommended actions professionally.

## Tools Used
1. Manual email analysis
2. phishtool


## Investigation Steps
## Step 1 — Why this request is suspecious  
The request appears suspicious because the sender has no history of initiating financial decisions or requesting changes to banking information. A review of the sender's previous communications with XYZ.com showed that all prior conversations were related to regular business activities, with no previous financial discussions or banking-change requests.  

There was also no scheduled event or prior communication indicating that a banking change was expected. Requests of this nature are normally initiated by the client's Finance team and communicated in advance through the established process. The request therefore appeared out of context and warranted further investigation and independent verification.

## Step 2 — Email Content Review
The initial review analyzed the email's content to identify the sender's intent and phishing indicators, including the sender address, domain legitimacy, urgency, branding, and requested user action etc..

Initial review of the email content revealed no obvious indicators of phishing, such as urgency or threatening language, suspicious links, or mismatched branding. The email contained a direct request from a known client representative to change the bank account details for future financial transactions, making it a high-stakes request.  
There were also no apparent signs of typosquatting, as the email originated from the client's legitimate domain and a known business representative.

## Step 3 — Email Header Analysis
The email headers did not show any obvious red flags. SPF, DKIM, and DMARC all passed, and the email was sent through the client's legitimate mail infrastructure.  

## Step 4 — Business Process Verification
- The predefined process for changing banking information was not followed.
- The sender's role did not authorize them to initiate or approve banking-information changes.
- No prior approval or authorized change request was found.
- The requested banking details did not match the vendor's existing records.

## Step 5 — Client Confirmation & Request Validation

During communication with the client, they denied making the request and asked us not to process the banking change while they investigate the incident internally.  

## IOC Summary
| IOC Type | Value | Verdict |
|---|---|---|
| Sender Email | `michael.anderson@globaltech.com` | Suspicious — legitimate account potentially compromised |
| Sender Domain | `globaltech.com` | Legitimate — no typosquatting identified |
| Sending IP | `203.0.113.25` | Legitimate — authorized client mail infrastructure |
| Reply-To | `michael.anderson@globaltech.com` | Legitimate — matches sender |
| Requested Bank Account | New/unrecognized account | Suspicious — does not match existing vendor records |
| Email Subject | `Update to Banking Details for Future Payments` | Suspicious — unexpected financial request |
| SPF / DKIM / DMARC | `PASS / PASS / PASS` | No authentication anomaly |
| Primary Indicator | Unauthorized bank-account change request | **High Risk — potential BEC/payment fraud** |


# Conclusion
These behaviors strongly indicate a Business Email Compromise (BEC) / payment-redirection attempt. The unexpected banking request, deviation from the sender's normal communication pattern, failure to follow the established process, and confirmation from the client that the request was unauthorized confirm that the legitimate account was likely compromised and misused by an attacker.

# Key Takeaways
- History of the conversation are important when investigating BEC and payment-fraud attempts.
- Unexpected financial requests should be treated as high-risk, even when the sender and domain appear legitimate.
- Critical changes, such as bank-account or payment-detail changes, should follow predefined verification and approval procedures.
- Comparing the request with the sender's previous communication patterns and role can reveal behavioral anomalies that technical email checks may miss.
- A legitimate sender does not necessarily mean a legitimate request; account compromise can allow attackers to operate from trusted accounts.

# Actions Taken
- Banking information changes were not processed.
- The request was independently verified with the client's Finance team and senior leadership.
- The client was informed of the suspicious activity and requested to investigate the compromised account internally.
- The email and associated indicators were preserved for further investigation.