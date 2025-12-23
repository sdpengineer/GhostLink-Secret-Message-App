<?php
session_start();
if (!isset($_SESSION['user_id'])) { 
    header("Location: index.php?page=login&error=Access Denied"); 
    exit(); 
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>GhostLink // Terminal</title>
    <link rel="icon" href="sdpGhostlink.png" type="image/png">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* BASE STYLES */
        body { background-color: #0d0d0d; color: #00ff41; font-family: 'Courier New', monospace; margin: 0; display: flex; height: 100vh; overflow: hidden; user-select: none; }
        
        /* CYAN SIDEBAR BUTTONS */
        .sb-btn { background: none; border: 1px solid #00ffff; color: #00ffff; padding: 5px 10px; cursor: pointer; text-decoration: none; margin: 0 5px; font-weight: bold; transition: 0.3s; }
        .sb-btn:hover { background: #00ffff; color: #000; box-shadow: 0 0 10px #00ffff; }

        /* TOAST NOTIFICATION */
        #toast {
            visibility: hidden; min-width: 250px; background-color: #0d0d0d; color: #00ff41;
            text-align: center; border: 1px solid #00ff41; border-radius: 4px; padding: 15px;
            position: fixed; z-index: 10000; left: 50%; bottom: 30px; transform: translateX(-50%);
            box-shadow: 0 0 15px rgba(0, 255, 65, 0.3); font-weight: bold; font-size: 14px;
        }
        #toast.show { visibility: visible; animation: fadein 0.5s, fadeout 0.5s 2.5s; }
        @keyframes fadein { from {bottom: 0; opacity: 0;} to {bottom: 30px; opacity: 1;} }
        @keyframes fadeout { from {bottom: 30px; opacity: 1;} to {bottom: 0; opacity: 0;} }

        /* LAYOUT */
        .sidebar { width: 30%; border-right: 2px solid #333; padding: 0; display: flex; flex-direction: column; background: #050505; }
        .chat-area { width: 70%; display: flex; flex-direction: column; background: #000; position: relative; }
        
        /* SIDEBAR COMPONENTS */
        .sb-header { padding: 15px; border-bottom: 1px solid #333; background: #111; }
        .search-box { width: 90%; background: #000; border: 1px solid #333; color: #00ff41; padding: 8px; font-family: inherit; }
        .user-list { flex: 1; overflow-y: auto; padding: 10px; }
        
        .user-card { padding: 12px; border: 1px solid #222; margin-bottom: 5px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: 0.2s; background: #0a0a0a; }
        .user-card:hover { background: #1a1a1a; border-color: #00ff41; }
        .status-dot { height: 8px; width: 8px; border-radius: 50%; display: inline-block; margin-right: 8px; }
        
        .sb-footer { padding: 15px; border-top: 1px solid #333; text-align: center; font-size: 12px; }

        /* CHAT COMPONENTS */
        .chat-header { padding: 15px; border-bottom: 2px solid #333; background: #111; display: flex; justify-content: space-between; align-items: center; }
        .messages { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; }
        
        .msg { max-width: 60%; padding: 10px; border-radius: 5px; font-size: 14px; word-wrap: break-word; position: relative; }
        .msg.sent { align-self: flex-end; background: #003300; border: 1px solid #00ff41; color: #ccffcc; }
        .msg.received { align-self: flex-start; background: #1a1a1a; border: 1px solid #555; color: #aaa; }
        
        /* IMAGE & FILE LINKS */
        .file-link { color: #00ffff; text-decoration: underline; cursor: pointer; display:block; margin-top:5px; }
        .chat-img { max-width: 100%; border: 1px solid #00ff41; margin-top: 5px; cursor: pointer; display: block; }

        .msg-time {
            display: block; font-size: 10px; margin-top: 5px; text-align: right; opacity: 0.7;
            font-family: 'Courier New', monospace; border-top: 1px solid rgba(0, 255, 65, 0.2); padding-top: 2px;
        }
        .msg.sent .msg-time { color: #00ff41; }
        .msg.received .msg-time { color: #888; }

        /* INPUT AREA */
        .input-zone { padding: 20px; border-top: 2px solid #333; background: #111; display: flex; gap: 10px; align-items: center; }
        input[type="text"], input[type="password"] { background: #000; border: 1px solid #333; color: #00ff41; padding: 10px; font-family: inherit; }
        #secretKey { width: 100px; border-color: #ff3333; color: #ff3333; text-align:center; }
        #secretKey:focus { border-color: #00ff41; color: #00ff41; }

        button.send-btn { background: #00ff41; color: #000; border: none; padding: 10px 20px; cursor: pointer; font-weight: bold; }
        
        /* ATTACHMENT BUTTON */
        .attach-btn { cursor: pointer; background: #222; border: 1px solid #555; color: #fff; padding: 10px; }
        .attach-btn:hover { border-color: #00ff41; color: #00ff41; }

        .hidden { display: none !important; }

        /* CONTEXT MENU */
        #contextMenu { position: absolute; background: #111; border: 1px solid #00ff41; z-index: 9999; padding: 5px 0; display: none; width: 150px; box-shadow: 0 0 10px #000; }
        #contextMenu div { padding: 10px 15px; cursor: pointer; font-size: 12px; color: #fff; }
        #contextMenu div:hover { background: #00ff41; color: #000; }

        /* MODAL */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 100; display: flex; justify-content: center; align-items: center; }
        .modal-box { background: #111; border: 2px solid #00ff41; padding: 20px; width: 300px; text-align: center; box-shadow: 0 0 20px #003300; }

        @media (max-width: 768px) {
            .sidebar { display: none; width: 100%; height: 100%; position: absolute; z-index: 20; }
            .sidebar.active { display: flex; }
            .chat-area { width: 100%; }
            .msg { max-width: 85%; }
            #secretKey { width: 60px; font-size: 10px; }
        }
    </style>
</head>
<body onclick="hideMenu()">

    <div id="toast"></div>
    <audio id="notifySound" src="https://actions.google.com/sounds/v1/alarms/beep_short.ogg"></audio>
    <input type="file" id="fileInput" style="display: none;" onchange="uploadFile()">

    <div class="sidebar" id="sidebar">
        <div class="sb-header">
            <h3>// AGENTS <span style="float:right; cursor:pointer; font-size:12px; color:#555;" onclick="toggleSidebar()">[HIDE]</span></h3>
            <input type="text" id="searchUser" class="search-box" placeholder="Search Agent / Phone..." onkeyup="fetchUsers()">
        </div>
        <div id="userList" class="user-list">
            <div style="text-align:center; padding:20px; color:#444;">Scanning Network...</div>
        </div>
        <div class="sb-footer">
            <a href="#" onclick="showProfile()" class="sb-btn">IDENTITY</a>
            <a href="index.php?page=usage" class="sb-btn">USAGE</a>
            <a href="index.php?page=about" class="sb-btn">ABOUT</a>
            <a href="logout.php" class="sb-btn" style="border-color:#ff3333; color:#ff3333;">ABORT</a>
        </div>
    </div>

    <div class="chat-area">
        <div class="chat-header">
            <span onclick="toggleSidebar()" style="cursor:pointer; margin-right:10px;">[MENU]</span>
            <span id="chatTitle">// SELECT A TARGET</span>
            <input type="password" id="secretKey" placeholder="KEY" title="Both users must use same key">
        </div>
        
        <div class="messages" id="msgBox">
            <div style="text-align:center; margin-top:50px; color:#333;">
                <img src="sdpGhostlink.png" width="64" style="opacity:0.2; margin-bottom:10px;"><br>
                ENCRYPTED UPLINK STANDBY...
            </div>
        </div>

        <div class="input-zone hidden" id="inputZone">
            <button class="attach-btn" onclick="document.getElementById('fileInput').click()">📎</button>
            <input type="text" id="msgInput" style="flex:1" placeholder="Type encrypted message...">
            <button class="send-btn" onclick="sendMessage()">SEND</button>
        </div>
        
        <div id="contextMenu">
            <div onclick="confirmDelete()">DELETE MESSAGE</div>
        </div>
    </div>

    <div id="profileModal" class="modal-overlay hidden">
        <div class="modal-box">
            <h3 style="margin-top:0;">EDIT IDENTITY</h3>
            <input type="text" id="editBio" placeholder="Update Bio" style="width: 90%; margin-bottom: 10px;"><br>
            <input type="text" id="editPhone" placeholder="Update Phone" style="width: 90%; margin-bottom: 10px;"><br><br>
            <button class="send-btn" onclick="saveProfile()">SAVE CHANGES</button>
            <button class="sb-btn" onclick="document.getElementById('profileModal').classList.add('hidden')">CANCEL</button>
        </div>
    </div>

    <script>
        let currentTargetId = null;
        let refreshInterval = null;
        let selectedMsgId = null;
        let lastMsgCount = 0; 

        function showToast(text) {
            var x = document.getElementById("toast");
            x.innerText = text;
            x.className = "show";
            setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
        }

        function checkResponse(data) {
            if(data.trim() === "ACCESS DENIED") { window.location = "index.php?page=login&error=Session Expired"; return false; }
            return true;
        }

        // --- INIT ---
        fetchUsers();
        setInterval(fetchUsers, 5000); 

        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('active'); }

        // --- CORE FUNCTIONS ---
        function fetchUsers() {
            let searchVal = document.getElementById('searchUser').value;
            let formData = new FormData();
            formData.append('mode', 'get_users');
            formData.append('search', searchVal);
            fetch('engine.php', { method: 'POST', body: formData })
            .then(res => res.text())
            .then(data => { if(checkResponse(data)) document.getElementById('userList').innerHTML = data; });
        }

        window.openChat = function(userId, username) {
            currentTargetId = userId;
            document.getElementById('chatTitle').innerHTML = "// " + username;
            document.getElementById('inputZone').classList.remove('hidden');
            document.getElementById('msgBox').innerHTML = "<div style='text-align:center; color:#555; padding-top:50px;'>Establishing Uplink...</div>";
            document.getElementById('sidebar').classList.remove('active');

            if(refreshInterval) clearInterval(refreshInterval);
            lastMsgCount = 0; 
            fetchMessages();
            refreshInterval = setInterval(fetchMessages, 3000); 
        }

        window.sendMessage = function() {
            let text = document.getElementById('msgInput').value;
            let key = document.getElementById('secretKey').value;
            
            if(text.trim() === "") return;
            if(key.trim() === "") { showToast("⚠️ SET SECRET KEY FIRST"); document.getElementById('secretKey').focus(); return; }

            let encrypted = CryptoJS.AES.encrypt(text, key).toString();
            let formData = new FormData();
            formData.append('mode', 'send_msg');
            formData.append('target', currentTargetId);
            formData.append('msg', encrypted);

            fetch('engine.php', { method: 'POST', body: formData })
            .then(res => res.text())
            .then(data => {
                if(checkResponse(data)) {
                    document.getElementById('msgInput').value = "";
                    fetchMessages(); 
                }
            });
        }

        // --- UPLOAD LOGIC ---
        window.uploadFile = function() {
            let fileInput = document.getElementById('fileInput');
            if(fileInput.files.length === 0) return;
            
            // Client-side size check (25MB)
            if(fileInput.files[0].size > 25000000) {
                showToast("❌ File too large (Max 25MB)");
                return;
            }

            let formData = new FormData();
            formData.append("file", fileInput.files[0]);

            document.getElementById('msgInput').value = "[UPLOADING...]";

            fetch('upload.php', { method: "POST", body: formData })
            .then(res => res.text())
            .then(data => {
                let cleanData = data.trim();
                if(cleanData.startsWith("error:")) {
                    showToast("❌ " + cleanData);
                    document.getElementById('msgInput').value = "";
                } else {
                    // Send file link as message
                    document.getElementById('msgInput').value = "FILE:" + cleanData;
                    sendMessage(); 
                }
            });
        }

        function fetchMessages() {
            if(!currentTargetId) return;
            let key = document.getElementById('secretKey').value;
            let formData = new FormData();
            formData.append('mode', 'get_msgs');
            formData.append('target', currentTargetId);

            fetch('engine.php', { method: 'POST', body: formData })
            .then(res => res.text())
            .then(html => {
                if(!checkResponse(html)) return;

                let tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                let newCount = tempDiv.childElementCount;

                if(lastMsgCount > 0 && newCount > lastMsgCount) {
                    let lastMsg = tempDiv.lastElementChild;
                    if(lastMsg && lastMsg.classList.contains('received')) {
                        document.getElementById('notifySound').play().catch(e => {});
                    }
                }
                lastMsgCount = newCount; 

                let messages = tempDiv.querySelectorAll('.msg');
                messages.forEach(msg => {
                    let encText = msg.getAttribute('data-enc');
                    let timeStamp = msg.getAttribute('data-time');
                    let plainText = "[LOCKED]";
                    
                    if(key.trim() !== "") {
                        try {
                            let bytes = CryptoJS.AES.decrypt(encText, key);
                            plainText = bytes.toString(CryptoJS.enc.Utf8);
                            if(!plainText) plainText = "Wrong Key 🛑";
                        } catch(e) { plainText = "Decryption Error"; }
                    } else { plainText = "🔒 Enter Key"; }

                    // --- ROBUST FILE PARSING ---
                    let cleanText = plainText.trim(); // IMPORTANT: This fixes the receiver view
                    if(cleanText.startsWith("FILE:")) {
                        let fileSrc = cleanText.substring(5).trim(); 
                        let ext = fileSrc.split('.').pop().toLowerCase();
                        
                        // IF IMAGE: Show preview with error fallback
                        if(['jpg','jpeg','png','gif'].includes(ext)) {
                            msg.innerHTML = `<img src='${fileSrc}' class='chat-img' 
                                            onclick='window.open(this.src)' 
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            <a href='${fileSrc}' target='_blank' class='file-link' style='display:none;'>⚠️ IMG LOAD FAILED (CLICK TO VIEW)</a>`;
                        } else {
                            msg.innerHTML = `<a href='${fileSrc}' target='_blank' class='file-link'>📎 DOWNLOAD ${ext.toUpperCase()} FILE</a> <br><span style='font-size:10px; color:#ff3333;'>(Auto-Deletes in 5m)</span>`;
                        }
                    } else {
                        msg.innerHTML = cleanText;
                    }
                    // Add Timestamp
                    msg.innerHTML += "<span class='msg-time'>[" + timeStamp + "]</span>";
                });

                if(document.getElementById('msgBox').childElementCount !== tempDiv.childElementCount) {
                    document.getElementById('msgBox').innerHTML = tempDiv.innerHTML;
                    let chatBox = document.getElementById('msgBox');
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            });
        }

        // --- CONTEXT MENU (RIGHT CLICK) FIX ---
        window.openMenu = function(e, id, type) {
            e.preventDefault(); 
            if(type !== 'sent') { showToast("Cannot delete received msg"); return; } 
            
            selectedMsgId = id;
            let menu = document.getElementById('contextMenu');
            menu.style.display = 'block';
            
            // Position Menu
            let x = e.clientX; 
            let y = e.clientY;
            
            // Adjust if out of screen
            if (x + 150 > window.innerWidth) x -= 150;
            if (y + 50 > window.innerHeight) y -= 50;

            menu.style.left = x + 'px';
            menu.style.top = y + 'px';
        }

        function hideMenu() {
            document.getElementById('contextMenu').style.display = 'none';
        }

        window.confirmDelete = function() {
            if(!selectedMsgId) return;
            if(confirm("Permanently erase data?")) {
                let formData = new FormData();
                formData.append('mode', 'delete_msg');
                formData.append('msg_id', selectedMsgId);
                fetch('engine.php', { method: 'POST', body: formData })
                .then(res => res.text())
                .then(data => { 
                    if(data.includes("deleted")) { 
                        showToast("🗑️ MESSAGE DELETED");
                        let el = document.getElementById('msg_' + selectedMsgId);
                        if(el) el.remove(); 
                    } 
                });
            }
            hideMenu();
        }

        // --- PROFILE ---
        window.showProfile = function() {
            document.getElementById('profileModal').classList.remove('hidden');
            let formData = new FormData();
            formData.append('mode', 'get_my_profile');
            fetch('engine.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => { 
                document.getElementById('editBio').value = data.bio; 
                document.getElementById('editPhone').value = data.phone; 
            });
        }
        window.saveProfile = function() {
            let bio = document.getElementById('editBio').value;
            let phone = document.getElementById('editPhone').value;
            let formData = new FormData();
            formData.append('mode', 'update_profile');
            formData.append('bio', bio);
            formData.append('phone', phone);
            fetch('engine.php', { method: 'POST', body: formData })
            .then(() => { 
                document.getElementById('profileModal').classList.add('hidden'); 
                showToast("✅ IDENTITY UPDATED"); 
            });
        }

        document.getElementById("msgInput").addEventListener("keypress", function(event) {
            if (event.key === "Enter") sendMessage();
        });
    </script>
</body>
</html>