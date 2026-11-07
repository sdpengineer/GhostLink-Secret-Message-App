<?php
session_start();
require 'config.php';
date_default_timezone_set('Asia/Kolkata');
header('X-Content-Type-Options: nosniff');
if (!isset($_SESSION['user_id'])) { http_response_code(401); exit('ACCESS DENIED'); }
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) { http_response_code(500); exit('Connection failed'); }
$conn->set_charset('utf8mb4');
$me = (int)$_SESSION['user_id'];
$mode = $_POST['mode'] ?? '';
$conn->query("UPDATE users SET last_activity = NOW() WHERE id = $me");
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function uploadFilePath(?string $path): ?string {
    if (!$path || !preg_match('~^uploads/[A-Za-z0-9._-]+$~', $path)) return null;
    $root = realpath(__DIR__ . '/uploads'); $file = realpath(__DIR__ . '/' . $path);
    return ($root && $file && dirname($file) === $root && is_file($file)) ? $file : null;
}
function removeUpload(?string $path): void { $file = uploadFilePath($path); if ($file) @unlink($file); }

if ($mode === 'get_users') {
    $search = trim((string)($_POST['search'] ?? '')); $like = '%' . $search . '%';
    $sql = "SELECT u.id,u.username,u.bio,u.phone,(u.last_activity>NOW()-INTERVAL 2 MINUTE) is_online,
        (SELECT COUNT(*) FROM messages m LEFT JOIN message_reads r ON r.message_id=m.id AND r.user_id=? WHERE m.sender_id=u.id AND m.receiver_id=? AND r.message_id IS NULL AND m.view_once=0) unread_count
        FROM users u WHERE u.id<>? AND (?='' OR u.username LIKE ? OR u.phone LIKE ?) ORDER BY u.username";
    $stmt=$conn->prepare($sql);$stmt->bind_param('iiisss',$me,$me,$me,$search,$like,$like);$stmt->execute();$rows=$stmt->get_result();
    if(!$rows->num_rows){echo "<div class='empty'>No people found.</div>";exit;}
    while($u=$rows->fetch_assoc()){
        $id=(int)$u['id'];$name=e($u['username']);$bio=e(mb_substr((string)$u['bio'],0,60));$online=(int)$u['is_online']===1;$unread=(int)$u['unread_count'];
        echo "<button type='button' class='user-card' data-user-id='$id' data-username='$name' data-online='".($online?'1':'0')."'><span class='avatar'>".e(mb_strtoupper(mb_substr($u['username'],0,1)))."</span><span class='contact-meta'><b>$name</b><small>$bio</small></span><span class='presence-dot ".($online?'online':'offline')."'></span><span class='unread-badge' ".($unread?'':'hidden').">".($unread>99?'99+':$unread)."</span></button>";
    }exit;
}
if($mode==='typing'){
    $peer=filter_var($_POST['target']??null,FILTER_VALIDATE_INT);$typing=(int)($_POST['typing']??0)?1:0;
    if(!$peer||$peer===$me){http_response_code(400);exit('invalid');}
    $stmt=$conn->prepare('INSERT INTO chat_typing (user_id,peer_id,is_typing,updated_at) VALUES (?,?,?,NOW()) ON DUPLICATE KEY UPDATE is_typing=VALUES(is_typing),updated_at=NOW()');$stmt->bind_param('iii',$me,$peer,$typing);$stmt->execute();echo 'ok';exit;
}
if($mode==='get_typing'){
    $peer=filter_var($_POST['target']??null,FILTER_VALIDATE_INT);if(!$peer||$peer===$me){http_response_code(400);exit('0');}
    $stmt=$conn->prepare('SELECT is_typing FROM chat_typing WHERE user_id=? AND peer_id=? AND updated_at>NOW()-INTERVAL 8 SECOND');$stmt->bind_param('ii',$peer,$me);$stmt->execute();echo $stmt->get_result()->fetch_column()?'1':'0';exit;
}
if($mode==='send_msg'){
    $peer=filter_var($_POST['target']??null,FILTER_VALIDATE_INT);$msg=(string)($_POST['msg']??'');$once=(int)($_POST['view_once']??0)?1:0;$attachment=trim((string)($_POST['attachment_path']??''));
    if(!$peer||$peer===$me||$msg===''||strlen($msg)>500000){http_response_code(400);exit('Invalid message');}
    if($attachment!==''&&!preg_match('~^uploads/[A-Za-z0-9._-]+$~',$attachment)){http_response_code(400);exit('Invalid attachment');}
    $check=$conn->prepare('SELECT id FROM users WHERE id=?');$check->bind_param('i',$peer);$check->execute();if(!$check->get_result()->num_rows){http_response_code(404);exit('Recipient not found');}
    $stmt=$conn->prepare('INSERT INTO messages (sender_id,receiver_id,message,view_once,attachment_path) VALUES (?,?,?,?,?)');$stmt->bind_param('iisis',$me,$peer,$msg,$once,$attachment);if($stmt->execute())echo 'sent';else{http_response_code(500);echo 'failed';}exit;
}
if($mode==='get_msgs'){
    $peer=filter_var($_POST['target']??null,FILTER_VALIDATE_INT);if(!$peer||$peer===$me){http_response_code(400);exit('Invalid conversation');}
    $read=$conn->prepare('INSERT IGNORE INTO message_reads(message_id,user_id) SELECT id,? FROM messages WHERE sender_id=? AND receiver_id=? AND view_once=0');$read->bind_param('iii',$me,$peer,$me);$read->execute();
    $stmt=$conn->prepare('SELECT id,sender_id,message,timestamp,view_once,viewed_at FROM messages WHERE ((sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)) AND (view_once=0 OR sender_id=? OR viewed_at IS NULL) ORDER BY timestamp ASC');$stmt->bind_param('iiiii',$me,$peer,$peer,$me,$me);$stmt->execute();$rows=$stmt->get_result();
    while($m=$rows->fetch_assoc()){
        $id=(int)$m['id'];$mine=(int)$m['sender_id']===$me;$once=(int)$m['view_once']===1;$date=date('Y-m-d',strtotime($m['timestamp']));$time=date('g:i A',strtotime($m['timestamp']));
        $cipher=(!$once||$mine)?e($m['message']):'';$readMark=0;
        if($mine&&!$once){$r=$conn->prepare('SELECT 1 FROM message_reads WHERE message_id=? AND user_id=? LIMIT 1');$r->bind_param('ii',$id,$peer);$r->execute();$readMark=$r->get_result()->num_rows?1:0;}
        $reactions=[];$r=$conn->prepare('SELECT reaction,COUNT(*) n FROM message_reactions WHERE message_id=? GROUP BY reaction');$r->bind_param('i',$id);$r->execute();$rr=$r->get_result();while($v=$rr->fetch_assoc())$reactions[]=['emoji'=>$v['reaction'],'count'=>(int)$v['n'];
        echo "<article id='msg_$id' class='msg ".($mine?'sent':'received')."' data-enc='$cipher' data-date='$date' data-time='$time' data-view-once='".($once?1:0)."' data-read='$readMark' data-reactions='".e(json_encode($reactions,JSON_UNESCAPED_UNICODE))."'>Encrypted</article>";
    }exit;
}
if($mode==='toggle_reaction'){
    $id=filter_var($_POST['msg_id']??null,FILTER_VALIDATE_INT);$emoji=(string)($_POST['reaction']??'');$allowed=['👍','❤️','😂','😮','😢','🎉'];if(!$id||!in_array($emoji,$allowed,true)){http_response_code(400);exit('invalid');}
    $c=$conn->prepare('SELECT id FROM messages WHERE id=? AND (sender_id=? OR receiver_id=?)');$c->bind_param('iii',$id,$me,$me);$c->execute();if(!$c->get_result()->num_rows){http_response_code(404);exit('not found');}
    $c=$conn->prepare('SELECT 1 FROM message_reactions WHERE message_id=? AND user_id=? AND reaction=?');$c->bind_param('iis',$id,$me,$emoji);$c->execute();if($c->get_result()->num_rows){$q=$conn->prepare('DELETE FROM message_reactions WHERE message_id=? AND user_id=? AND reaction=?');$q->bind_param('iis',$id,$me,$emoji);}else{$q=$conn->prepare('INSERT INTO message_reactions(message_id,user_id,reaction) VALUES(?,?,?)');$q->bind_param('iis',$id,$me,$emoji);}$q->execute();echo 'ok';exit;
}
if($mode==='open_view_once'){
    $id=filter_var($_POST['msg_id']??null,FILTER_VALIDATE_INT);$conn->begin_transaction();try{$q=$conn->prepare('SELECT message,attachment_path FROM messages WHERE id=? AND receiver_id=? AND view_once=1 AND viewed_at IS NULL FOR UPDATE');$q->bind_param('ii',$id,$me);$q->execute();$m=$q->get_result()->fetch_assoc();if(!$m){$conn->rollback();http_response_code(410);echo 'unavailable';exit;}$q=$conn->prepare('UPDATE messages SET viewed_at=NOW() WHERE id=? AND receiver_id=? AND viewed_at IS NULL');$q->bind_param('ii',$id,$me);$q->execute();$conn->commit();header('Content-Type: application/json');echo json_encode(['message'=>$m['message'],'attachment'=>$m['attachment_path']]);}catch(Throwable $e){$conn->rollback();http_response_code(500);echo 'error';}exit;
}
if($mode==='finish_view_once'){
    $id=filter_var($_POST['msg_id']??null,FILTER_VALIDATE_INT);$q=$conn->prepare('SELECT attachment_path FROM messages WHERE id=? AND receiver_id=? AND view_once=1 AND viewed_at IS NOT NULL');$q->bind_param('ii',$id,$me);$q->execute();$m=$q->get_result()->fetch_assoc();if(!$m){http_response_code(404);exit('not found');}$q=$conn->prepare('DELETE FROM messages WHERE id=? AND receiver_id=? AND view_once=1 AND viewed_at IS NOT NULL');$q->bind_param('ii',$id,$me);$q->execute();if($q->affected_rows){if($m['attachment_path'])removeUpload($m['attachment_path']);echo 'deleted';}exit;
}
if($mode==='delete_msg'){
    $id=filter_var($_POST['msg_id']??null,FILTER_VALIDATE_INT);$q=$conn->prepare('SELECT attachment_path FROM messages WHERE id=? AND sender_id=?');$q->bind_param('ii',$id,$me);$q->execute();$m=$q->get_result()->fetch_assoc();if($m){$q=$conn->prepare('DELETE FROM messages WHERE id=? AND sender_id=?');$q->bind_param('ii',$id,$me);$q->execute();if($q->affected_rows){removeUpload($m['attachment_path']);echo 'deleted';}}exit;
}
if($mode==='clear_chat'){
    $peer=filter_var($_POST['target']??null,FILTER_VALIDATE_INT);if(!$peer||$peer===$me){http_response_code(400);exit('invalid');}$files=[];$q=$conn->prepare('SELECT attachment_path FROM messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)');$q->bind_param('iiii',$me,$peer,$peer,$me);$q->execute();$r=$q->get_result();while($x=$r->fetch_assoc())if($x['attachment_path'])$files[]=$x['attachment_path'];$q=$conn->prepare('DELETE FROM messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)');$q->bind_param('iiii',$me,$peer,$peer,$me);$q->execute();foreach($files as $f)removeUpload($f);echo 'cleared';exit;
}
if($mode==='update_profile'){$bio=trim((string)($_POST['bio']??''));$phone=trim((string)($_POST['phone']??''));$q=$conn->prepare('UPDATE users SET bio=?,phone=? WHERE id=?');$q->bind_param('ssi',$bio,$phone,$me);$q->execute();echo 'updated';exit;}
if($mode==='get_my_profile'){$q=$conn->prepare('SELECT username,bio,phone FROM users WHERE id=?');$q->bind_param('i',$me);$q->execute();header('Content-Type: application/json');echo json_encode($q->get_result()->fetch_assoc());exit;}
http_response_code(400);echo 'Unknown mode';
?>
