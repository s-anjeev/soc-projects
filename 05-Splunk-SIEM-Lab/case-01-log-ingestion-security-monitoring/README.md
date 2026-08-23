# Log Ingestion Security Monitoring

## Summery
This will be our first step in learning Splunk. In this lab project, we have ingested Windows Security Events, Sysmon logs, and Apache access logs into our Splunk Cloud instance.  

This provides us with a centralized SIEM where we can ingest, store, manage, and access logs from different sources in a single platform.   

## Scenario
SecureSelf InfoTech is preparing to deploy a new production web server that will be publicly accessible. Before making it public, your job as a SOC Analyst is to ensure that the server's security and web activity logs are properly ingested into the Splunk SIEM.   

The goal is to establish centralized visibility into Windows Security Events, Sysmon activity, and Apache access logs so the SOC can monitor, detect, and investigate suspicious activity once the server goes live.    

## Objective
Ensure Windows Security, Sysmon, and Apache logs from the new production server are properly ingested into Splunk SIEM before the server goes public.  


## Step 1 - Install Splunk Universal Forwarder
The Splunk Universal Forwarder (UF) is a lightweight agent installed on servers or endpoints to collect and forward log data to Splunk. It is commonly used to send Windows Event Logs, Sysmon logs, application logs, and other machine-generated data to a central Splunk instance.   

`inputs.conf` is a key Splunk configuration file that defines what data the Universal Forwarder should collect and where that data comes from.  


The Splunk Universal Forwarder can be downloaded directly from the official Splunk website.   
During installation, the Splunk Universal Forwarder must be configured with the destination where the collected logs will be forwarded. This includes specifying the Splunk receiving server/hostname and the receiving port.   
![img](https://github.com/s-anjeev/soc-projects/blob/main/05-Splunk-SIEM-Lab/case-01-log-ingestion-security-monitoring/images/destination.png)  

Set up username and password when prompted.  
![img](https://github.com/s-anjeev/soc-projects/blob/main/05-Splunk-SIEM-Lab/case-01-log-ingestion-security-monitoring/images/credentials.png)  


## Step 2 - Configuring inputs.conf
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

## Step 3 - Installing the Splunk Cloud Universal Forwarder Credentials Package
After installing the Universal Forwarder, we download the Universal Forwarder credentials package from the Splunk Cloud instance. This package configures the Forwarder with the necessary connection details and certificates required to securely send data to Splunk Cloud.   

After downloading the package, a file named splunkclouduf.spl is saved to the system.  

Open PowerShell or Command Prompt as Administrator and navigate to:`C:\Program Files\SplunkUniversalForwarder\bin`  

Then run:`splunk.exe install app %HOMEPATH%\Downloads\splunkclouduf.spl`  
When prompted, enter Universal Forwarder username and password.  
If the installation is successful, Splunk displays:
`App %HOMEPATH%\Downloads\splunkclouduf.spl installed`   

This completes the installation of the Splunk Cloud credentials package and prepares the Universal Forwarder to securely forward data to the Splunk Cloud environment.   

![img](https://github.com/s-anjeev/soc-projects/blob/main/05-Splunk-SIEM-Lab/case-01-log-ingestion-security-monitoring/images/setup.png)  


## Step 4 - Sysmon and Apache Log Ingestion
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


## Indexes Sourcetypes in splunk
**Index:** A logical storage location where Splunk stores events. It helps organize and separate different types of data.   
`index=web` stores Apache web logs, while `index=sysmon` stores Sysmon logs.  

**Sourcetype:** A label that identifies the type and format of data in an event. It helps Splunk understand how to parse and extract fields from the data.  
`sourcetype=access_combined` identifies Apache access logs, while `sourcetype=sysmon` identifies Sysmon events.  

Before moving forward, it is worth noting that before ingesting logs into an index, we must create that index in the Splunk instance first. Otherwise, the data will not be ingested. We must also assign the appropriate sourcetype to ensure accurate parsing and field extraction.   


After making all these changes, restart the Splunk Universal Forwarder so that the changes can take effect.   
`./splunk.exe restart`


## Splunk Cloud 
To access your Splunk Cloud instance, open a browser and enter the Splunk Cloud URL provided in your email: https://prd-p-tlleq.splunkcloud.com/. Enter your username and password to log in.   

After a successful login, click on the Search feature. The Search & Reporting interface will appear, where we can use SPL (Search Processing Language) queries to search, filter, and correlate logs from different sources.   

![img](https://github.com/s-anjeev/soc-projects/blob/main/05-Splunk-SIEM-Lab/case-01-log-ingestion-security-monitoring/images/search.png)

For very basic filtering, we can use the index and source type to narrow down the results.  

**windows security event logs**
we can use `index="winserver" source="WinEventLog:Security"` to view only the Security logs collected from our Windows Server.   
![img](https://github.com/s-anjeev/soc-projects/blob/main/05-Splunk-SIEM-Lab/case-01-log-ingestion-security-monitoring/images/winserver.png)


**Sysmon event logs**
Similarly we can use `index="sysmon"` to view sysmon only logs.  
![img](https://github.com/s-anjeev/soc-projects/blob/main/05-Splunk-SIEM-Lab/case-01-log-ingestion-security-monitoring/images/sysmon.png)   


We have successfully completed the Splunk setup. Windows Security Events, Sysmon logs, and Apache access logs are being collected and forwarded to Splunk Cloud.  

The environment is now ready for log analysis, SPL queries, security monitoring, and SOC investigations.