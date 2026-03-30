#!/usr/bin/env bash

# Script to display the number of columns in the terminal

cols=$(( ${#TERM} ? $(tput cols) : 80 ))

echo ${cols}
