# Log Ingestion Security Monitoring

## Summery
This will be our first step in learning Splunk. In this lab project, we have ingested Windows Security Events, Sysmon logs, and Apache access logs into our Splunk Cloud instance.  

This provides us with a centralized SIEM where we can ingest, store, manage, and access logs from different sources in a single platform.   

## Scenario
SecureSelf InfoTech is preparing to deploy a new production web server that will be publicly accessible. Before making it public, your job as a SOC Analyst is to ensure that the server's security and web activity logs are properly ingested into the Splunk SIEM.   

The goal is to establish centralized visibility into Windows Security Events, Sysmon activity, and Apache access logs so the SOC can monitor, detect, and investigate suspicious activity once the server goes live.    

## Objective
Ensure Windows Security, Sysmon, and Apache logs from the new production server are properly ingested into Splunk SIEM before the server goes public.  


## Install Splunk Universal Forwarder
The Splunk Universal Forwarder (UF) is a lightweight agent installed on servers or endpoints to collect and forward log data to Splunk. It is commonly used to send Windows Event Logs, Sysmon logs, application logs, and other machine-generated data to a central Splunk instance.   

`inputs.conf` is a key Splunk configuration file that defines what data the Universal Forwarder should collect and where that data comes from.  


The Splunk Universal Forwarder can be downloaded directly from the official Splunk website.   
During installation, the Splunk Universal Forwarder must be configured with the destination where the collected logs will be forwarded. This includes specifying the Splunk receiving server/hostname and the receiving port.   
![img](https://github.com/s-anjeev/soc-projects/blob/main/05-Splunk-SIEM-Lab/case-01-log-ingestion-security-monitoring/images/destination.png)  

Set up username and password when prompted.  
![img](https://github.com/s-anjeev/soc-projects/blob/main/05-Splunk-SIEM-Lab/case-01-log-ingestion-security-monitoring/images/credentials.png)  


## Configuring inputs.conf
By default, inputs.conf may not exist in the local directory, so we create it manually at: `C:\Program Files\SplunkUniversalForwarder\etc\system\local\inputs.conf`  

The purpose of this configuration is to define which Windows logs should be collected, where they should be sent, and how Splunk should identify the incoming data. This allows us to control exactly what data is collected from the workstation and forwarded to our Splunk environment.  

Here is the inputs.conf configuration used in this lab:  
[WinEventLog://Security]   
index = winserver  
disabled = false  

[WinEventLog://System]  
index = winserver  
disabled = false  

[WinEventLog://Application]  
index = winserver  
disabled = false  

This configuration means I am ingesting Security, System, and Application Windows Event Logs into the `winserver` index of my Splunk Cloud instance for centralized monitoring and analysis.   

## Installing the Splunk Cloud Universal Forwarder Credentials Package
After installing the Universal Forwarder, we download the Universal Forwarder credentials package from the Splunk Cloud instance. This package configures the Forwarder with the necessary connection details and certificates required to securely send data to Splunk Cloud.   

After downloading the package, a file named splunkclouduf.spl is saved to the system.  

Open PowerShell or Command Prompt as Administrator and navigate to:`C:\Program Files\SplunkUniversalForwarder\bin`  

Then run:`splunk.exe install app %HOMEPATH%\Downloads\splunkclouduf.spl`  
When prompted, enter Universal Forwarder username and password.  
If the installation is successful, Splunk displays:
`App %HOMEPATH%\Downloads\splunkclouduf.spl installed`   

This completes the installation of the Splunk Cloud credentials package and prepares the Universal Forwarder to securely forward data to the Splunk Cloud environment.   

![img](https://github.com/s-anjeev/soc-projects/blob/main/05-Splunk-SIEM-Lab/case-01-log-ingestion-security-monitoring/images/setup.png)  


## Sysmon and Apache Log Ingestion
To ingest Apache and Sysmon logs, we add their respective inputs to the existing inputs.conf file.
For Apache, we use the following configuration:   

[monitor://C:\xampp\apache\logs\access.log]   
disabled = false   
index = web   
sourcetype = access_combined  

Here, C:\xampp\apache\logs\access.log specifies the log file that the Universal Forwarder will monitor. The index = web setting determines which Splunk index will store the events, while sourcetype = access_combined identifies the data as Apache access logs and helps Splunk apply the appropriate parsing and field extraction.  


For Sysmon, we configure the Windows Event Log input:
[WinEventLog://Microsoft-Windows-Sysmon/Operational]  
disabled = 0  
index = sysmon  
renderXml = false  
sourcetype = sysmon  

This configuration tells the Universal Forwarder to collect events from the Microsoft-Windows-Sysmon/Operational event log and forward them to the sysmon index. The sourcetype = sysmon identifies the events as Sysmon data, while renderXml = false controls how the Windows Event Log data is rendered before being forwarded.  

After adding these configurations, the Universal Forwarder can collect both Apache web activity and Sysmon endpoint telemetry and forward them to Splunk Cloud.   


