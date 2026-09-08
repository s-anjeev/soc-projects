# User Behavior Baseline and Malicious Activity Detection

## Summary
An HR department user's account was observed logging in from an unknown IP address and an unusual geolocation that differed from the user's normal login patterns. The login also occurred at an unusual time, further increasing the suspicion of account compromise. During the investigation, additional activity was correlated with the login event and confirmed that the legitimate user had not performed the activity.    
The investigation determined that an attacker had obtained and used the user's credentials to gain unauthorized access to the account.  

## Scenario
Monday, 03:00 AM at SecureCode Infotec. The SOC receives an alert that user Shreya logged in from an unknown IP address and unusual geolocation. As the analyst on duty, investigate the activity and determine whether it is a True Positive or False Positive.   

## Objective 
Investigate the suspicious login, analyze the user's baseline and authentication activity, and determine whether the account was compromised by an attacker.    

## Alert Received
**Name:** Unusual User Behaviour Detected   
**Description:** A user logged in from an unknown IP address, with unusual timing.   
**Username:** Shreya   
**Workstation:** WORKSTATION-SHREYA   
**Time:** 09/07/2026 03:00:40 AM   
**IP Address:**  216.194.166.140   
**Department:** HR   
**Authentication:** Successful   
**Severity:** Medium   
**Source Location:** Los Angeles, USA"    
**Destination:** Internal Server  
     
## Investigation Steps
As usual, I’ll start the investigation with the information provided by the alert: the username, timestamp, workstation name, and the fact that its a successful login attempt.    

Since we know this is a Successful login attempt, Windows Event ID 4624 (Successful login) is a useful starting point.   

We can use the username, workstation, and timestamp from the alert to narrow down the search in Splunk. This should reduce the amount of unrelated activity and help us quickly identify the process that generated the alert.    

**SPL query:**  `source="Shreya_RDP_Windows_Logs.csv" host="WORKSTATION-SHREYA" index="main" sourcetype="csv" Source_IP="216.194.166.140" Username=Shreya Timestamp="2026-09-07 03:00:40" Event_ID=4624`   


This query revealed the event that generated the alert. The event log confirms that the user Shreya successfully logged in to the system from the IP address 216.194.166.140, geolocated to Los Angeles, USA, at 2026-09-07 03:00:40 AM.   


## IP Threat Intel. 
The next step is IP threat intelligence because the IP is the most critical piece of information here. This IP is unknown to our system, and a user logging in from an uncommon IP needs to be investigated properly.   

**Ip reputation check using abuseipdb**   
The AbuseIPDB analysis revealed that this IP was reported 585 times, with an Abuse Confidence Score of 47%. The reported location is Los Angeles, California, USA. The IP has been widely detected performing unauthorized port scanning and attempting to break into systems.   

This IP address has been reported a total of 585 times from 33 distinct sources. 216.194.166.140 was first reported on January 6, 2023, and the most recent report was 6 hours ago.   

The output from AbuseIPDB is enough to categorize this activity as highly suspicious, but we cannot rely on the IP address alone because IP ownership can change over time. Therefore, we need to continue the investigation by correlating this activity with other authentication and endpoint logs.   


## Checking Authentication Logs Before and After the Login
The next step is to check the authentication logs just before and after the suspicious login. The main goal is to identify any failed or successful authentication attempts associated with the user or the source IP around the login time.  

This will help us determine whether the successful login was an isolated event or whether it was preceded by multiple failed login attempts or followed by other suspicious authentication activity.    

**SPL query:**  `index="main" source="Shreya_RDP_Windows_Logs.csv" sourcetype=csv_auth earliest="09/07/2026:02:55:40" latest="09/07/2026:03:05:40" | table time,Username,Hostname,Source_IP,Source_Location,Event_ID,Logon_Type,Result`    



This investigation revealed that before the successful login attempt, there were four unsuccessful login attempts, with exactly 10 seconds between each of the five authentication requests. Performing authentication attempts at such precise and consistent intervals would be difficult for a human to do manually and strongly suggests an automated password-guessing attempt. On the fifth attempt, the authentication was successful, indicating that the attacker may have successfully gained access to the user's account.     


## User Behavior Analysis
he next step is to analyze the user's normal login behavior and compare it with the suspicious activity. We need to check whether Shreya normally logs in around 3:00 AM using the same workstation and source IP.

**SPL Query:** `index="main" source="Shreya_RDP_Windows_Logs.csv" sourcetype=csv_auth earliest="08/27/2026:12:00:00" latest="09/07/2026:03:05:40" | table time,Username,Hostname,Source_IP,Source_Location,Event_ID,Logon_Type,Result`   

