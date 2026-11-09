<?php
session_start();
require 'config.php';
date_default_timezone_set('Asia/Kolkata');
header('X-Content-Type-Options: nosniff');
if (!isset($_SESSION['user_id'])) { http_response_code(401); exit('ACCESS DENIED'); }

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) { http_response_code(500); exit('Connection failed'); }
$conn->set_charset('utf8mb4');
$me = (int) $_SESSION['user_id'];
$mode = $_POST['mode'] ?? '';
$conn->query("UPDATE users SET last_activity = NOW() WHERE id = $me");

function gl_escape($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function gl_upload_path(?string $path): ?string {
    if (!$path || !preg_match('~^uploads/[A-Za-z0-9._-]+$~', $path)) return null;
    $root = realpath(__DIR__ . '/uploads');
    $file = realpath(__DIR__ . '/' . $path);
    return ($root && $file && dirname($file) === $root && is_file($file)) ? $file : null;
}
function gl_remove_upload(?string $path): void {
    $file = gl_upload_path($path);
    if ($file) @unlink($file);
}

// Recover view-once messages whose viewer closed the page before cleanup completed.
$expired = $conn->query("SELECT id, attachment_path FROM messages WHERE view_once = 1 AND viewed_at < NOW() - INTERVAL 60 SECOND LIMIT 100");
if ($expired) {
    while ($row = $expired->fetch_assoc()) {
        $delete = $conn->prepare('DELETE FROM messages WHERE id = ? AND view_once = 1 AND viewed_at < NOW() - INTERVAL 60 SECOND');
        $delete->bind_param('i', $row['id']);
        $delete->execute();
        if ($delete->affected_rows) gl_remove_upload($row['attachment_path']);
    }
}

if ($mode === 'get_users') {
    $search = trim((string) ($_POST['search'] ?? ''));
    $like = '%' . $search . '%';
    $sql = "SELECT u.id, u.username, u.bio,
                (u.last_activity > NOW() - INTERVAL 2 MINUTE) AS is_online,
                (SELECT COUNT(*) FROM messages m
                 LEFT JOIN message_reads r ON r.message_id = m.id AND r.user_id = ?
                 WHERE m.sender_id = u.id AND m.receiver_id = ?
                   AND r.message_id IS NULL AND m.view_once = 0) AS unread_count
            FROM users u
            WHERE u.id <> ? AND (? = '' OR u.username LIKE ? OR u.phone LIKE ?)
            ORDER BY u.username";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiisss', $me, $me, $me, $search, $like, $like);
    $stmt->execute();
    $users = $stmt->get_result();
    if (!$users->num_rows) { echo "<div class='empty'>No people found.</div>"; exit; }
    while ($user = $users->fetch_assoc()) {
        $id = (int) $user['id'];
        $name = gl_escape($user['username']);
        $bio = gl_escape(mb_substr((string) $user['bio'], 0, 60));
        $online = (int) $user['is_online'] === 1;
        $unread = (int) $user['unread_count'];
        $initial = gl_escape(mb_strtoupper(mb_substr((string) $user['username'], 0, 1)));
        echo "<button type='button' class='user-card' data-user-id='$id' data-username='$name' data-online='" . ($online ? '1' : '0') . "'>"
            . "<span class='avatar'>$initial</span><span class='contact-meta'><b>$name</b><small>$bio</small></span>"
            . "<span class='presence-dot " . ($online ? 'online' : 'offline') . "'></span>"
            . "<span class='unread-badge' " . ($unread ? '' : 'hidden') . ">" . ($unread > 99 ? '99+' : $unread) . "</span></button>";
    }
    exit;
}

if ($mode === 'typing') {
    $peer = filter_var($_POST['target'] ?? null, FILTER_VALIDATE_INT);
    $typing = (int) ($_POST['typing'] ?? 0) ? 1 : 0;
    if (!$peer || $peer === $me) { http_response_code(400); exit('invalid'); }
    $stmt = $conn->prepare('INSERT INTO chat_typing (user_id, peer_id, is_typing, updated_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE is_typing = VALUES(is_typing), updated_at = NOW()');
    $stmt->bind_param('iii', $me, $peer, $typing);
    $stmt->execute();
    echo 'ok';
    exit;
}

if ($mode === 'get_typing') {
    $peer = filter_var($_POST['target'] ?? null, FILTER_VALIDATE_INT);
    if (!$peer || $peer === $me) { http_response_code(400); exit('0'); }
    $stmt = $conn->prepare('SELECT is_typing FROM chat_typing WHERE user_id = ? AND peer_id = ? AND updated_at > NOW() - INTERVAL 8 SECOND');
    $stmt->bind_param('ii', $peer, $me);
    $stmt->execute();
    echo $stmt->get_result()->fetch_column() ? '1' : '0';
    exit;
}

if ($mode === 'send_msg') {
    $peer = filter_var($_POST['target'] ?? null, FILTER_VALIDATE_INT);
    $message = (string) ($_POST['msg'] ?? '');
    $viewOnce = (int) ($_POST['view_once'] ?? 0) ? 1 : 0;
    $attachment = trim((string) ($_POST['attachment_path'] ?? ''));
    if (!$peer || $peer === $me || $message === '' || strlen($message) > 500000) { http_response_code(400); exit('Invalid message'); }
    if ($attachment !== '' && !gl_upload_path($attachment)) { http_response_code(400); exit('Invalid or unavailable attachment'); }
    $check = $conn->prepare('SELECT id FROM users WHERE id = ?');
    $check->bind_param('i', $peer);
    $check->execute();
    if (!$check->get_result()->num_rows) { http_response_code(404); exit('Recipient not found'); }
    $stmt = $conn->prepare('INSERT INTO messages (sender_id, receiver_id, message, view_once, attachment_path) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('iisis', $me, $peer, $message, $viewOnce, $attachment);
    if ($stmt->execute()) echo 'sent'; else { http_response_code(500); echo 'failed'; }
    exit;
}

if ($mode === 'get_msgs') {
    $peer = filter_var($_POST['target'] ?? null, FILTER_VALIDATE_INT);
    if (!$peer || $peer === $me) { http_response_code(400); exit('Invalid conversation'); }

    // Mark regular incoming messages as read before returning the conversation.
    $read = $conn->prepare('INSERT IGNORE INTO message_reads (message_id, user_id) SELECT id, ? FROM messages WHERE sender_id = ? AND receiver_id = ? AND view_once = 0');
    $read->bind_param('iii', $me, $peer, $me);
    $read->execute();

    $stmt = $conn->prepare('SELECT id, sender_id, message, timestamp, view_once FROM messages WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) AND (view_once = 0 OR (view_once = 1 AND (receiver_id <> ? OR viewed_at IS NULL))) ORDER BY timestamp ASC, id ASC');
    $stmt->bind_param('iiiii', $me, $peer, $peer, $me, $me);
    $stmt->execute();
    $messages = $stmt->get_result();
    while ($message = $messages->fetch_assoc()) {
        $id = (int) $message['id'];
        $mine = (int) $message['sender_id'] === $me;
        $once = (int) $message['view_once'] === 1;
        $date = date('Y-m-d', strtotime($message['timestamp']));
        $time = date('g:i A', strtotime($message['timestamp']));
        $cipher = (!$once || $mine) ? gl_escape($message['message']) : '';
        $readMark = 0;
        if ($mine && !$once) {
            $readQuery = $conn->prepare('SELECT 1 FROM message_reads WHERE message_id = ? AND user_id = ? LIMIT 1');
            $readQuery->bind_param('ii', $id, $peer);
            $readQuery->execute();
            $readMark = $readQuery->get_result()->num_rows ? 1 : 0;
        }
        $reactions = [];
        $reactionQuery = $conn->prepare('SELECT reaction, COUNT(*) AS reaction_count FROM message_reactions WHERE message_id = ? GROUP BY reaction');
        $reactionQuery->bind_param('i', $id);
        $reactionQuery->execute();
        $reactionRows = $reactionQuery->get_result();
        while ($reaction = $reactionRows->fetch_assoc()) {
            $reactions[] = ['emoji' => $reaction['reaction'], 'count' => (int) $reaction['reaction_count']];
        }
        $reactionData = gl_escape(json_encode($reactions, JSON_UNESCAPED_UNICODE));
        echo "<article id='msg_$id' class='msg " . ($mine ? 'sent' : 'received') . "' data-enc='$cipher' data-date='$date' data-time='$time' data-view-once='" . ($once ? '1' : '0') . "' data-read='$readMark' data-reactions='$reactionData'></article>";
    }
    exit;
}

if ($mode === 'toggle_reaction') {
    $id = filter_var($_POST['msg_id'] ?? null, FILTER_VALIDATE_INT);
    $emoji = (string) ($_POST['reaction'] ?? '');
    $allowed = ['👍', '❤️', '😂', '😮', '😢', '🎉'];
    if (!$id || !in_array($emoji, $allowed, true)) { http_response_code(400); exit('invalid'); }
    $check = $conn->prepare('SELECT id FROM messages WHERE id = ? AND (sender_id = ? OR receiver_id = ?)');
    $check->bind_param('iii', $id, $me, $me);
    $check->execute();
    if (!$check->get_result()->num_rows) { http_response_code(404); exit('not found'); }
    $find = $conn->prepare('SELECT 1 FROM message_reactions WHERE message_id = ? AND user_id = ? AND reaction = ?');
    $find->bind_param('iis', $id, $me, $emoji);
    $find->execute();
    if ($find->get_result()->num_rows) {
        $stmt = $conn->prepare('DELETE FROM message_reactions WHERE message_id = ? AND user_id = ? AND reaction = ?');
    } else {
        $stmt = $conn->prepare('INSERT INTO message_reactions (message_id, user_id, reaction) VALUES (?, ?, ?)');
    }
    $stmt->bind_param('iis', $id, $me, $emoji);
    $stmt->execute();
    echo 'ok';
    exit;
}

if ($mode === 'open_view_once') {
    $id = filter_var($_POST['msg_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id) { http_response_code(400); exit('invalid'); }
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare('SELECT message, attachment_path FROM messages WHERE id = ? AND receiver_id = ? AND view_once = 1 AND viewed_at IS NULL FOR UPDATE');
        $stmt->bind_param('ii', $id, $me);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) { $conn->rollback(); http_response_code(410); exit('unavailable'); }
        $update = $conn->prepare('UPDATE messages SET viewed_at = NOW() WHERE id = ? AND receiver_id = ? AND viewed_at IS NULL');
        $update->bind_param('ii', $id, $me);
        $update->execute();
        if ($update->affected_rows !== 1) { $conn->rollback(); http_response_code(410); exit('unavailable'); }
        $conn->commit();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['message' => $row['message'], 'attachment' => $row['attachment_path']]);
    } catch (Throwable $error) {
        $conn->rollback();
        http_response_code(500);
        echo 'error';
    }
    exit;
}

if ($mode === 'finish_view_once') {
    $id = filter_var($_POST['msg_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id) { http_response_code(400); exit('invalid'); }
    $stmt = $conn->prepare('SELECT attachment_path FROM messages WHERE id = ? AND receiver_id = ? AND view_once = 1 AND viewed_at IS NOT NULL');
    $stmt->bind_param('ii', $id, $me);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) { http_response_code(404); exit('not found'); }
    $delete = $conn->prepare('DELETE FROM messages WHERE id = ? AND receiver_id = ? AND view_once = 1 AND viewed_at IS NOT NULL');
    $delete->bind_param('ii', $id, $me);
    $delete->execute();
    if ($delete->affected_rows) { gl_remove_upload($row['attachment_path']); echo 'deleted'; }
    exit;
}

if ($mode === 'delete_msg') {
    $id = filter_var($_POST['msg_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id) { http_response_code(400); exit('invalid'); }
    $find = $conn->prepare('SELECT attachment_path FROM messages WHERE id = ? AND sender_id = ?');
    $find->bind_param('ii', $id, $me);
    $find->execute();
    $row = $find->get_result()->fetch_assoc();
    if ($row) {
        $delete = $conn->prepare('DELETE FROM messages WHERE id = ? AND sender_id = ?');
        $delete->bind_param('ii', $id, $me);
        $delete->execute();
        if ($delete->affected_rows) { gl_remove_upload($row['attachment_path']); echo 'deleted'; }
    }
    exit;
}

if ($mode === 'clear_chat') {
    $peer = filter_var($_POST['target'] ?? null, FILTER_VALIDATE_INT);
    if (!$peer || $peer === $me) { http_response_code(400); exit('invalid'); }
    $files = [];
    $find = $conn->prepare('SELECT attachment_path FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)');
    $find->bind_param('iiii', $me, $peer, $peer, $me);
    $find->execute();
    $result = $find->get_result();
    while ($row = $result->fetch_assoc()) if ($row['attachment_path']) $files[] = $row['attachment_path'];
    $delete = $conn->prepare('DELETE FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)');
    $delete->bind_param('iiii', $me, $peer, $peer, $me);
    $delete->execute();
    foreach ($files as $file) gl_remove_upload($file);
    echo 'cleared';
    exit;
}

if ($mode === 'update_profile') {
    $bio = trim((string) ($_POST['bio'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $stmt = $conn->prepare('UPDATE users SET bio = ?, phone = ? WHERE id = ?');
    $stmt->bind_param('ssi', $bio, $phone, $me);
    $stmt->execute();
    echo 'updated';
    exit;
}
if ($mode === 'get_my_profile') {
    $stmt = $conn->prepare('SELECT username, bio, phone FROM users WHERE id = ?');
    $stmt->bind_param('i', $me);
    $stmt->execute();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($stmt->get_result()->fetch_assoc());
    exit;
}
http_response_code(400);
echo 'Unknown mode';
?>
