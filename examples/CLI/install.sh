#!/bin/bash

## <script src="/dist/scripts/cli-bash.js"></script>
## <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.16.0/themes/prism-okaidia.min.css" rel="stylesheet" />
## <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.16.0/components/prism-core.min.js" data-manual></script>
## <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.16.0/components/prism-bash.min.js"></script>
## <style>body {color: #272822; background-color: #272822; font-size: 0.8em;} </style>
# Love open-source, dev-tooling and passionate about code as much as we do?
# ---
# We're always looking for awesome hackers like you to join our 100% remote team!
# Check and see if you find any relevant position @ https://appwrite.io/company/careers 👩‍💻 😎
# (and let us know you found this message...)

# This script contains hidden JS code to allow better readability and syntax highlighting
# You can use "View source" of this page to see the full script.

# Appwrite CLI location
APPWRITE_INSTALL_DIR="/usr/local/bin"

# Appwrite CLI Executable name 
APPWRITE_EXECUTABLE_NAME=appwrite

# Appwrite executable file path 
APPWRITE_EXECUTABLE_FILEPATH="$APPWRITE_INSTALL_DIR/$APPWRITE_EXECUTABLE_NAME"

# Appwrite CLI temp name 
APPWRITE_TEMP_NAME=temp-$(date +%s)

# Appwrite CLI image name
APPWRITE_CLI_IMAGE_NAME=appwrite/cli

# Appwrite CLI image version 
APPWRITE_CLI_IMAGE_VERSION=0.0.1

# sudo is required to copy executable to APPWRITE_INSTALL_DIR for linux
USE_SUDO="false"

# Add some color to life
RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m' # No Color


greeting() {
    echo -e "${RED}"
    cat << "EOF"
    _                            _ _           ___   __   _____ 
   /_\  _ __  _ ____      ___ __(_) |_ ___    / __\ / /   \_   \
  //_\| '_ \| '_ \ \ /\ / / '__| | __/ _ \  / /   / /     / /\/
 /  _  \ |_) | |_) \ V  V /| |  | | ||  __/ / /___/ /___/\/ /_  
 \_/ \_/ .__/| .__/ \_/\_/ |_|  |_|\__\___| \____/\____/\____/  
       |_|   |_|                                                  
 
EOF
    echo -e "${NC}\n"
    echo "🔥 Welcome to the Appwrite CLI install shield 🛡."
}

getSystemInfo() {
    echo "[1/4] Getting System Info ..."
    ARCH=$(uname -m)
    case $ARCH in
        armv7*) ARCH="arm";;
        aarch64) ARCH="arm64";;
        x86_64) ARCH="amd64";;
    esac

    OS=$(echo `uname`|tr '[:upper:]' '[:lower:]')

    # Need root access if its a linux system
    if [ "$OS" == "linux" ] && [ "$APPWRITE_INSTALL_DIR" == "/usr/local/bin" ]; then
        USE_SUDO="true"
    fi
    
    # Need root access if its Apple Silicon
    if [ "$OS" == "darwin" ] && [[ "$(uname -a)" = *ARM64* ]]; then
        USE_SUDO="true"
    fi

    printf "${GREEN}\nOS : $OS \nARCH : $ARCH \nREQUIRES ROOT : $USE_SUDO\n\n${NC}"
}

runAsRoot() {
    local CMD="$*"
    if [ $EUID -ne 0 -a $USE_SUDO = "true" ]; then
        CMD="sudo $CMD"
    fi
    $CMD
}

printSuccess() {
    printf "${GREEN}✅ Done ... ${NC}\n\n"
}

performChecks() {
    echo "[2/4] Performing Checks ..."

    # Check if docker is installed 
    printf "${GREEN}🚦 Checking if docker is installed ... ${NC}\n"
    if ! command -v docker  &> /dev/null; then
        printf "${RED}❌ Docker could not be found. Please install docker for your OS from https://docs.docker.com/get-docker/ and try again.${NC}\n"
        exit 1
    fi
    printSuccess

    # Check if the Docker Daemon is running
    printf "${GREEN}🏃‍ Checking if docker daemon is running ... ${NC}\n"
    rep=$(curl -s --unix-socket /var/run/docker.sock http://ping > /dev/null)
    status=$?
    if [ "$status" == "7" ]; then
        printf "${RED}❌ The docker daemon is not operational. Make sure that docker is running and try again.${NC} \n"
        exit 1
    fi
    printSuccess

}

install() {

    echo "[3/4] Starting installation ..."

    # Fetch the Appwrite CLI Image.
    printf "${GREEN}🐳 Pulling docker image ... ${NC}\n"
    out=$(docker pull "$APPWRITE_CLI_IMAGE_NAME:$APPWRITE_CLI_IMAGE_VERSION")
    if [[ $out != *"Image is up to date"* ]] && [[ $out != *"Downloaded newer image"* ]]; then
        printf "${RED}❌ Failed to fetch docker image. Exiting ... ${NC}\n"
        exit 1
    fi
    printSuccess
    
    echo '#!/bin/bash

allowList=(version help init client account avatars database functions health locale storage teams users)

if [[ -z $1 ]]; then 
    set -- "$@" help
fi 

# Check if the command is in the allowList
if [[ ! " ${allowList[@]} " =~ " ${1} " ]]; then
    printf "\nLooks like a crazy hamster 🐹 flipped a bit.\n\nUse appwrite help for a list of supported commands.\n"
    exit 1
fi

# https://stackoverflow.com/a/30655982/2299554
for x in "${@}" ; do
    # try to figure out if quoting was required for the $x
    if [[ "$x" != "${x%[[:space:]]*}" ]]; then
        x="\""$x"\""
    fi
    x="${x// /%20}"
    _args=$_args" "$x
done

bash -c "docker run -i --rm --volume appwrite-cli:/usr/local/code/app/.preferences/ --volume \"$PWD\":/usr/local/code/files:rw --network host '$APPWRITE_CLI_IMAGE_NAME:$APPWRITE_CLI_IMAGE_VERSION' $_args" ' > $APPWRITE_TEMP_NAME

    printf "${GREEN}🚧 Setting Permissions ${NC}\n"
    chmod +x $APPWRITE_TEMP_NAME
    if [ $? -ne 0 ]; then
        printf "${RED}❌ Failed to set permissions ... ${NC}\n"
        exit 1
    fi
    printSuccess

    printf "${GREEN}📝 Copying temporary file to $APPWRITE_EXECUTABLE_FILEPATH ... ${NC}\n"
    runAsRoot cp $APPWRITE_TEMP_NAME $APPWRITE_EXECUTABLE_FILEPATH
    if [ $? -ne 0 ]; then
        printf "${RED}❌ Failed to copy temporary file to $APPWRITE_EXECUTABLE_FILEPATH ... ${NC}\n"
        exit 1
    fi
    printSuccess
}

cleanup() {
    echo "🧹 Cleaning up mess ... "
    rm $APPWRITE_TEMP_NAME 
    if [ $? -ne 0 ]; then
        printf "${RED}❌ Failed to remove temporary file ... ${NC}\n"
        exit 1
    fi
    printSuccess

}

installCompleted() {
    echo "[4/4] Wrapping up installation ... "
    cleanup
    printf "🤘 May the force be with you. \n"
    echo "🚀 To get started with Appwrite CLI, please visit https://appwrite.io/docs/command-line"
}

# Installation Starts here 
greeting
getSystemInfo
performChecks
install
installCompleted
