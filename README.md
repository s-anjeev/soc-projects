# SOC Analyst Incident Report
## Real-World SSH Brute Force Attack — Detection, Investigation & Containment

---

**Analyst:** Sanjeev  
**Date of Incident:** April 4, 2026  
**Environment:** AWS EC2 Instance — Ubuntu Linux (ip-172-31-18-243)  
**Incident Type:** SSH Brute Force / Credential Stuffing Attack  
**Severity:** Medium  
**Status:** Contained  

---

## Executive Summary

Detected, investigated, and contained a real-world SSH brute force attack targeting an AWS EC2 Ubuntu instance. Leveraged Linux authentication logs (`/var/log/auth.log`) to identify over 200 failed login attempts originating from two malicious IP addresses. Confirmed zero successful unauthorised access. Implemented firewall rules to block attacker IPs, rotated credentials, and enforced SSH key-only authentication — eliminating the brute force attack surface entirely.

---

## 1. Environment Overview

- **Platform:** AWS EC2 (Amazon Web Services Elastic Compute Cloud)
- **Operating System:** Ubuntu Linux
- **Instance IP:** 172.31.18.243 (Private — RFC 1918)
- **Primary Use:** Secondary lab environment for security concepts, malware analysis, and log analysis
- **Exposure:** SSH port 22 open to the internet (default configuration)

---

## 2. Detection

### How the Incident Was Discovered

While conducting routine log analysis exercises on `/var/log/auth.log`, executed a `grep` query to filter SSH authentication failure events. The results revealed a significantly higher volume of failed login attempts than expected for a personal lab instance.

**Detection Query Used:**
```bash
grep "Failed password" /var/log/auth.log
```

**What Triggered the Alert:**
- Hundreds of failed SSH authentication attempts recorded
- Each attempt used a **different username** — indicating automated username enumeration
- Requests spaced **5–6 seconds apart** — consistent with automated tooling, not manual attempts
- Only **two unique source IP addresses** responsible for all attempts — indicating coordinated botnet activity

---

## 3. Investigation

### Phase 1 — Quantified the Attack Scope

Enumerated total failed authentication attempts to establish attack volume:

```bash
grep "Failed password" /var/log/auth.log | wc -l
```

**Finding:** Over 200 failed authentication attempts recorded.

---

### Phase 2 — Identified Attack Pattern

Extracted and analysed the usernames targeted to determine attacker methodology:

```bash
grep "Failed password" /var/log/auth.log | awk '{print $9}' | sort | uniq -c | sort -rn
```

**Finding:** Each attempt used a **different username** — consistent with a **dictionary-based credential stuffing attack** cycling through common username lists (root, admin, test, ubuntu, user, etc.).

---

### Phase 3 — Isolated Source IP Addresses

Extracted attacker IP addresses to identify threat actors:

```bash
grep "Failed password" /var/log/auth.log | awk '{print $11}' | sort | uniq -c | sort -rn
```

**Finding:** All 200+ attempts originated from **two IP addresses only**, confirming coordinated botnet activity.

| IP Address | Failed Attempts | VirusTotal Verdict |
|---|---|---|
| IP Address 1 | ~180 | 8/92 vendors flagged malicious |
| IP Address 2 | ~30 | 2/22 vendors flagged malicious |

---

### Phase 4 — Confirmed No Successful Unauthorised Access

Validated attack failure by filtering successful authentication events:

```bash
grep "Accepted password" /var/log/auth.log
grep "Accepted publickey" /var/log/auth.log
```

**Finding:** All successful logins originated exclusively from **analyst's own IP address**. Zero successful unauthorised access confirmed. Attacker was unable to enumerate valid usernames.

---

### Phase 5 — Threat Intelligence Enrichment

Investigated both attacker IPs using VirusTotal threat intelligence platform.

**IP Address 1 Analysis:**
- Flagged by **8 out of 92** security vendors as malicious
- Identified as **command and control (C2) cloud infrastructure**
- Country of origin: **Netherlands**
- Classification: Known botnet-associated IP

**IP Address 2 Analysis:**
- Flagged by **2 out of 22** security vendors as malicious
- Cloud-hosted infrastructure consistent with botnet operations

**Assessment:** Both IPs represent **commodity botnet infrastructure** — rented cloud instances running automated SSH scanning tools across internet-facing IP ranges. The Netherlands origin does not necessarily indicate attacker location — cloud infrastructure is geographically distributed and routinely used to anonymise attack origin.

---

### Phase 6 — Attack Timeline Reconstruction

| Time Indicator | Observation |
|---|---|
| Attack start | First failed attempt recorded in auth.log |
| Request interval | 5–6 seconds between each attempt |
| Attack method | Automated credential stuffing — rotating usernames |
| Attack duration | Sustained over multiple hours |
| Attack outcome | Zero successful intrusions |

---

## 4. Attack Classification

| Attribute | Detail |
|---|---|
| **Attack Type** | SSH Brute Force / Credential Stuffing |
| **Vector** | SSH Port 22 (open to internet) |
| **Automation** | Confirmed — 5–6 second intervals, rotating usernames |
| **Origin** | Botnet — Netherlands-based cloud infrastructure |
| **Target** | Opportunistic — not a targeted attack |
| **Attacker Capability** | Unable to enumerate valid username |
| **Impact** | Zero — no unauthorised access achieved |

