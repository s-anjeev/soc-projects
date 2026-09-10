# Threat Detection

Threat detection is the process of identifying potentially malicious or suspicious activity in an IT environment by analyzing security data such as logs, network traffic, endpoint activity, authentication events, and alerts.   

This section demonstrates how to build detection rules that automatically identify suspicious and malicious activity in a SIEM environment. Rather than waiting for an analyst to manually spot an attack after it has already happened, detection rules run continuously in the background and fire alerts the moment suspicious behaviour crosses a defined threshold.   

## Why Detection Rules Matter
Investigation skills tell you what happened after an attack. Detection rules stop the attack while it is happening.  

The three cases in this section are directly linked to the Splunk investigations completed earlier in this portfolio. In those investigations the attacks had already succeeded before they were discovered. These detection rules would have caught each attack in real time and prevented the breach from happening at all.   

# Cases
## Case 01 — Brute Force Attack Detection
Detects repeated failed login attempts from a single source IP against the same account within a 60 second window. It can detect brute force before attacker succeeds giving the analyst time to take necessary action. 
- [View case 1](https://github.com/Cybervault-1/My-cybersecurity-portfolio/tree/main/03-threat-detection-scenarios/case-01-brute-force-detection)

## Case 02 — Successful login after multiple failed attempts
Detect a successful authentication following multiple failed login attempts, which may indicate a successful password guess during a brute-force or password-spraying attack.
- [View case 2](https://github.com/Cybervault-1/My-cybersecurity-portfolio/tree/main/03-threat-detection-scenarios/case-01-brute-force-detection)