The previous authentication logs show that Shreya normally logs in from 185.220.101.45, with the source location listed as Chandigarh, India, usually around 9:00 AM. However, the suspicious login occurred at 03:00:40 AM from 216.194.166.140, with the source location listed as Los Angeles, USA.     

This does not match the user's regular login behavior and increases the suspicion around this activity.   


## User Verification
One day before the suspicious login, Shreya logged in from her usual location at her usual time using the same IP address.   
Further contact with Shreya revealed that she was not traveling and was in Chandigarh, following her normal routine and activity. She also confirmed that the suspicious login at 03:00 AM from Los Angeles, USA, does not belong to her.   

This confirms that the login was not performed by the legitimate user and significantly increases the likelihood that her account has been compromised.


## Indicators Of Compromise
| IOC Type | Value | Details |
|---|---|---|
| Source IP | `216.194.166.140` | Suspicious external IP; geolocated to Los Angeles, USA |
| User Account | `Shreya` | User whose account was used for the suspicious login |
| Hostname | `WORKSTATION-SHREYA` | Workstation associated with the account |
| Successful Login | `2026-09-07 03:00:40` | Successful authentication from the suspicious IP |
| Event ID | `4624` | Successful Windows authentication |
| Logon Type | `10` | RemoteInteractive / RDP |
| Failed Attempts | `4 × Event ID 4625` | Four failed authentication attempts before the successful login |
| Attack Pattern | `4625 → 4625 → 4625 → 4625 → 4624` | Multiple failed attempts followed by successful authentication |
| IP Reputation | `585 reports / 33 sources` | Reported on AbuseIPDB with 47% Abuse Confidence Score |

**Suspicious Behavior** -> `4 failed → 1 successful RDP login` Four failed authentication attempts at exactly 10-second intervals followed by a successful RDP login.  
The user confirmed that the login was not performed by her, and the source IP has a suspicious reputation, with 585 reports from 33 distinct sources on AbuseIPDB.  

## Attack Timeline

| Time | Event | Observation |
|---|---|---|
| `2026-09-07 03:00:00` | Failed Authentication | Event ID `4625` from `216.194.166.140` |
| `2026-09-07 03:00:10` | Failed Authentication | Event ID `4625` — 10 seconds after the previous attempt |
| `2026-09-07 03:00:20` | Failed Authentication | Event ID `4625` — 10 seconds after the previous attempt |
| `2026-09-07 03:00:30` | Failed Authentication | Event ID `4625` — 10 seconds after the previous attempt |
| `2026-09-07 03:00:40` | Successful RDP Login | Event ID `4624`, Logon Type `10` — successful authentication |   


## MITRE Maping
| MITRE ATT&CK ID | Technique | Tactic | Evidence |
|---|---|---|---|
| T1110 | Brute Force | Credential Access | Four failed authentication attempts followed by a successful login from the same suspicious IP. |
| T1110.001 | Password Guessing | Credential Access | Multiple failed authentication attempts against Shreya's account followed by a successful authentication. |
| T1021.001 | Remote Services: RDP | Lateral Movement | Successful authentication used Logon Type 10, indicating a Remote Interactive/RDP login. |
| T1078 | Valid Accounts | Defense Evasion / Persistence / Initial Access | The attacker successfully authenticated using the legitimate user's account, which the user confirmed was not her activity. |

## Summary
The investigation confirmed that the alert was a true positive. Shreya's account was successfully accessed on 2026-09-07 at 03:00:40 AM from the unfamiliar IP address 216.194.166.140, geolocated to Los Angeles, USA.  

Before the successful login, there were four failed authentication attempts at exactly 10-second intervals, followed by a successful RDP login. The IP also has a suspicious reputation, with 585 reports from 33 distinct sources on AbuseIPDB.  

User behavior analysis showed that Shreya normally logs in around 9:00 AM from Chandigarh using her usual IP address. Shreya confirmed that she was in Chandigarh, was not traveling, and did not perform the suspicious login.  

Based on the authentication pattern, unusual source IP and location, suspicious IP reputation, and confirmation from the user, the activity is consistent with a potential account compromise through automated password guessing.  


## Recommended Actions
- Reset Shreya's password immediately and invalidate all active sessions.
- Temporarily disable the account if unauthorized access is confirmed.
- Block `216.194.166.140` at the firewall/VPN/RDP level.
- Review and terminate any active RDP sessions associated with Shreya's account.
- Investigate authentication activity for the same IP against other user accounts.
- Enable MFA for Shreya's account and other accounts exposed to external RDP access.
- Continue monitoring the account for further suspicious authentication attempts.