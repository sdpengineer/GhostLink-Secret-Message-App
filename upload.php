<?php
// 1. SETUP UPLOAD DIRECTORY
$target_dir = "uploads/";

// Create folder if missing
if (!file_exists($target_dir)) {
    if (!mkdir($target_dir, 0777, true)) {
        die("error:Server cannot create 'uploads' folder. Check permissions.");
    }
}

// 2. AUTO-DESTRUCT (Clean files > 5 mins old)
$files = glob($target_dir . "*");
$now = time();
if ($files) {
    foreach ($files as $file) {
        if (is_file($file) && ($now - filemtime($file) >= 300)) { 
            unlink($file);
        }
    }
}

// 3. UPLOAD HANDLER
if (isset($_FILES['file'])) {
    // Basic PHP Error Check
    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        die("error:Upload Error Code " . $_FILES['file']['error']);
    }

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'mp3', 'wav', 'mp4'];
    $filename = $_FILES['file']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // Security Checks
    if (!in_array($ext, $allowed)) die("error:Invalid File Type");
    if ($_FILES['file']['size'] > 25000000) die("error:File too large (Max 25MB)");

    $target_file = $target_dir . uniqid() . "." . $ext;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
        // CRITICAL FIX: Use die() to output ONLY the filename and stop immediately.
        // This prevents hidden spaces from breaking the receiver's preview.
        die($target_file); 
    } else {
        die("error:Failed to move file");
    }
}
?>