#!/usr/bin/env bash

# Validates generated Markdown documentation with Vale.
# This script should be run after generating SDKs to ensure documentation quality.
#
# Usage:
#   ./scripts/validate-markdown.sh [sdk_dir]
#
# Arguments:
#   sdk_dir: Path to the SDK examples directory (default: current directory)
#
# Prerequisites:
#   - Vale must be installed (https://vale.sh/)
#   - SDK must be generated first (php example.php <target>)

set -euo pipefail

sdk_dir="${1:-.}"
sdk_dir="$(cd "$sdk_dir" && pwd)"

# Check if Vale is installed
if ! command -v vale &> /dev/null; then
    echo "Error: Vale is not installed."
    echo "Install it from https://vale.sh/"
    exit 1
fi

# Check if .vale.ini exists
if [[ ! -f ".vale.ini" ]]; then
    echo "Error: .vale.ini not found in current directory."
    echo "Please run this script from the repository root."
    exit 1
fi

echo "Running Vale on generated Markdown files in: $sdk_dir"

# Run Vale and capture output
if vale "$sdk_dir" 2>&1; then
    echo "✓ All Markdown files pass Vale checks."
    exit 0
else
    echo "✗ Vale found issues in generated Markdown files."
    echo ""
    echo "To fix violations:"
    echo "1. Check the template files in templates/<lang>/"
    echo "2. Update the template to address the violation"
    echo "3. Regenerate the SDK: php example.php <target>"
    echo "4. Run this check again"
    exit 1
fi
