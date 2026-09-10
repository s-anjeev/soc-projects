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

