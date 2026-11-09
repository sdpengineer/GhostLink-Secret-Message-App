<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login&error=Access%20Denied');
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta name="theme-color" content="#090d17">
    <title>Messages — GhostLink</title>
    <link rel="icon" href="sdpGhostlink.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    <style>
        :root{color-scheme:dark;--bg:#090d17;--text:#f2f5fb;--muted:#98a3b6;--line:#ffffff1a;--accent:#83ecc4;--blue:#91a9ff;--danger:#ff9da7}
        *{box-sizing:border-box}html,body{height:100%;margin:0}body{overflow:hidden;color:var(--text);font:14px 'DM Sans',sans-serif;background:radial-gradient(ellipse at 5% 0,#4f74ff28,transparent 36%),radial-gradient(ellipse at 100% 100%,#52deb81c,transparent 35%),var(--bg)}
        button,input{font:inherit}.app{height:100dvh;padding:16px;display:grid;grid-template-columns:310px minmax(0,1fr);gap:14px}.glass{background:linear-gradient(145deg,#ffffff12,#ffffff06);border:1px solid var(--line);backdrop-filter:blur(20px);box-shadow:0 20px 70px #0005}.sidebar,.chat{min-height:0;border-radius:22px;overflow:hidden}.sidebar{display:flex;flex-direction:column;position:relative}.side-head{padding:21px;border-bottom:1px solid var(--line)}.brand{display:flex;align-items:center;gap:10px;color:var(--text);text-decoration:none;font:700 1.2rem 'Space Grotesk'}.brand img{width:34px;height:34px;object-fit:contain}.brand span{color:var(--accent)}.subline{color:var(--muted);margin:10px 0 16px}.search,.key-input,.message-input{width:100%;padding:11px 13px;border:1px solid var(--line);border-radius:12px;background:#080d18;color:var(--text);outline:none}.list-label{padding:16px 18px 8px;font-size:.68rem;letter-spacing:.13em;text-transform:uppercase;color:var(--muted)}.user-list{flex:1;overflow:auto;padding:6px 9px}.user-card{display:flex;align-items:center;gap:10px;width:100%;margin:4px 0;padding:11px;border:1px solid transparent;border-radius:14px;text-align:left;background:transparent;color:var(--text);cursor:pointer}.user-card:hover,.user-card.active{background:#ffffff0d;border-color:var(--line)}.avatar{width:39px;height:39px;border-radius:13px;display:grid;place-items:center;flex:none;color:#07130f;font-weight:800;background:linear-gradient(135deg,var(--accent),var(--blue))}.contact-meta{display:grid;min-width:0;flex:1;gap:4px}.contact-meta b,.contact-meta small{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.contact-meta small{font-size:.72rem;color:var(--muted)}.presence-dot{width:8px;height:8px;border-radius:50%;background:#687386;flex:none}.presence-dot.online{background:#7ef0ad}.unread-badge{border-radius:99px;background:var(--accent);color:#07130f;font-weight:800;padding:2px 6px;font-size:.68rem}.side-foot{display:flex;gap:5px;flex-wrap:wrap;padding:12px;border-top:1px solid var(--line)}.side-link{background:transparent;color:var(--muted);border:0;padding:8px;cursor:pointer;text-decoration:none;font-size:.76rem}.abort{color:var(--danger)}
        .chat{display:flex;flex-direction:column;min-width:0}.chat-head{min-height:76px;display:flex;justify-content:space-between;align-items:center;gap:10px;padding:12px 18px;border-bottom:1px solid var(--line)}.head-left,.head-actions{display:flex;align-items:center;gap:10px;min-width:0}.target{font:600 1rem 'Space Grotesk';white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.target-sub{font-size:.75rem;color:var(--muted);margin-top:4px}.key-input{width:150px}.icon-btn,.send,.sidebar-close{border:1px solid var(--line);border-radius:11px;background:#ffffff0b;color:var(--text);padding:9px 11px;cursor:pointer}.toolbar{display:flex;gap:8px;align-items:center;padding:9px 16px;border-bottom:1px solid var(--line)}.chat-search{flex:1;padding:8px 11px}.messages{flex:1;min-height:0;overflow:auto;display:flex;flex-direction:column;gap:10px;padding:18px clamp(14px,4vw,48px)}.msg{max-width:min(74%,650px);padding:12px 14px;border:1px solid var(--line);border-radius:17px;background:#ffffff0b;line-height:1.55;white-space:pre-wrap;overflow-wrap:anywhere}.msg.sent{align-self:flex-end;background:linear-gradient(135deg,#83ecc424,#91a9ff1e);border-bottom-right-radius:4px}.msg.received{align-self:flex-start;border-bottom-left-radius:4px}.msg-time{display:block;color:var(--muted);font-size:.65rem;text-align:right;margin-top:6px}.input-zone{display:flex;align-items:center;gap:8px;padding:12px 15px calc(12px + env(safe-area-inset-bottom));border-top:1px solid var(--line);background:#090d17ed}.message-input{flex:1;min-width:0}.send{background:linear-gradient(135deg,var(--accent),var(--blue));color:#08120e;border:0;font-weight:700}.empty{margin:auto;text-align:center;padding:20px;color:var(--muted);line-height:1.7}.hidden{display:none!important}.toast{position:fixed;z-index:50;bottom:22px;left:50%;transform:translate(-50%,12px);opacity:0;background:#182136;border:1px solid var(--line);padding:11px 16px;border-radius:12px;transition:.2s;pointer-events:none;max-width:90vw}.toast.show{opacity:1;transform:translate(-50%,0)}.sidebar-close,.mobile-menu{display:none}
        @media(max-width:850px){.app{grid-template-columns:270px minmax(0,1fr);padding:10px;gap:10px}.key-input{width:115px}}
        @media(max-width:650px){.app{display:block;padding:0}.chat{height:100dvh;border:0;border-radius:0}.sidebar{display:none;position:fixed;z-index:30;inset:8px auto 8px 8px;width:min(330px,calc(100vw - 16px));background:#101827f5}.sidebar.active{display:flex}.sidebar-close,.mobile-menu{display:inline-grid;place-items:center}.sidebar-close{position:absolute;right:12px;top:12px;width:40px;height:40px;font-size:1.45rem;z-index:2}.side-head{padding-right:60px}.mobile-menu{width:42px;height:42px;flex:none}.chat-head{padding:9px}.head-actions{gap:5px}.key-input{width:105px;padding:9px}.toolbar{padding:8px}.messages{padding:14px}.msg{max-width:90%}.input-zone{gap:5px;padding:9px 8px calc(9px + env(safe-area-inset-bottom))}.send{padding:10px}}
        @media(prefers-reduced-motion:reduce){*,*::before,*::after{scroll-behavior:auto!important;animation:none!important;transition:none!important}}
    </style>
</head>
<body>
<div class="app">
    <aside class="sidebar glass" id="sidebar" aria-label="Contacts">
        <div class="side-head">
            <button type="button" class="sidebar-close" id="closeSidebar" aria-label="Close contacts">×</button>
            <a href="index.php" class="brand"><img src="sdpGhostlink.png" alt=""><span>GhostLink</span></a>
            <p class="subline">Private conversations, made personal.</p>
            <input id="searchUser" class="search" type="search" placeholder="Search people…" aria-label="Search people">
        </div>
        <div class="list-label">Contacts</div>
        <div id="userList" class="user-list"><div class="empty">Finding people…</div></div>
        <div class="side-foot"><a href="index.php?page=about" class="side-link">About</a><a href="logout.php" class="side-link abort">Sign out</a></div>
    </aside>
    <main class="chat glass">
        <header class="chat-head">
            <div class="head-left"><button type="button" class="icon-btn mobile-menu" id="openSidebar" aria-label="Open contacts" aria-expanded="false">☰</button><div><div class="target" id="chatTitle">Choose someone to chat</div><div class="target-sub" id="chatSubtitle">Select a contact to begin</div></div></div>
            <div class="head-actions"><input id="secretKey" class="key-input" type="password" placeholder="Shared key" autocomplete="off" aria-label="Shared encryption key"></div>
        </header>
        <div class="toolbar"><input id="chatSearch" class="search chat-search" type="search" placeholder="Search this conversation…" aria-label="Search messages"></div>
        <section class="messages" id="msgBox" aria-live="polite"><div class="empty">Select a person to start a conversation.</div></section>
        <form class="input-zone hidden" id="inputZone"><input type="text" id="msgInput" class="message-input" placeholder="Write a message…" autocomplete="off" aria-label="Message"><button class="send" type="submit" id="sendButton">Send ↗</button></form>
    </main>
</div>
<div class="toast" id="toast" role="status" aria-live="polite"></div>
<script>
'use strict';
const $=id=>document.getElementById(id);
let peer=null, peerName='', messageTimer=null, userTimer=null;
function toast(text){const node=$('toast');node.textContent=text;node.classList.add('show');setTimeout(()=>node.classList.remove('show'),3500)}
function toggleSidebar(open){const sidebar=$('sidebar'),show=typeof open==='boolean'?open:!sidebar.classList.contains('active');sidebar.classList.toggle('active',show);$('openSidebar').setAttribute('aria-expanded',String(show))}
async function post(data){const form=new FormData();for(const [key,value] of Object.entries(data))form.append(key,value);const response=await fetch('engine.php',{method:'POST',body:form,credentials:'same-origin'});return response.text()}
function checkAuth(text){if(text.trim()==='ACCESS DENIED'){location.href='index.php?page=login&error=Session%20Expired';return false}return true}
async function fetchUsers(){try{const html=await post({mode:'get_users',search:$('searchUser').value});if(!checkAuth(html))return;$('userList').innerHTML=html;$('userList').querySelectorAll('.user-card').forEach(button=>{button.classList.toggle('active',button.dataset.userId===peer);button.onclick=()=>openChat(button.dataset.userId,button.dataset.username)})}catch(error){toast('Could not load contacts. Check the server output.')}}
function openChat(id,name){peer=String(id);peerName=name||'Contact';$('chatTitle').textContent=peerName;$('chatSubtitle').textContent='Messages are encrypted in your browser';$('inputZone').classList.remove('hidden');$('msgBox').innerHTML='<div class="empty">Loading conversation…</div>';toggleSidebar(false);if(messageTimer)clearInterval(messageTimer);fetchMessages();messageTimer=setInterval(fetchMessages,3000);fetchUsers()}
function renderMessages(html){const temp=document.createElement('div');temp.innerHTML=html;const rows=[...temp.querySelectorAll('.msg')],box=$('msgBox'),nearBottom=box.scrollHeight-box.scrollTop-box.clientHeight<100;box.replaceChildren();if(!rows.length){box.innerHTML='<div class="empty">No messages yet. Send the first one.</div>';return}for(const row of rows){const message=document.createElement('article');message.id=row.id;message.className=row.classList.contains('sent')?'msg sent':'msg received';const encrypted=row.dataset.enc||'';let plain='';if($('secretKey').value&&typeof CryptoJS!=='undefined'){try{plain=CryptoJS.AES.decrypt(encrypted,$('secretKey').value).toString(CryptoJS.enc.Utf8)}catch(error){}}
 if(!plain)plain=$('secretKey').value?'Unable to decrypt. Check the shared key.':'Enter your shared key to read this message.';
 if(plain.startsWith('FILE:')){const path=plain.slice(5).trim();if(/^uploads\/[\w.-]+$/.test(path)){const link=document.createElement('a');link.href=path;link.target='_blank';link.rel='noopener noreferrer';link.textContent='Open attachment';message.append(link)}else message.textContent='Attachment unavailable'}else message.append(document.createTextNode(plain));if(row.dataset.time){const time=document.createElement('small');time.className='msg-time';time.textContent=row.dataset.time;message.append(time)}box.append(message)}if(nearBottom)box.scrollTop=box.scrollHeight}
async function fetchMessages(){if(!peer)return;try{const html=await post({mode:'get_msgs',target:peer});if(checkAuth(html))renderMessages(html)}catch(error){toast('Could not load this conversation.')}}
async function sendMessage(){const text=$('msgInput').value,key=$('secretKey').value;if(!peer)return toast('Choose a contact first.');if(!text.trim())return;if(!key.trim())return toast('Enter your shared key first.');if(typeof CryptoJS==='undefined')return toast('Encryption library did not load. Check your internet connection.');$('sendButton').disabled=true;try{const encrypted=CryptoJS.AES.encrypt(text,key).toString(),result=await post({mode:'send_msg',target:peer,msg:encrypted,view_once:0,attachment_path:''});if(!checkAuth(result))return;if(result.trim()==='sent'){$('msgInput').value='';await fetchMessages()}else toast(result.trim()||'Message could not be sent.')}catch(error){toast('Message could not be sent.')}finally{$('sendButton').disabled=false;$('msgInput').focus()}}
$('openSidebar').addEventListener('click',()=>toggleSidebar());$('closeSidebar').addEventListener('click',()=>toggleSidebar(false));$('searchUser').addEventListener('input',fetchUsers);$('inputZone').addEventListener('submit',event=>{event.preventDefault();sendMessage()});$('secretKey').addEventListener('input',fetchMessages);$('chatSearch').addEventListener('input',()=>{const query=$('chatSearch').value.toLocaleLowerCase();document.querySelectorAll('#msgBox .msg').forEach(message=>{message.classList.toggle('hidden',!!query&&!message.textContent.toLocaleLowerCase().includes(query))})});window.addEventListener('keydown',event=>{if(event.key==='Escape')toggleSidebar(false)});fetchUsers();userTimer=setInterval(fetchUsers,7000);
</script>
</body>
</html>
