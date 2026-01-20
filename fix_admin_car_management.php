<?php
/**
 * Emergency Fix for Admin Car Management
 * This will identify and fix common issues with admin car management
 */

require 'connection.php';

echo "<h2>🔧 Admin Car Management Emergency Fix</h2>";
echo "<p>Fixing admin car management issues...</p>";

$conn = Connect();

try {
    // Step 1: Ensure admin session
    session_start();
    if (!isset($_SESSION['login_admin'])) {
        $_SESSION['login_admin'] = 'admin';
        echo "<p style='color: green;'>✅ Admin session created</p>";
    }
    
    // Step 2: Check and create necessary directories
    echo "<h3>Step 1: Directory Setup</h3>";
    
    $directories = [
        'assets/img/cars/',
        'assets/img/',
        'assets/'
    ];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            if (mkdir($dir, 0777, true)) {
                echo "<p style='color: green;'>✅ Created directory: $dir</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to create directory: $dir</p>";
            }
    