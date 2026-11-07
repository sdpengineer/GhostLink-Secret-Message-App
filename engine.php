<?php
session_start();
require 'config.php';
date_default_timezone_set('Asia/Kolkata');
header('X-Content-Type-Options: nosniff');
if (!isset($_SESSION['user_id'])) { http_response_code(401); die('ACCESS DENIED'); }

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) { http_response_code(500); die('Connection failed'); }
$conn->set_charset('utf8mb4');
$mode = $_POST['mode'] ?? '';
$my_id = (int)$_SESSION['user_id'];
$conn->query("UPDATE users SET last_activity = NOW() WHERE id = $my_id");

function gl_json($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
}
function gl_upload_path(string $path): ?string {
    if (!preg_match('~^uploads/[A-Za-z0-9._-]+$~', $path)) return null;
    $root = realpath(__DIR__ . '/uploads');
    $file = realpath(__DIR__ . '/' . $path);
    if (!$root || !$file || dirname($file) !== $root || !is_file($file)) return null;
    return $file;
}
function gl_remove_attachment(?string $path): void {
    if (!$path) return;
    $file = gl_upload_path($path);
    if ($file) @unlink($file);
}

if ($mode === 'get_users') {
    $search = trim((string)($_POST['search'] ?? ''));
    $like = '%' . $search . '%';
    $stmt = $conn->prepare("SELECT id, username, bio, phone, (last_activity > NOW() - INTERVAL 2 MINUTE) AS is_online FROM users WHERE id <> ? AND (? = '' OR username LIKE ? OR phone LIKE ?) ORDER BY username");
    $stmt->bind_param('isss', $my_id, $search, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result->num_rows) { echo "<div class='empty'>No people found.</div>"; exit; }
    while ($row = $result->fetch_assoc()) {
        $id = (int)$row['id'];
        $name = htmlspecialchars($row['username'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $bio = htmlspecialchars(mb_substr((string)$row['bio'], 0, 60), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $phone = htmlspecialchars((string)$row['phone'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $online = (int)$row['is_online'] === 1;
        $dot = $online ? '#00ff41' : '#687386';
        $state = $online ? 'ONLINE' : 'OFFLINE';
        echo "<button type='button' class='user-card' data-user-id='$id' data-username='$name' data-online='" . ($online ? '1' : '0') . "'><div><span class='status-dot' style='background:$dot'></span><b>$name</b><span class='contact-phone'> ($phone)</span><div class='contact-bio'>$bio</div></div><span class='contact-status' style='color:$dot'>$state</span></button>";
    }
    exit;
}

if ($mode === 'send_msg') {
    $receiver = filter_var($_POST['target'] ?? null, FILTER_VALIDATE_INT);
    $message = (string)($_POST['msg'] ?? '');
    $viewOnce = (int)($_POST['view_once'] ?? 0) === 1 ? 1 : 0;
    $attachment = trim((string)($_POST['attachment_path'] ?? ''));
    if (!$receiver || $receiver === $my_id || $message === '' || strlen($message) > 500000) { http_response_code(400); exit('Invalid message'); }
    if ($attachment !== '' && !preg_match('~^uploads/[A-Za-z0-9._-]+$~', $attachment)) { http_response_code(400); exit('Invalid attachment'); }
    $check = $conn->prepare('SELECT id FROM users WHERE id = ?');
    $check->bind_param('i', $receiver); $check->execute();
    if (!$check->get_result()->num_rows) { http_response_code(404); exit('Recipient not found'); }
    $stmt = $conn->prepare('INSERT INTO messages (sender_id, receiver_id, message, view_once, attachment_path) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('iisis', $my_id, $receiver, $message, $viewOnce, $attachment);
    if ($stmt->execute()) echo 'sent'; else { http_response_code(500); echo 'Could not save message'; }
    exit;
}

if ($mode === 'get_msgs') {
    $other = filter_var($_POST['target'] ?? null, FILTER_VALIDATE_INT);
    if (!$other || $other === $my_id) { http_response_code(400); exit('Invalid conversation'); }
    // Mark ordinary incoming messages read when the conversation is opened.
    $read = $conn->prepare('INSERT IGNORE INTO message_reads (message_id, user_id) SELECT id, ? FROM messages WHERE sender_id = ? AND receiver_id = ? AND view_once = 0');
    $read->bind_param('iii', $my_id, $other, $my_id); $read->execute();
    $stmt = $conn->prepare('SELECT id, sender_id, receiver_id, message, timestamp, view_once, viewed_at FROM messages WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) AND (view_once = 0 OR sender_id = ? OR viewed_at IS NULL) ORDER BY timestamp ASC');
    $stmt->bind_param('iiiii', $my_id, $other, $other, $my_id, $my_id); $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $id = (int)$row['id'];
        $isMine = (int)$row['sender_id'] === $my_id;
        $type = $isMine ? 'sent' : 'received';
        $once = (int)$row['view_once'] === 1;
        $time = htmlspecialchars(date('d-m-Y h:i:s A', strtotime($row['timestamp'])), ENT_QUOTES, 'UTF-8');
        $encrypted = (!$once || $isMine) ? htmlspecialchars((string)$row['message'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '';
        $readMark = '';
        if ($isMine && !$once) {
            $readStmt = $conn->prepare('SELECT 1 FROM message_reads WHERE message_id = ? AND user_id = ? LIMIT 1');
            $readStmt->bind_param('ii', $id, $other); $readStmt->execute();
            if ($readStmt->get_result()->num_rows) $readMark = ' read';
        }
        $reactions = [];
        $reactionStmt = $conn->prepare('SELECT reaction, COUNT(*) AS total FROM message_reactions WHERE message_id = ? GROUP BY reaction');
        $reactionStmt->bind_param('i', $id); $reactionStmt->execute();
        $reactionResult = $reactionStmt->get_result();
        while ($reaction = $reactionResult->fetch_assoc()) $reactions[] = ['emoji' => $reaction['reaction'], 'count' => (int)$reaction['total']];
        $reactionData = htmlspecialchars(json_encode($reactions, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
        echo "<div id='msg_$id' class='msg $type$readMark' data-enc='$encrypted' data-time='$time' data-view-once='" . ($once ? '1' : '0') . "' data-reactions='$reactionData'>Encrypted...</div>";
    }
    exit;
}

if ($mode === 'open_view_once') {
    $id = filter_var($_POST['msg_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id) { gl_json(['error' => 'Invalid message'], 400); exit; }
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare('SELECT id, message, attachment_path, timestamp FROM messages WHERE id = ? AND receiver_id = ? AND view_once = 1 AND viewed_at IS NULL FOR UPDATE');
        $stmt->bind_param('ii', $id, $my_id); $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) { $conn->rollback(); gl_json(['error' => 'This message has already been opened or is unavailable'], 410); exit; }
        $update = $conn->prepare('UPDATE messages SET viewed_at = NOW() WHERE id = ? AND receiver_id = ? AND viewed_at IS NULL');
        $update->bind_param('ii', $id, $my_id); $update->execute();
        if ($update->affected_rows !== 1) { $conn->rollback(); gl_json(['error' => 'This message has already been opened'], 410); exit; }
        $conn->commit();
        gl_json(['message' => $row['message'], 'attachment' => $row['attachment_path'], 'time' => date('d-m-Y h:i:s A', strtotime($row['timestamp']))]);
    } catch (Throwable $e) { $conn->rollback(); gl_json(['error' => 'Could not open this message'], 500); }
    exit;
}

if ($mode === 'finish_view_once') {
    $id = filter_var($_POST['msg_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id) { http_response_code(400); exit('Invalid message'); }
    $stmt = $conn->prepare('SELECT attachment_path FROM messages WHERE id = ? AND receiver_id = ? AND view_once = 1 AND viewed_at IS NOT NULL');
    $stmt->bind_param('ii', $id, $my_id); $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) { http_response_code(404); exit('Not found'); }
    $delete = $conn->prepare('DELETE FROM messages WHERE id = ? AND receiver_id = ? AND view_once = 1 AND viewed_at IS NOT NULL');
    $delete->bind_param('ii', $id, $my_id); $delete->execute();
    if ($delete->affected_rows) { gl_remove_attachment($row['attachment_path']); echo 'deleted'; }
    else { http_response_code(409); echo 'Already removed'; }
    exit;
}

if ($mode === 'toggle_reaction') {
    $id = filter_var($_POST['msg_id'] ?? null, FILTER_VALIDATE_INT);
    $emoji = (string)($_POST['reaction'] ?? '');
    $allowed = ['👍','❤️','😂','😮','😢','🎉'];
    if (!$id || !in_array($emoji, $allowed, true)) { http_response_code(400); exit('Invalid reaction'); }
    $check = $conn->prepare('SELECT id FROM messages WHERE id = ? AND (sender_id = ? OR receiver_id = ?)');
    $check->bind_param('iii', $id, $my_id, $my_id); $check->execute();
    if (!$check->get_result()->num_rows) { http_response_code(404); exit('Not found'); }
    $find = $conn->prepare('SELECT 1 FROM message_reactions WHERE message_id = ? AND user_id = ? AND reaction = ?');
    $find->bind_param('iis', $id, $my_id, $emoji); $find->execute();
    if ($find->get_result()->num_rows) { $stmt = $conn->prepare('DELETE FROM message_reactions WHERE message_id = ? AND user_id = ? AND reaction = ?'); $stmt->bind_param('iis', $id, $my_id, $emoji); $stmt->execute(); }
    else { $stmt = $conn->prepare('INSERT INTO message_reactions (message_id, user_id, reaction) VALUES (?, ?, ?)'); $stmt->bind_param('iis', $id, $my_id, $emoji); $stmt->execute(); }
    echo 'ok'; exit;
}

if ($mode === 'delete_msg') {
    $id = filter_var($_POST['msg_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id) { http_response_code(400); exit('Invalid message'); }
    $find = $conn->prepare('SELECT attachment_path FROM messages WHERE id = ? AND sender_id = ?');
    $find->bind_param('ii', $id, $my_id); $find->execute(); $row = $find->get_result()->fetch_assoc();
    if ($row) { $delete = $conn->prepare('DELETE FROM messages WHERE id = ? AND sender_id = ?'); $delete->bind_param('ii', $id, $my_id); $delete->execute(); if ($delete->affected_rows) { gl_remove_attachment($row['attachment_path']); echo 'deleted'; } }
    exit;
}

if ($mode === 'update_profile') {
    $bio = trim((string)($_POST['bio'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $stmt = $conn->prepare('UPDATE users SET bio = ?, phone = ? WHERE id = ?');
    $stmt->bind_param('ssi', $bio, $phone, $my_id); $stmt->execute(); echo 'updated'; exit;
}
if ($mode === 'get_my_profile') {
    $stmt = $conn->prepare('SELECT username, bio, phone FROM users WHERE id = ?');
    $stmt->bind_param('i', $my_id); $stmt->execute(); gl_json($stmt->get_result()->fetch_assoc()); exit;
}
http_response_code(400);
echo 'Unknown mode';
?>
