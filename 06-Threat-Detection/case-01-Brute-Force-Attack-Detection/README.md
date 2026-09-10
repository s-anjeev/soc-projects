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
https://prd-p-7szl1.splunkcloud.com/

install app %HOMEPATH%\Downloads\splunkclouduf.spl