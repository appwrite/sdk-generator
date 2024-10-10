#!/bin/bash

# Check if all required arguments are provided
if [ "$#" -ne 3 ]; then
    echo "Usage: $0 <folder_path> <max_line_length> <file_globs>"
    exit 1
fi

folder_path="$1"
max_length="$2"
file_globs="$3"

# Function to get relative path
get_relative_path() {
    local path="$1"
    local base="$(pwd)"
    echo "${path#$base/}"
}

# Initialize a flag to check if any lines exceed the max length
found_lines=0

# Convert comma-separated globs to an array
IFS=',' read -ra glob_array <<< "$file_globs"

# Use find to get all files matching the glob patterns
for glob in "${glob_array[@]}"; do
    while IFS= read -r -d '' file; do
        # Get the relative path
        relative_path=$(get_relative_path "$file")
        
        # Use awk to process each file
        awk -v max="$max_length" -v file="$relative_path" '
        length($0) > max {
            print file ":" NR
            found = 1
            exit 1
        }
        END {
            exit found
        }' "$file"
        
        # Check awk's exit status
        if [ $? -eq 1 ]; then
            found_lines=1
        fi
    done < <(find "$folder_path" -type f -name "$glob" -print0)
done

# Exit with appropriate code
if [ $found_lines -eq 0 ]; then
    echo "All lines are within the $max_length character limit."
    exit 0
else
    echo "Some lines exceedded the $max_length character limit."
    exit 1
fi