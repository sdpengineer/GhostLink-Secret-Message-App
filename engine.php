<?php
session_start();
require 'config.php';

// 1. SET TIMEZONE TO IST (Indian Standard Time)
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['user_id'])) { die("ACCESS DENIED"); }

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) { die("Connection failed"); }

$mode = $_POST['mode'] ?? '';
$my_id = $_SESSION['user_id'];

// --- 0. UPDATE MY "LAST SEEN" (Every time engine is called) ---
$conn->query("UPDATE users SET last_activity = NOW() WHERE id = $my_id");

// --- 1. GET USERS (With Online Status) ---
if ($mode == 'get_users') {
    $search = $_POST['search'] ?? '';
    $search_sql = "";
    if($search != "") {
        $search = $conn->real_escape_string($search);
        $search_sql = "AND (username LIKE '%$search%' OR phone LIKE '%$search%')";
    }

    // SQL calculates if user was active in last 2 minutes
    $sql = "SELECT id, username, bio, phone, 
            (last_activity > NOW() - INTERVAL 2 MINUTE) as is_online 
            FROM users WHERE id != ? $search_sql";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $my_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $is_online = ($row['is_online'] == 1);
            $color = $is_online ? "#00ff41" : "#555"; 
            $status_text = $is_online ? "ONLINE" : "OFFLINE";
            $bio_short = substr(htmlspecialchars($row['bio']), 0, 25);

            echo "<div class='user-card' onclick='openChat(".$row['id'].", \"".$row['username']."\")'>
                    <div>
                        <span class='status-dot' style='background:$color; box-shadow: 0 0 5px $color;'></span> 
                        <b>" . htmlspecialchars($row['username']) . "</b>
                        <span style='font-size:10px; color:#555; margin-left:5px;'>(" . htmlspecialchars($row['phone']) . ")</span>
                        <div style='font-size: 10px; color: #777; margin-left: 15px;'>$bio_short...</div>
                    </div>
                    <span style='font-size:9px; color:$color'>$status_text</span>
                  </div>";
        }
    } else {
        echo "<div style='padding:20px; color:#444; text-align:center;'>No Agents Found</div>";
    }
}

// --- 2. SEND MESSAGE ---
if ($mode == 'send_msg') {
    $receiver_id = $_POST['target'];
    $encrypted_msg = $_POST['msg']; 
    // Uses NOW() for DB timestamp
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $my_id, $receiver_id, $encrypted_msg);
    $stmt->execute();
    echo "sent";
}

// --- 3. GET MESSAGES (IST Formatted) ---
if ($mode == 'get_msgs') {
    $other_id = $_POST['target'];
    $sql = "SELECT * FROM messages WHERE 
            (sender_id = ? AND receiver_id = ?) OR 
            (sender_id = ? AND receiver_id = ?) 
            ORDER BY timestamp ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $my_id, $other_id, $other_id, $my_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $type = ($row['sender_id'] == $my_id) ? "sent" : "received";
        
        // FORMAT DATE TO IST: d-m-Y h:i:s A
        $dateObj = new DateTime($row['timestamp']);
        $timeStr = $dateObj->format('d-m-Y h:i:s A');

        // IMPORTANT: Uses 'openMenu' to match your Dashboard script
        echo "<div id='msg_".$row['id']."' class='msg $type' 
                   data-enc='".$row['message']."' 
                   data-time='".$timeStr."'
                   oncontextmenu='openMenu(event, ".$row['id'].", \"$type\")'>
                   Encrypted...
              </div>"; 
    }
}

// --- 4. DELETE MESSAGE ---
if ($mode == 'delete_msg') {
    $msg_id = $_POST['msg_id'];
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ? AND sender_id = ?");
    $stmt->bind_param("ii", $msg_id, $my_id);
    $stmt->execute();
    if($stmt->affected_rows > 0) { echo "deleted"; }
}

// --- 5. PROFILE MANAGEMENT ---
if ($mode == 'update_profile') {
    $new_bio = $_POST['bio'];
    $new_phone = $_POST['phone'];
    $stmt = $conn->prepare("UPDATE users SET bio = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("ssi", $new_bio, $new_phone, $my_id);
    $stmt->execute();
}

if ($mode == 'get_my_profile') {
    $stmt = $conn->prepare("SELECT bio, phone FROM users WHERE id = ?");
    $stmt->bind_param("i", $my_id);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_assoc());
}
?>