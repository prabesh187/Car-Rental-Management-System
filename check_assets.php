<?php
echo "<h2>Assets Directory Check</h2>";

$directories_to_check = [
    'assets',
    'assets/img',
    'assets/img/cars'
];

foreach ($directories_to_check as $dir) {
    echo "<h3>Checking: $dir</h3>";
    
    if (file_exists($dir)) {
        if (is_dir($dir)) {
            echo "<p style='color: green;'>✓ Directory exists</p>";
            
            if (is_writable($dir)) {
                echo "<p style='color: green;'>✓ Directory is writable</p>";
            } else {
                echo "<p style='color: red;'>✗ Directory is NOT writable</p>";
                echo "<p>Try running: <code>chmod 755 $dir</code></p>";
            }
            
            // List contents
            $files = scandir($dir);
            $files = array_diff($files, array('.', '..'));
            
            if (!empty($files)) {
                echo "<p>Contents (" . count($files) . " items):</p>";
                echo "<ul>";
                foreach ($files as $file) {
                    $full_path = $dir . '/' . $file;
                    if (is_dir($full_path)) {
                        echo "<li>📁 $file/</li>";
                    } else {
                        $size = filesize($full_path);
                        echo "<li>📄 $file (" . number_format($size) . " bytes)</li>";
                    }
                }
                echo "</ul>";
            } else {
                echo "<p>Directory is empty</p>";
            }
            
        } else {
            echo "<p style='color: red;'>✗ Path exists but is not a directory</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ Directory does not exist</p>";
        echo "<p>Creating directory...</p>";
        
        if (mkdir($dir, 0755, true)) {
            echo "<p style='color: green;'>✓ Directory created successfully</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to create directory</p>";
        }
    }
    
    echo "<hr>";
}

// Test file upload simulation
echo "<h3>File Upload Test</h3>";
$test_content = "This is a test file for upload functionality";
$test_file = "assets/img/cars/test_upload.txt";

if (file_put_contents($test_file, $test_content)) {
    echo "<p style='color: green;'>✓ Test file created successfully: $test_file</p>";
    
    if (unlink($test_file)) {
        echo "<p style='color: green;'>✓ Test file deleted successfully</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Test file created but couldn't delete</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Failed to create test file</p>";
}

echo "<br><a href='admin_cars.php'>← Back to Admin Cars</a>";
?>