#!/usr/bin/env bash

# Script to repeat a string n times

string="$1"
times="${2:-1}"  # Use the second argument or set a default value of 1

for ((c = 1; c <= times; c++)); do
    echo -n "$string"
done
