<?php

namespace Waterloobae\QuizConverter;
require_once __DIR__ . '/../vendor/autoload.php';

$included_files = get_included_files();
echo "Autoloaded classes:\n";
foreach ($included_files as $filename) {
    echo "$filename\n <br>";
}

// use H5P\H5PFileStorage;
// use H5P\H5PDatabase;
// use H5P\H5PEventDispatcher;
// use H5P\H5POptionStorage;
// use H5P\H5PContentValidator;
// use H5P\H5PExport;
// use H5P\H5PFramework;

require_once __DIR__ . '/../src/H5PFramework.php';

use H5PCore;
use H5PFramework;

// Path to H5P content and core library
$h5pLibraryPath = __DIR__ . '/../vendor/h5p/h5p-core';
$h5pLibraryPath = __DIR__ . '/../vendor/h5p';
$h5pContentPath = __DIR__ ;

$h5pFramework = new H5PFramework();

// Initialize H5P Core
$h5pCore = new H5PCore($h5pFramework, $h5pLibraryPath, $h5pContentPath, 'en');

// The .h5p file you want to display
$h5pFile = __DIR__ . '/H5PMath.h5p';
echo "H5P File: $h5pFile <br>";
// Check if the file exists
if (!file_exists($h5pFile)) {
    die("H5P file not found: $h5pFile");
}


// Load and display the H5P content
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H5P Quiz Viewer</title>
    <link rel="stylesheet" href="/vendor/h5p/h5p-core/styles/h5p.css">
</head>
<body>

<h2>H5P Quiz</h2>
<div class="h5p-container">
    <?php
    // Load the H5P content
    $h5pContent = $h5pCore->loadLibrary($h5pFile, null, null);
    
    if ($h5pContent) {
        // Render the H5P content
        // Display the content
        echo "<div class='h5p-content'>";
        echo "<h3>" . htmlspecialchars($h5pContent['title']) . "</h3>";
        echo "<div class='h5p-content-body'>";
        echo $h5pContent['content'];
        echo "</div>";
        echo "</div>";
        // Display the content metadata
    } else {
        echo "<p>Failed to load H5P content.</p>";
    }
    ?>
    
</div>
