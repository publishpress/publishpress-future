#!/usr/bin/env php
<?php

function show_help()
{
    echo "Usage: check-dependencies.sh BASE_DIR NAMESPACE/PLUGIN_NAME\n";
    echo "Options:\n";
    echo "  -h, --help        Display this help message.\n";
    echo "  BASE_DIR          Specify the directory to search for files.\n";
    echo "  NAMESPACE         Specify the namespace of the plugin.\n";
    echo "  PLUGIN_NAME       Specify the plugin name.\n";
}

// Check if the -h or --help option is given
if (in_array('-h', $argv) || in_array('--help', $argv)) {
    show_help();
    exit(0);
}

$baseDir = $argv[1] ?? '';
if (empty($baseDir)) {
    echo "Error: Please provide a base dir.\n";
    show_help();
    exit(1);
}

$plugin = $argv[2] ?? '';
if (empty($plugin)) {
    echo "Error: Please provide a plugin name.\n";
    show_help();
    exit(1);
}

// Define file paths
$sourceComposerPath = "$baseDir/vendor/$plugin/lib/composer.json";
$destinationComposerPath = "$baseDir/composer.json";

// Check if the source composer file exists
if (!file_exists($sourceComposerPath)) {
    echo "Error: Source composer.json file does not exist.\n";
    exit(1);
}

// Check if the destination composer file exists
if (!file_exists($destinationComposerPath)) {
    echo "Error: Destination composer.json file does not exist.\n";
    exit(1);
}

// Read the contents of the source composer file
$sourceComposerContent = file_get_contents($sourceComposerPath);
$sourceComposerData = json_decode($sourceComposerContent, true);

// Read the contents of the destination composer file
$destinationComposerContent = file_get_contents($destinationComposerPath);
$destinationComposerData = json_decode($destinationComposerContent, true);

// Flag to indicate if all requirements are present
$allRequirementsPresent = true;
$missedRequirements = [];

// Check 'require' dependencies
if (isset($sourceComposerData['require'])) {
    foreach ($sourceComposerData['require'] as $package => $version) {
        if (!isset($destinationComposerData['require'][$package])) {
            $missedRequirements[] = sprintf('"%s": %s', $package, $version);
            $allRequirementsPresent = false;
        }
    }
}

// Output the result
if ($allRequirementsPresent) {
    echo "All requirements from the source file are present in the destination file.\n";
} else {
    echo "Some requirements from the Free plugin are missing in the Pro plugin:\n";
    foreach ($missedRequirements as $missedRequirement) {
        echo sprintf("%s\n", $missedRequirement);
    }

    echo "\n";
    echo "Please add the missing requirements running `composer fix:deps` and run `composer update` again.\n";
    exit(1);
}
