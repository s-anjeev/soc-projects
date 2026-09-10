# Brute Force Attack Detection

## Overview
This detection rule identifies brute force attacks in real time by monitoring for repeated failed login attempts from a single source IP against the same user account within a 60 second window. When the threshold is crossed an alert fires immediately giving the analyst time to respond before the attacker succeeds.   


## Detection Logic
Threat detection logic is primarily based on understanding how a threat behaves and interacts with the environment. Detection logic can consider factors such as the frequency and volume of requests, the type of requests, the number of events generated, the source and destination, authentication patterns, process behavior, and the sequence of activities.  

The following are the key characteristics of a brute-force attack that we look for when developing detection logic:  
- Same source IP making repeated attempts
- Same user account being targeted
- Attempts arriving every few seconds
- Volume far exceeding normal user behaviour

The rule triggers when a single source IP generates more than 5 failed login attempts against the same account within a 60 second window.

## SPL Detection Query
This query triggers an alert when a single IP address generates more than five failed login attempt within one minute.   
**SPL Query:**   
```spl
index="winserver" source="WinEventLog:Security" EventCode=4625
| bin _time span=1m
| stats count as "Failed_Attempts" by _time, Source_Network_Address, ComputerName
| where Failed_Attempts > 5
| table _time, Source_Network_Address, ComputerName, Failed_Attempts
```

## Query Breakdown
| **Line** | **What it does** |
|---|---|
| `index="winserver" source="WinEventLog:Security" EventCode=4625` | Filter only failed Windows login events |
| `bin _time span=1m` | Group events into 1 minute time windows |
| `stats count as "Failed_Attempts" by _time, Source_Network_Address, ComputerName` | Count failures per system per IP |
| `where Failed_Attempts > 5` | Only return results exceeding the threshold |
| `table _time, Source_Network_Address, ComputerName, Failed_Attempts` | Display the results as a clean, readable table |

## Test Results
The query was run against the brute force investigation log file. The screenshot below shows the detection firing on 1  minute windows confirming the rule works correctly.

![img](https://github.com/s-anjeev/soc-projects/blob/main/06-Threat-Detection/case-01-Brute-Force-Attack-Detection/images/1.png)   

## Alert Action
When the alert is triggered, an email notification is automatically sent to the SOC analyst for investigation.
![img](https://github.com/s-anjeev/soc-projects/blob/main/06-Threat-Detection/case-01-Brute-Force-Attack-Detection/images/2.png)  


## Response Actions When Alert Fires
- Identify the attacking source IP from the alert
- Check the IP on AbuseIPDB for reputation
- Block the IP at the perimeter firewall immediately
- Monitor whether the targeted account was successfully breached
- If breached disable the account and begin incident response
- Document findings and actions taken

## MITRE ATT&CK
|Technique|	ID|
|---|---|
|Brute Force — Password Guessing|	T1110.001|