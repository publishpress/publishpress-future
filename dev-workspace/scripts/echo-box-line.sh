#!/usr/bin/env bash

# Script to draw box borders (helper for echo_header)

show_help() {
    echo "Usage: echo-box-line.sh <start_char> <content> <fill_char> <end_char>"
    echo ""
    echo "Example:"
    echo "echo-box-line.sh ╔ '═' '═' ╗"
    echo "echo-box-line.sh ╚ '═' '═' ╝"
    echo ""
}

if [ "$1" = "-h" ] || [ "$1" = "--help" ]; then
    show_help
    exit 0
fi

if [ $# -lt 4 ] || [ -z "$1" ] || [ -z "$2" ] || [ -z "$3" ] || [ -z "$4" ]; then
    show_help
    exit 1
fi

start_char=$1
content=$2
fill_char=$3
end_char=$4
cols=$(terminal-cols.sh)

# Calculate the number of fill characters needed
fill_length=$(( cols - ${#start_char} - ${#content} - ${#end_char} ))
if [ $fill_length -lt 0 ]; then
    fill_length=0
fi
fill=$(printf "%0.s$fill_char" $(seq 1 $fill_length))

# Output the line
echo "${start_char}${content}${fill}${end_char}"
