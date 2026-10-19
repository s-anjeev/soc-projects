# Brute Force Attack Detection

## Overview
This lab detects successful logins following multiple failed attempts, helping identify successful password guesses during brute-force and password-spraying attacks, including slow, low-profile attacks. Suspicious successful logins trigger an email notification to the SOC analyst for investigation.   

## Detection Logic
Threat detection logic is primarily based on understanding how a threat behaves and interacts with the environment. Detection logic can consider factors such as the frequency and volume of requests, the type of requests, the number of events generated, the source and destination, authentication patterns, process behavior, and the sequence of activities.    

The following are the key characteristics:
- One IP address targeting multiple accounts — possible password spraying
- Multiple IP addresses targeting a single account — possible distributed brute force
- Multiple failed attempts followed by a successful login — possible successful password guess

## SPL Detection Query
**SPL Query:**   
```spl
index="winserver" source="WinEventLog:Security" (EventCode=4625 OR EventCode=4624)
| bin _time span=30m
| stats count(eval(EventCode=4624)) as successful_login,
        count(eval(EventCode=4625)) as failed_login
        by _time, ComputerName, Source_Network_Address
| where failed_login > 5 AND successful_login > 0
| table _time, Source_Network_Address, ComputerName, successful_login, failed_login
```

## Query Breakdown

| Line | What it does |
|---|---|
| `index="winserver" source="WinEventLog:Security" (EventCode=4625 OR EventCode=4624)` | Filter successful and failed Windows login events |
| `bin _time span=30m` | Group events into 30-minute time windows |
| `stats count(eval(EventCode=4624)) as successful_login, count(eval(EventCode=4625)) as failed_login by _time, ComputerName, Source_Network_Address` | Count successful and failed logins per time window, computer, and source IP |
| `where failed_login > 5 AND successful_login > 0` | Only return results with more than 5 failures and at least 1 successful login |
| `table _time, Source_Network_Address, ComputerName, successful_login, failed_login` | Display the results as a clean, readable table |

## Response Actions When Alert Fires

| Step | Response Action | Purpose |
|---|---|---|
| 1 | Identify the source IP address from the alert. | Determine the origin of suspicious authentication attempts. |
| 2 | Check the IP reputation using AbuseIPDB and other threat intelligence sources. | Assess whether the source is associated with known malicious activity. |
| 3 | Validate the activity and block the source IP at the perimeter firewall if malicious. | Prevent further unauthorized authentication attempts. |
| 4 | Investigate whether the targeted account experienced a successful compromise. | Determine whether the attacker gained access. |
| 5 | If compromise is confirmed, disable or temporarily contain the affected account and initiate incident response. | Contain the threat and prevent further unauthorized access. |
| 6 | Review relevant authentication logs and investigate any post-login activity. | Identify the scope and potential impact of the incident. |
| 7 | Document the findings, evidence, containment measures, and remediation actions. | Maintain an incident record for reporting and future detection improvements. |

**Note:** Do not block an IP solely because of a poor reputation score. Validate the evidence and follow your organization's incident response procedures, since the address may belong to a legitimate shared network or VPN.

## MITRE ATT&CK

| Tactic | Technique ID | Technique | Relevance |
|---|---|---|---|
| Credential Access | [T1110](https://attack.mitre.org/techniques/T1110/) | Brute Force | Repeated failed login attempts may indicate password-guessing activity. |
| Initial Access | [T1078](https://attack.mitre.org/techniques/T1078/) | Valid Accounts | A successful login following repeated failures may indicate the use of valid credentials. |
