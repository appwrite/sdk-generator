## <script src="/dist/scripts/cli-powershell.js"></script>
## <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.16.0/themes/prism-okaidia.min.css" rel="stylesheet" />
## <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.16.0/components/prism-core.min.js" data-manual></script>
## <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.16.0/components/prism-powershell.min.js"></script>
## <style>body {color: #272822; background-color: #272822; font-size: 0.8em;} </style>
# Love open-source, dev-tooling and passionate about code as much as we do?
# ---
# We're always looking for awesome hackers like you to join our 100% remote team!
# Check and see if you find any relevant position @ https://appwrite.io/company/careers
# (and let us know you found this message...)

# This script contains hidden JS code to allow better readability and syntax highlighting
# You can use "View source" of this page to see the full script.

# Appwrite CLI location
$APPWRITE_INSTALL_DIR = Join-Path -Path $env:LOCALAPPDATA -ChildPath "Appwrite"

# Appwrite CLI Executable name 
$APPWRITE_EXECUTABLE_NAME = "appwrite.ps1"

# Appwrite executable file path 
$APPWRITE_EXECUTABLE_FILEPATH = Join-Path -Path $APPWRITE_INSTALL_DIR -ChildPath $APPWRITE_EXECUTABLE_NAME

# Appwrite CLI image name
$APPWRITE_CLI_IMAGE_NAME = "appwrite/cli"

# Appwrite CLI image version 
$APPWRITE_CLI_IMAGE_VERSION = "0.0.1"

$APPWRITE_EXECUTABLE_CONTENT = @"
`$allowList = 'version', 'help', 'init', 'client' , 'account', 'avatars', 'database', 'functions', 'health', 'locale', 'storage', 'teams', 'users'
if ( `$args.count -eq 0 ) {
    `$args += 'help'
}

if ( -not (`$allowList -contains `$args[0])) {
    Write-Host "Looks like a crazy hamster flipped a bit.`n`nUse appwrite help for a list of supported commands."
    exit 1
}

docker run -i --rm --volume appwrite-cli:/usr/local/code/app/.preferences/ --volume `$pwd``:/usr/local/code/files:rw --network host $APPWRITE_CLI_IMAGE_NAME`:$APPWRITE_CLI_IMAGE_VERSION (`$args | % {[uri]::EscapeUriString(`$_)})
"@

$USER_PATH_ENV_VAR = [Environment]::GetEnvironmentVariable("PATH", "User")

function Greeting {
    Write-Host @"
    _                            _ _           ___   __   _____ 
   /_\  _ __  _ ____      ___ __(_) |_ ___    / __\ / /   \_   \
  //_\| '_ \| '_ \ \ /\ / / '__| | __/ _ \  / /   / /     / /\/
 /  _  \ |_) | |_) \ V  V /| |  | | ||  __/ / /___/ /___/\/ /_  
 \_/ \_/ .__/| .__/ \_/\_/ |_|  |_|\__\___| \____/\____/\____/  
       |_|   |_|                                                  
 
"@ -ForegroundColor red
    Write-Host "Welcome to the Appwrite CLI install shield."
    
}

function CheckSystemInfo {
    Write-Host "[1/4] Getting System Info ..."
    if ((Get-ExecutionPolicy) -gt 'RemoteSigned' -or (Get-ExecutionPolicy) -eq 'ByPass') {
        Write-Host "PowerShell requires an execution policy of 'RemoteSigned'."
        Write-Host "To make this change please run:"
        Write-Host "'Set-ExecutionPolicy RemoteSigned -scope CurrentUser'"
        break
    }
}

function PerformChecks {

    Write-Host "[2/4] Performing Checks ..."

    if (!(docker --version)) {
        throw "Docker could not be found. Please install docker for your OS from https://docs.docker.com/get-docker/ and try again."
    }

    if (!(docker ps)) {
        throw "The docker daemon is not operational. Make sure that docker is running and try again."
    }
}

function Install {
    Write-Host "[3/4] Starting installation ..."

    $out = (Invoke-Expression "docker pull $APPWRITE_CLI_IMAGE_NAME`:$APPWRITE_CLI_IMAGE_VERSION") | Out-String

    if ($out -NotMatch "Image is up to date|Downloaded newer image") {
        throw "Failed to fetch docker image. Exiting ..."
    }

    [void](New-Item -Path $APPWRITE_EXECUTABLE_FILEPATH -Value $APPWRITE_EXECUTABLE_CONTENT -ItemType File -Force)

    if ($USER_PATH_ENV_VAR -like '*Appwrite*') {
        Write-Host "Skipping to add Appwrite to User Path."
    }
    else {
        [System.Environment]::SetEnvironmentVariable("PATH", $USER_PATH_ENV_VAR + ";$APPWRITE_INSTALL_DIR", "User")
    }
}

function CleanUp {
    Write-Host "Cleaning up mess ..."
}

function InstallCompleted {
    Write-Host "[4/4] Finishing Installation ... "
    cleanup
    Write-Host "🤘 May the force be with you."
    Write-Host "To get started with Appwrite CLI, please visit https://appwrite.io/docs/command-line"
}

Greeting
CheckSystemInfo
PerformChecks
Install
InstallCompleted
