<?php
/**
 * Main entry point - redirects to HTML or installer
 */

// Check if installed
if (!file_exists(__DIR__ . '/.installed')) {
    header('Location: install.php');
    exit;
}

// Serve index.html
if (file_exists(__DIR__ . '/index.html')) {
    readfile(__DIR__ . '/index.html');
} else {
    http_response_code(404);
    echo 'Application not found. Please run the installer.';
}
