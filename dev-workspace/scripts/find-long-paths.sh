#!/usr/bin/env php
<?php

function show_help()
{
    echo "Usage: find-long-paths.sh DIRECTORY [NUMBER_OF_PATHS]\n";
    echo "Options:\n";
    echo "  -h, --help        Display this help message.\n";
    echo "  DIRECTORY         Specify the directory to search for files.\n";
    echo "  NUMBER_OF_PATHS   (Optional) Specify the number of paths to display. Default is 10.\n";
}

// Check if the -h or --help option is given
if (in_array('-h', $argv) || in_array('--help', $argv)) {
    show_help();
    exit(0);
}

// Check if at least a directory is given as an argument
if ($argc < 2) {
    show_help();
    exit(1);
}

$dir = $argv[1];

// Check if the given directory exists
if (!is_dir($dir)) {
    echo "Error: Directory does not exist.\n";
    exit(1);
}

// Set the default number of paths to display
$number_of_paths = 10;

// Receive the number_of_paths as the second argument, using 10 as default if not set
if ($argc === 3) {
    $number_of_paths = intval($argv[2]);
}

$file_paths = [];

$directory = new RecursiveDirectoryIterator($dir);
$iterator = new RecursiveIteratorIterator($directory);

// Loop through all files in the directory recursively
foreach ($iterator as $info) {
    $filePath = $info->getPathname();

    // Skip files in subdirectories test, dist, and dev-workspace
    if (preg_match('#/(test|dist|dev-workspace|node_modules)/#', $filePath)) {
        continue;
    }

    $length = strlen($filePath);
    $file_paths[$length] = $filePath;
}

// Sort the lengths in descending order
krsort($file_paths);

// Output the longest paths
echo "The longest paths are:\n";

$count = 0;
foreach ($file_paths as $length => $path) {
    // Display the path along with its length
    echo "Length: $length characters, Path: $path\n";

    $count++;
    // Break the loop once the specified number of paths have been displayed
    if ($count >= $number_of_paths) {
        break;
    }
}

// If there were no files in the directory
if ($count === 0) {
    echo "No files found in the directory.\n";
}
