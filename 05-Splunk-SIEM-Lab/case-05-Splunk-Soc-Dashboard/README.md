# Splunk SOC Dashboard

## Summery 
This project focuses on building a practical Splunk SOC Dashboard that an L1/L2 SOC analyst can use for real-time security monitoring, threat detection, and incident investigation.   
The dashboard will provide security monitoring, alert visibility, and investigation capabilities by visualizing authentication activity, failed logins, suspicious processes, PowerShell activity, network events, and other security-relevant telemetry.

## Splunk Dashboard Overview
A Splunk dashboard is a visual interface in Splunk that displays important information from your logs and events in an easy-to-understand way.   
It displays important security data from logs and events using panels such as charts, tables, and statistics. It helps SOC analysts monitor, detect, and investigate security activity from a single view.  

**Why Splunk Dashboard Important**   
- Provides a single view of important security events, alerts, and activities across the environment.
- Helps SOC analysts quickly identify suspicious patterns such as brute-force attacks, unusual logins, or malicious PowerShell activity.
- Converts large volumes of raw logs into charts, tables, and statistics that are easier to understand.
- Allows analysts to drill down from dashboard panels into the underlying events for detailed investigation.
- Reduces the time spent manually searching through logs and helps analysts prioritize important security events.   


## Dashboard Panels
This SOC dashboard is designed to monitor and detect authentication-related security issues, including password-spraying attacks, brute-force attacks, and credential-stuffing attacks. It provides visibility into failed and successful login attempts, source IP addresses, targeted usernames, and affected computers, helping SOC analysts quickly identify suspicious authentication activity and initiate further investigation.   

### Panel 1
The first panel of this SOC dashboard uses a bar chart to display the total number of failed and successful login attempts within a specific time period.   
This panel helps SOC analysts quickly compare the ratio of successful and failed login attempts. A significantly higher number of failed attempts compared to successful attempts may indicate a brute-force or password-spraying attack.    

**SPL Query:** `index="winserver" source="WinEventLog:Security" host="WKSTN-041" (EventCode=4625 OR EventCode=4624) | eval Logon_Status=if(EventCode=4624,"Successful Logon","Failed Logon") | stats count by Logon_Status`   

![img](https://github.com/s-anjeev)   

### Panel 2
The second panel uses a table to display the number of failed login attempts associated with each source IP address, with the IP addresses generating the highest number of failed attempts listed at the top.   
This helps SOC analysts quickly identify potentially suspicious IP addresses and investigate authentication activity that may be related to brute-force or password-spraying attacks.   

**SPL Query:** `index="winserver" source="WinEventLog:Security" host="WKSTN-041" EventCode=4625 | stats count as Failed_Logons by Source_Network_Address | sort - Failed_Logons`   

![img](https://github.com/s-anjeev)

### Panel 3
The third widget is also a table that represents the number of failed login attempts per computer, grouped by username. This helps SOC analysts identify which user accounts are being targeted on specific computers. A high number of failed attempts against multiple usernames on the same computer may indicate password-spraying or brute-force activity and can help analysts prioritize further investigation.   

**SPL Query:** `index="winserver" source="WinEventLog:Security" EventCode=4625 | stats count as Failed_Logons by ComputerName, Account_Name | sort - Failed_Logons`   

![img](https://github.com/s-anjeev)   


**Complete SOC Dashboard**  
![img](https://github.com/s-anjeev)  