---

## 5. Containment & Remediation

### Action 1 — Blocked Attacker IPs via UFW Firewall

Deployed firewall deny rules to block both malicious IP addresses at the network level:

```bash
sudo ufw allow 22/tcp
sudo ufw deny from <IP_ADDRESS_1> to any
sudo ufw deny from <IP_ADDRESS_2> to any
sudo ufw enable
sudo ufw status verbose
```

**Result:** Both attacker IPs blocked. Future connection attempts from these addresses dropped at the firewall.

---

### Action 2 — Rotated SSH Credentials

Changed the SSH account password to invalidate any potentially compromised credentials:

```bash
sudo passwd ubuntu
```

**Result:** Credentials rotated. Even if attacker had obtained the previous password through other means, it is no longer valid.

---

### Action 3 — Enforced SSH Key-Only Authentication

Disabled password-based SSH authentication entirely, enforcing private key authentication only — eliminating brute force as a viable attack vector:

```bash
sudo nano /etc/ssh/sshd_config
```

Modified the following directives:
```
PasswordAuthentication no
PubkeyAuthentication yes
```

Restarted SSH service to apply changes:
```bash
sudo systemctl restart sshd
```

**Result:** Password authentication disabled. Brute force attacks are now ineffective — no password exists to guess.

---

## 6. Key Findings & Lessons Learned

**Finding 1 — Default SSH Port Exposure**  
Port 22 open to the internet on a cloud instance immediately attracts automated scanners. Within months of creation, this instance accumulated 200+ brute force attempts — demonstrating the hostile nature of the internet-facing attack surface.

**Finding 2 — Automated Botnet Scanning is Constant**  
Modern botnets continuously scan internet IP ranges for exposed SSH ports. This was not a targeted attack — the instance was identified opportunistically through automated scanning, not deliberate targeting.

**Finding 3 — Strong Credentials Prevented Compromise**  
Despite 200+ attempts, the attacker failed to gain access. This confirms the value of strong, non-default passwords as a first line of defence.

**Finding 4 — SSH Key Authentication is Superior**  
Disabling password authentication entirely removes the brute force attack surface. Key-based authentication should be the default configuration for any internet-facing SSH service.

**Finding 5 — auth.log is a Critical Detection Resource**  
Without reviewing `/var/log/auth.log`, this attack would have gone undetected. Regular log review and alerting on authentication anomalies is essential for early detection.

---

## 7. Recommendations

**Immediate (Implemented):**
- Block known malicious IPs via UFW firewall rules
- Rotate SSH credentials
- Enforce SSH key-only authentication

**Short Term:**
- Deploy **fail2ban** to automatically block IPs after N failed attempts — prevents sustained brute force campaigns
- Change SSH port from default 22 to a non-standard port — reduces automated scanner noise
- Implement **CloudWatch** or log shipping to a SIEM for centralised monitoring and alerting

**Long Term:**
- Restrict SSH access to specific trusted IP ranges via security group rules in AWS
- Implement multi-factor authentication for all remote access
- Schedule regular auth.log reviews or automated alerting on authentication anomaly thresholds

---

## 8. MITRE ATT&CK Mapping

| Tactic | Technique | ID |
|---|---|---|
| Initial Access | Brute Force — Password Spraying | T1110.003 |
| Initial Access | Brute Force — Credential Stuffing | T1110.004 |
| Reconnaissance | Active Scanning | T1595 |

---

## 9. Indicators of Compromise (IOCs)

| Type | Value | Verdict |
|---|---|---|
| IP Address | Attacker IP 1 | Malicious — 8/92 VirusTotal |
| IP Address | Attacker IP 2 | Malicious — 2/22 VirusTotal |
| Pattern | 200+ failed SSH attempts | Brute force indicator |
| Pattern | 5–6 second request intervals | Automated tooling |
| Pattern | Rotating usernames | Credential stuffing |

---

## 10. Tools & Technologies Used

| Tool | Purpose |
|---|---|
| `/var/log/auth.log` | Primary log source for detection and investigation |
| `grep` | Filtered and extracted relevant log events |
| `awk` | Parsed log fields — extracted usernames and IPs |
| `wc -l` | Quantified attack volume |
| VirusTotal | Threat intelligence enrichment on attacker IPs |
| UFW (Uncomplicated Firewall) | Deployed IP block rules |
| `passwd` | Rotated SSH credentials |
| `sshd_config` | Enforced key-only authentication |

---

## Conclusion

Successfully detected, investigated, and contained a real-world SSH brute force attack against an AWS EC2 instance using native Linux log analysis techniques. Demonstrated end-to-end SOC analyst capabilities — from initial detection through threat intelligence enrichment, attack classification, and multi-layered remediation. The incident resulted in zero successful unauthorised access and produced actionable security improvements that significantly reduced the attack surface of the environment.

---

*Incident documented for professional portfolio — SOC L1 Analyst Development*  
*Date: April 2026*
