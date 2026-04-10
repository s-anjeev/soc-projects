# Create temp folder path
$folderPath = "C:\tmp_lab"

# Create folder if it doesn't exist
if (!(Test-Path $folderPath)) {
    New-Item -ItemType Directory -Path $folderPath
}

# Define file path
$filePath = "$folderPath\hello.txt"

# Write content to file
"hello dear i am here" | Out-File -FilePath $filePath -Encoding UTF8

# Confirm output
Write-Host "File created at $filePath"