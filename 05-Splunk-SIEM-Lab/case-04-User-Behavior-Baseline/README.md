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
**Description:** A user logged in from an unknown IP address.
**Username:** Shreya
**Workstation:** WORKSTATION-SHREYA
**Time:** 09/07/2026 03:00:40 AM
**IP Address:**  127.0.0.1
**Department:** HR
**Authentication:** Successful
**Severity:** Medium
**Source Location:** Delhi, India
**Destination:** Internal Server

## Investigation Steps
As usual, I’ll start the investigation with the information provided by the alert: the username, timestamp, workstation name, and the fact that its a successful login attempt.    

Since we know this is a Successful login attempt, Windows Event ID 4624 (Successful login) is a useful starting point.   

We can use the username, workstation, and timestamp from the alert to narrow down the search in Splunk. This should reduce the amount of unrelated activity and help us quickly identify the process that generated the alert.  

**SPL query:**  

index="main" host="WORKSTATION-SHREYA"
