<?php
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(401); exit('error:Access denied'); }
$target_dir = __DIR__ . '/uploads/';
if (!is_dir($target_dir) && !mkdir($target_dir, 0750, true)) exit("error:Server cannot create uploads folder.");
// Keep unreferenced uploads around for a day; view-once attachments are removed by engine.php immediately after viewing.
$now = time();
foreach (glob($target_dir . '*') ?: [] as $oldFile) {
    if (is_file($oldFile) && $now - filemtime($oldFile) >= 86400) @unlink($oldFile);
}
if (!isset($_FILES['file'])) exit('error:No file uploaded');
if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) exit('error:Upload failed');
$allowed = ['jpg','jpeg','png','gif','webp','pdf','doc','docx','xls','xlsx','zip','mp3','wav','mp4','m4a','ogg','webm'];
$ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed, true)) exit('error:Invalid file type');
if ($_FILES['file']['size'] > 25000000) exit('error:File too large (Max 25MB)');
$filename = bin2hex(random_bytes(20)) . '.' . $ext;
$destination = $target_dir . $filename;
if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) { @chmod($destination, 0640); exit('uploads/' . $filename); }
exit('error:Failed to move file');
?>
