<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login&error=Access%20Denied');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#090d17">
    <title>Messages — GhostLink</title>
    <link rel="icon" href="sdpGhostlink.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    <style>
        :root{color-scheme:dark;--bg:#090d17;--bg-2:#111827;--panel:rgba(16,19,30,.8);--line:rgba(255,255,255,.1);--text:#f3f6fb;--muted:#96a2b8;--accent:#8af0ca;--accent2:#8ca9ff;--success:#7ef0ad;--danger:#ff9ca9;--warning:#ffd68a;--radius:24px}
        *{box-sizing:border-box}html,body{width:100%;height:100%;margin:0}body{overflow:hidden;color:var(--text);font:14px 'DM Sans',sans-serif;background:radial-gradient(ellipse at 10% 0,rgba(84,113,255,.18),transparent 32%),radial-gradient(ellipse at 100% 100%,rgba(87,238,200,.12),transparent 30%),var(--bg);transition:background .25s,color .2s}.button,input,select,button{font:inherit}button{color:inherit}.app{height:100vh;height:100dvh;padding:18px;display:grid;grid-template-columns:330px minmax(0,1fr);gap:16px;position:relative;z-index:1}.glass{background:linear-gradient(145deg,rgba(255,255,255,.09),rgba(255,255,255,.025));border:1px solid var(--line);backdrop-filter:blur(22px);-webkit-backdrop-filter:blur(22px);box-shadow:0 22px 80px rgba(0,0,0,.28)}.sidebar,.chat{min-height:0;border-radius:var(--radius);overflow:hidden}.sidebar{display:flex;flex-direction:column}.side-head{padding:22px 20px 17px;border-bottom:1px solid var(--line)}.brand{font:700 1.18rem 'Space Grotesk',sans-serif;display:flex;align-items:center;gap:10px;color:var(--text);text-decoration:none}.brand img{width:34px;height:34px;object-fit:contain}.brand span{color:var(--accent)}.subline{margin:8px 0 16px;color:var(--muted);font-size:.84rem}.search{width:100%;padding:12px 14px;border:1px solid var(--line);border-radius:13px;background:rgba(4,8,17,.38);color:var(--text);outline:none}.search:focus,.input:focus,.key-input:focus,.modal input:focus,.modal select:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(138,240,202,.18)}.list-label{padding:17px 20px 8px;color:var(--muted);text-transform:uppercase;letter-spacing:.13em;font-size:.67rem;font-weight:700}.user-list{flex:1;overflow:auto;padding:5px 10px 12px}.user-card{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:4px 0;padding:12px 11px;border:1px solid transparent;border-radius:15px;color:var(--text);cursor:pointer;transition:all .18s ease;position:relative}.user-card:hover{background:rgba(255,255,255,.055);border-color:var(--line);transform:translateX(2px)}.user-card.active{background:linear-gradient(135deg,rgba(138,240,202,.12),rgba(140,169,255,.08));border-color:rgba(138,240,202,.22);box-shadow:inset 0 0 0 1px rgba(138,240,202,.18)}.user-main{display:flex;align-items:center;min-width:0;flex:1;gap:10px}.avatar{width:40px;height:40px;border-radius:12px;display:grid;place-items:center;font-weight:700;color:#091521;background:linear-gradient(135deg,var(--accent),var(--accent2));box-shadow:0 10px 24px rgba(130,168,255,.2);flex-shrink:0}.user-meta{min-width:0;flex:1}.user-name-row{display:flex;align-items:center;gap:7px;min-width:0}.user-name{font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.status-dot{width:8px;height:8px;border-radius:50%;display:inline-block;box-shadow:0 0 8px currentColor}.status-online{color:var(--success);background:var(--success)}.status-offline{color:#7a8596;background:#7a8596}.user-bio{margin-top:5px;color:var(--muted);font-size:.72rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.user-state{display:flex;align-items:center;gap:6px;font-size:.62rem;color:var(--muted);letter-spacing:.06em;text-transform:uppercase}.unread-badge{min-width:18px;height:18px;padding:0 6px;border-radius:999px;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#061019;font-size:.66rem;font-weight:800;display:grid;place-items:center}.side-foot{display:flex;align-items:center;gap:5px;flex-wrap:wrap;padding:13px;border-top:1px solid var(--line)}.side-link{padding:9px 10px;border:1px solid transparent;border-radius:10px;background:transparent;color:var(--muted);font-size:.78rem;text-decoration:none;cursor:pointer}.side-link:hover{background:rgba(255,255,255,.06);color:var(--text)}.abort{color:#ffabb2}.chat{display:flex;flex-direction:column;position:relative;min-width:0}.chat-head{min-height:78px;display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px 20px;border-bottom:1px solid var(--line);background:rgba(8,12,22,.2);flex-shrink:0}.head-left,.head-actions{display:flex;align-items:center;gap:12px;min-width:0}.head-actions{justify-content:flex-end;flex-wrap:wrap}.target{font:600 1rem 'Space Grotesk',sans-serif;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:34vw}.target-sub{margin-top:4px;color:var(--muted);font-size:.76rem}.key-wrap{display:flex;align-items:center;gap:8px;color:var(--muted);font-size:.74rem}.key-input{width:155px;padding:9px 11px;border:1px solid var(--line);border-radius:11px;background:rgba(4,8,17,.42);color:var(--text);outline:none}.icon-btn,.attach,.send,.settings-trigger{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:40px;padding:9px 12px;border:1px solid var(--line);border-radius:12px;background:rgba(255,255,255,.05);color:var(--text);cursor:pointer;transition:transform .18s,border-color .18s,background .18s}.icon-btn:hover,.attach:hover,.settings-trigger:hover{transform:translateY(-1px);border-color:var(--accent);background:rgba(255,255,255,.08)}.messages{flex:1;min-height:0;overflow-y:auto;overscroll-behavior:contain;display:flex;flex-direction:column;gap:11px;padding:24px clamp(16px,4vw,54px);scroll-behavior:smooth;position:relative}.messages::before{content:"";position:sticky;top:0;left:0;display:block;height:0}.msg{max-width:min(72%,650px);padding:12px 15px;border:1px solid var(--line);border-radius:17px;line-height:1.55;white-space:pre-wrap;overflow-wrap:anywhere;animation:message-in .18s ease-out}.msg.sent{align-self:flex-end;background:linear-gradient(135deg,color-mix(in srgb,var(--accent) 22%,#1f2b3d),#1a2336);border-bottom-right-radius:5px}.msg.received{align-self:flex-start;background:rgba(255,255,255,.065);border-bottom-left-radius:5px}.msg-time{display:block;margin-top:6px;color:var(--muted);font-size:.66rem}.chat-img{display:block;max-width:min(360px,100%);max-height:300px;border-radius:10px;cursor:pointer}.file-link{color:var(--accent);text-decoration:none}.empty{margin:auto;text-align:center;padding:25px;color:var(--muted);line-height:1.7}.empty img{width:64px;height:64px;object-fit:contain;opacity:.65}.typing{display:flex;align-self:flex-start;gap:5px;padding:10px 12px;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,.04);color:var(--muted);max-width:120px}.typing span{display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--accent);animation:typing 1.1s infinite ease-in-out}.typing span:nth-child(2){animation-delay:.15s}.typing span:nth-child(3){animation-delay:.3s}.input-zone{position:sticky;bottom:0;z-index:2;display:flex;align-items:center;gap:10px;padding:14px 18px calc(14px + env(safe-area-inset-bottom));border-top:1px solid var(--line);background:rgba(9,13,23,.9);backdrop-filter:blur(18px);flex-shrink:0}.input-zone.hidden{display:none}.input{flex:1;min-width:0;padding:13px 15px;border:1px solid var(--line);border-radius:14px;background:rgba(4,8,17,.48);color:var(--text);outline:none}.send{border:0;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#081019;font-weight:700;padding:11px 17px}.send:hover{filter:brightness(1.08);transform:translateY(-1px)}.mobile-menu{display:none}#toast{position:fixed;z-index:50;bottom:25px;left:50%;transform:translate(-50%,12px);opacity:0;pointer-events:none;padding:12px 17px;border-radius:12px;background:#161d2ef2;border:1px solid var(--line);box-shadow:0 12px 40px #0005;transition:.2s}#toast.show{opacity:1;transform:translate(-50%,0)}#contextMenu{position:fixed;z-index:60;display:none;padding:6px;border-radius:12px;background:#161d2ef5;border:1px solid var(--line);backdrop-filter:blur(15px);box-shadow:0 12px 40px #0007}#contextMenu button{padding:10px 14px;border:0;border-radius:8px;background:transparent;cursor:pointer;color:#ffb5bd}.modal-overlay{position:fixed;z-index:40;inset:0;display:grid;place-items:center;padding:20px;background:rgba(2,5,12,.72);backdrop-filter:blur(12px)}.modal-overlay.hidden{display:none}.modal{width:min(480px,100%);max-height:90dvh;overflow:auto;padding:25px;border-radius:22px}.modal h2{margin:0 0 7px;font:600 1.4rem 'Space Grotesk',sans-serif}.modal-copy{margin:0 0 18px;color:var(--muted);line-height:1.5}.modal input,.modal select{width:100%;padding:11px 12px;margin:6px 0;border:1px solid var(--line);border-radius:11px;background:#101728;color:var(--text);outline:none}.modal-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:18px}.setting-row{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:13px 0;border-top:1px solid var(--line)}.setting-row label{color:var(--muted)}.setting-row select{width:auto;min-width:150px;margin:0}.setting-row input[type=checkbox]{width:18px;height:18px;accent-color:var(--accent)}.theme-swatches{display:flex;gap:9px}.swatch{width:28px;height:28px;border:2px solid transparent;border-radius:50%;cursor:pointer}.swatch[aria-pressed=true]{outline:2px solid var(--text);outline-offset:2px}.swatch[data-value=glass]{background:linear-gradient(135deg,#83ecc4,#91a9ff)}.swatch[data-value=aurora]{background:linear-gradient(135deg,#65fbd2,#b59aff)}.swatch[data-value=midnight]{background:linear-gradient(135deg,#c2a8ff,#75bcff)}.swatch[data-value=rose]{background:linear-gradient(135deg,#ffb4d2,#c6a0ff)}body[data-theme=aurora]{--bg:#071218;--accent:#65fbd2;--accent2:#b59aff}body[data-theme=midnight]{--bg:#080a13;--accent:#c2a8ff;--accent2:#75bcff}body[data-theme=rose]{--bg:#140b15;--accent:#ffb4d2;--accent2:#c6a0ff}body[data-density=compact] .messages{gap:7px;padding-top:16px;padding-bottom:16px}body[data-density=compact] .msg{padding:9px 12px}body[data-font-size=large]{font-size:16px}.chat-head,.input-zone{flex-shrink:0}
@media(max-width:900px){.app{grid-template-columns:270px minmax(0,1fr);padding:12px;gap:12px}.chat-head{padding:12px}.key-label{display:none}.key-input{width:125px}.settings-trigger{padding:9px}.settings-trigger .button-label{display:none}}
@media(max-width:680px){.app{display:block;padding:0}.sidebar{position:fixed;z-index:20;inset:10px auto 10px 10px;width:min(330px,calc(100vw - 20px));display:none;background:rgba(14,20,34,.97)}.sidebar.active{display:flex;animation:drawer-in .2s ease-out}.chat{height:100%;height:100dvh;border:0;border-radius:0}.mobile-menu{display:inline-flex}.chat-head{min-height:70px;padding:10px 12px;gap:6px}.head-actions{gap:5px}.key-input{width:100px;padding:8px}.icon-btn,.settings-trigger{min-height:38px;padding:8px 10px}.messages{padding:17px 14px}.msg{max-width:88%}.input-zone{padding:10px 10px calc(10px + env(safe-area-inset-bottom));gap:7px}.send{padding:10px 13px}.attach{padding:8px 11px}.target{max-width:35vw}.target-sub{font-size:.68rem}}
@media(max-width:390px){.target{font-size:.9rem}.target-sub{font-size:.63rem}.key-input{width:82px}.head-actions{gap:3px}.settings-trigger{padding:8px}.input-zone{gap:5px}.msg{max-width:92%}}
@keyframes message-in{from{opacity:.55;transform:translateY(5px)}to{opacity:1;transform:translateY(0)}}@keyframes drawer-in{from{opacity:0;transform:translateX(-8px)}to{opacity:1;transform:translateX(0)}}@keyframes typing{0%,80%,100%{transform:scale(.7);opacity:.4}40%{transform:scale(1);opacity:1}}@media(prefers-reduced-motion:reduce){*,*::before,*::after{scroll-behavior:auto!important;animation-duration:.01ms!important;animation-iteration-count:1!important;transition-duration:.01ms!important}}
    </style>
</head>
<body>
<div class="app">
    <div id="toast" role="status" aria-live="polite"></div>
    <input type="file" id="fileInput" hidden>
    <aside class="sidebar glass" id="sidebar">
        <div class="side-head">
            <a href="index.php" class="brand"><img src="sdpGhostlink.png" alt="GhostLink"><div>Ghost<span>Link</span></div></a>
            <p class="subline">A little space for your conversations.</p>
            <input type="search" id="searchUser" class="search" placeholder="Search people…" autocomplete="off" aria-label="Search people">
        </div>
        <div class="list-label">Your people</div>
        <div id="userList" class="user-list"><div class="empty">Finding people…</div></div>
        <div class="side-foot">
            <button type="button" class="side-link" onclick="showProfile()">Profile</button>
            <button type="button" class="side-link" onclick="showSettings()">Settings</button>
            <a href="index.php?page=usage" class="side-link">Guide</a>
            <a href="index.php?page=about" class="side-link">About</a>
            <a href="logout.php" class="side-link abort">Sign out</a>
        </div>
    </aside>

    <main class="chat glass">
        <header class="chat-head">
            <div class="head-left">
                <button type="button" class="icon-btn mobile-menu" onclick="toggleSidebar()" aria-label="Open contacts">☰</button>
                <div>
                    <div class="target" id="chatTitle">Choose someone to chat</div>
                    <div class="target-sub" id="chatSubtitle">Your private conversation starts here</div>
                </div>
            </div>
            <div class="head-actions">
                <label class="key-wrap"><span class="key-label">Shared key</span><input type="password" id="secretKey" class="key-input" placeholder="Enter key" title="Use the same key as your contact" autocomplete="off" aria-label="Shared encryption key"></label>
                <button type="button" class="settings-trigger" onclick="showSettings()" title="Dashboard settings" aria-label="Dashboard settings">⚙ <span class="button-label">Settings</span></button>
                <button type="button" class="icon-btn" onclick="showProfile()" title="Your profile" aria-label="Your profile">☺</button>
            </div>
        </header>

        <section class="messages" id="msgBox" aria-live="polite" aria-label="Conversation">
            <div class="empty"><img src="sdpGhostlink.png" alt=""><br>Select a person to start a conversation.</div>
        </section>

        <form class="input-zone hidden" id="inputZone">
            <button type="button" class="attach" id="attachButton" title="Attach a file" aria-label="Attach a file">＋</button>
            <input type="text" id="msgInput" class="input" placeholder="Write a message…" autocomplete="off" aria-label="Message">
            <button type="submit" class="send" id="sendButton">Send&nbsp; ↗</button>
        </form>
    </main>
</div>

<div id="contextMenu"><button type="button" onclick="confirmDelete()">Delete message</button></div>
<div id="profileModal" class="modal-overlay hidden"><section class="modal glass" role="dialog" aria-modal="true" aria-labelledby="profileTitle"><h2 id="profileTitle">Your profile</h2><p class="modal-copy">Update the details other people see about you.</p><input type="text" id="editBio" placeholder="A short bio" maxlength="255"><input type="tel" id="editPhone" placeholder="Phone number"><div class="modal-actions"><button type="button" class="icon-btn" onclick="closeProfile()">Cancel</button><button type="button" class="send" onclick="saveProfile()">Save changes</button></div></section></div>
<div id="settingsModal" class="modal-overlay hidden"><section class="modal glass" role="dialog" aria-modal="true" aria-labelledby="settingsTitle"><h2 id="settingsTitle">Your dashboard</h2><p class="modal-copy">Customise your GhostLink workspace.</p><div class="setting-row"><label>Color theme</label><div class="theme-swatches" role="group" aria-label="Choose a color theme"><button type="button" class="swatch" data-value="glass" aria-label="Glass theme" title="Glass"></button><button type="button" class="swatch" data-value="aurora" aria-label="Aurora theme" title="Aurora"></button><button type="button" class="swatch" data-value="midnight" aria-label="Midnight theme" title="Midnight"></button><button type="button" class="swatch" data-value="rose" aria-label="Rose theme" title="Rose"></button></div></div><div class="setting-row"><label for="densitySetting">Message spacing</label><select id="densitySetting"><option value="comfortable">Comfortable</option><option value="compact">Compact</option></select></div><div class="setting-row"><label for="fontSetting">Text size</label><select id="fontSetting"><option value="normal">Normal</option><option value="large">Large</option></select></div><div class="setting-row"><label for="soundSetting">New message sound</label><input id="soundSetting" type="checkbox"></div><div class="modal-actions"><button type="button" class="send" onclick="closeSettings()">Done</button></div></section></div>
<audio id="notifySound" src="https://actions.google.com/sounds/v1/alarms/beep_short.ogg" preload="none"></audio>
<script>
let currentTargetId=null, refreshInterval=null, selectedMsgId=null, lastMsgIds=new Set(), messageRequest=0, toastTimer=null;
const $=id=>document.getElementById(id);

function showToast(message){
  const toast=$('toast');
  toast.textContent=message;
  toast.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer=setTimeout(()=>toast.classList.remove('show'),2800);
}

function checkResponse(text){
  if(text.trim()==='ACCESS DENIED'){ location.href='index.php?page=login&error=Session%20Expired'; return false; }
  return true;
}

function toggleSidebar(){ $('sidebar').classList.toggle('active'); }

async function postForm(fields){
  const form=new FormData();
  for(const [key,value] of Object.entries(fields)) form.append(key,value);
  const response=await fetch('engine.php',{method:'POST',body:form,credentials:'same-origin'});
  return response.text();
}

function initialsFromName(name=''){
  const parts=name.trim().split(/\s+/).filter(Boolean); if(!parts.length) return '?';
  return parts.slice(0,2).map(part=>part[0].toUpperCase()).join('');
}

function decorateUserCards(){
  document.querySelectorAll('.user-card').forEach(card=>{
    if(card.dataset.decorated==='1') return;
    card.dataset.decorated='1';
    const nameEl=card.querySelector('b');
    const username = nameEl ? nameEl.textContent.trim() : 'Ghost';
    const avatar=document.createElement('div');
    avatar.className='avatar';
    avatar.textContent=initialsFromName(username);
    const main=document.createElement('div');
    main.className='user-main';
    main.appendChild(avatar);
    const meta=document.createElement('div');
    meta.className='user-meta';
    const nameRow=document.createElement('div');
    nameRow.className='user-name-row';
    const name=document.createElement('span');
    name.className='user-name';
    name.textContent=username;
    const dot=document.createElement('span');
    dot.className='status-dot';
    const online = card.querySelector('.status-dot')?.style.backgroundColor === 'rgb(0, 255, 65)' || card.querySelector('.status-dot')?.style.backgroundColor === '#00ff41';
    dot.classList.add(online?'status-online':'status-offline');
    dot.style.background = online ? '#7ef0ad' : '#7a8596';
    dot.style.color = online ? '#7ef0ad' : '#7a8596';
    nameRow.append(name,dot);
    const bio=document.createElement('div');
    bio.className='user-bio';
    bio.textContent = card.querySelector('div > div')?.textContent || 'Connected';
    meta.append(nameRow,bio);
    main.appendChild(meta);
    card.innerHTML='';
    card.appendChild(main);
    const state=document.createElement('div');
    state.className='user-state';
    const label=document.createElement('span');
    label.textContent=online ? 'Online' : 'Offline';
    state.appendChild(label);
    const badge=document.createElement('span');
    badge.className='unread-badge';
    badge.textContent='';
    state.appendChild(badge);
    card.appendChild(state);
    card.querySelector('.unread-badge').style.visibility='hidden';
  });
}

async function fetchUsers(){
  try{
    const data=await postForm({mode:'get_users',search:$('searchUser').value});
    if(!checkResponse(data)) return;
    const list=$('userList');
    if(list.innerHTML!==data){
      list.innerHTML=data;
      decorateUserCards();
      list.querySelectorAll('.user-card').forEach(card=>{
        card.addEventListener('click',()=>{
          list.querySelectorAll('.user-card').forEach(item=>item.classList.remove('active'));
          card.classList.add('active');
          const name = card.querySelector('.user-name')?.textContent || 'User';
          openChat(card.getAttribute('data-user-id') || card.getAttribute('onclick')?.match(/openChat\((\d+)/)?.[1], name);
        });
      });
    }
  }catch(e){ showToast('Could not load contacts. Try again.'); }
}

function scrollToLatest(){
  const box=$('msgBox'); box.scrollTop=box.scrollHeight;
}

function appendMessageContent(message,text){
  if(text.startsWith('FILE:')){
    const path=text.slice(5).trim();
    if(!/^uploads\/[\w./-]+$/.test(path)){ message.append(document.createTextNode('Attachment unavailable')); return; }
    const extension=path.split('.').pop().toLowerCase();
    if(['jpg','jpeg','png','gif','webp'].includes(extension)){
      const image=document.createElement('img');
      image.src=path; image.alt='Shared image'; image.className='chat-img'; image.loading='lazy';
      image.addEventListener('click',()=>window.open(path,'_blank','noopener,noreferrer'));
      message.append(image);
    } else {
      const link=document.createElement('a');
      link.href=path; link.target='_blank'; link.rel='noopener noreferrer'; link.className='file-link';
      link.textContent='↗ Download '+extension.toUpperCase()+' file';
      message.append(link);
    }
    return;
  }
  message.append(document.createTextNode(text));
}

function bindMessageMenu(message){
  message.addEventListener('contextmenu',event=>{
    event.preventDefault();
    if(!message.classList.contains('sent')){ showToast('You can only delete your own messages.'); return; }
    selectedMsgId=message.id.replace('msg_','');
    const menu=$('contextMenu');
    menu.style.display='block';
    menu.style.left=Math.min(event.clientX, innerWidth-180)+'px';
    menu.style.top=Math.min(event.clientY, innerHeight-60)+'px';
  });
}

window.openChat=function(userId, username){
  currentTargetId=String(userId);
  $('chatTitle').textContent=username;
  $('chatSubtitle').textContent='Messages are encrypted in your browser';
  $('inputZone').classList.remove('hidden');
  $('sidebar').classList.remove('active');
  $('msgBox').innerHTML='<div class="empty">Opening your conversation…</div>';
  lastMsgIds=new Set();
  if(refreshInterval) clearInterval(refreshInterval);
  const request=++messageRequest;
  fetchMessages(request);
  refreshInterval=setInterval(()=>fetchMessages(request),3000);
};

async function fetchMessages(request=messageRequest){
  if(!currentTargetId || request!==messageRequest) return;
  const target=currentTargetId;
  try{
    const html=await postForm({mode:'get_msgs',target});
    if(request!==messageRequest || target!==currentTargetId || !checkResponse(html)) return;
    const temp=document.createElement('div'); temp.innerHTML=html;
    const sources=[...temp.querySelectorAll('.msg')];
    const box=$('msgBox');
    const nearBottom=box.scrollHeight-box.scrollTop-box.clientHeight<110;
    const key=$('secretKey').value;
    const nextIds=new Set();
    let hasNewIncoming=false;

    for(const source of sources){
      const id=source.id; if(!id) continue;
      nextIds.add(id);
      const encrypted=source.dataset.enc||'';
      let message=document.getElementById(id);
      if(message && message.dataset.encrypted===encrypted) continue;
      const isNew=!lastMsgIds.has(id);
      if(isNew && source.classList.contains('received')) hasNewIncoming=true;
      if(!message){
        message=document.createElement('div');
        message.id=id;
        message.className='msg '+(source.classList.contains('sent')?'sent':'received');
        message.dataset.encrypted=encrypted;
        bindMessageMenu(message);
        box.append(message);
      } else {
        message.replaceChildren();
        message.className='msg '+(source.classList.contains('sent')?'sent':'received');
        message.dataset.encrypted=encrypted;
      }

      let text='';
      if(key && typeof CryptoJS!=='undefined'){
        try{ text=CryptoJS.AES.decrypt(encrypted,key).toString(CryptoJS.enc.Utf8); }
        catch(e){ text=''; }
      }
      if(text) appendMessageContent(message,text);
      else message.textContent=key ? 'Unable to decrypt. Check the shared key.' : 'Enter your shared key to read this message.';

      if(source.dataset.time){
        const time=document.createElement('small');
        time.className='msg-time';
        time.textContent=source.dataset.time;
        message.append(time);
      }
    }

    for(const old of [...box.querySelectorAll('.msg')]) if(!nextIds.has(old.id)) old.remove();

    if(!sources.length && !box.querySelector('.empty')) box.innerHTML='<div class="empty">No messages yet. Send the first one.</div>';
    else if(sources.length){
      box.querySelector('.empty')?.remove();
      if(nearBottom || lastMsgIds.size===0) scrollToLatest();
    }

    if(hasNewIncoming && $('soundSetting').checked) $('notifySound').play().catch(()=>{});
    lastMsgIds=nextIds;
  }catch(e){ /* keep the conversation visible during polling */ }
}

async function sendMessage(){
  const input=$('msgInput'), text=input.value, key=$('secretKey').value;
  if(!currentTargetId){ showToast('Choose someone to chat with first.'); return; }
  if(!text.trim()) return;
  if(!key.trim()){ showToast('Add your shared key before sending.'); $('secretKey').focus(); return; }
  if(typeof CryptoJS==='undefined'){ showToast('Encryption could not load. Refresh and try again.'); return; }
  const button=$('sendButton'); button.disabled=true;
  try{
    const encrypted=CryptoJS.AES.encrypt(text,key).toString();
    const result=await postForm({mode:'send_msg',target:currentTargetId,msg:encrypted});
    if(!checkResponse(result)) return;
    if(result.trim()==='sent'){ input.value=''; await fetchMessages(); }
    else showToast('Message could not be sent. Please retry.');
  }catch(e){ showToast('Message could not be sent. Please retry.'); }
  finally{ button.disabled=false; input.focus(); }
}

async function uploadFile(){
  const input=$('fileInput'); if(!input.files.length) return;
  const file=input.files[0];
  if(file.size>25000000){ showToast('File is too large (max 25 MB).'); input.value=''; return; }
  if(!$('secretKey').value.trim()){ showToast('Add your shared key before sharing a file.'); input.value=''; return; }
  if(!currentTargetId){ showToast('Choose someone to chat with first.'); input.value=''; return; }

  try{
    const form=new FormData(); form.append('file',file);
    const response=await fetch('upload.php',{method:'POST',body:form,credentials:'same-origin'});
    const path=(await response.text()).trim();
    if(path.startsWith('error:')){ showToast(path); return; }
    $('msgInput').value='FILE:'+path;
    await sendMessage();
  }catch(e){ showToast('Upload failed. Please try again.'); }
  finally{ input.value=''; $('msgInput').value=''; }
}

function hideMenu(){ $('contextMenu').style.display='none'; }
window.addEventListener('click',event=>{ if(!event.target.closest('#contextMenu')) hideMenu(); });
window.confirmDelete=async function(){
  if(!selectedMsgId) return;
  if(confirm('Delete this message?')){
    try{
      const response=await postForm({mode:'delete_msg',msg_id:selectedMsgId});
      if(!checkResponse(response)) return;
      if(response.includes('deleted')){
        $('msg_'+selectedMsgId)?.remove();
        lastMsgIds.delete('msg_'+selectedMsgId);
        showToast('Message deleted.');
      }
    }catch(e){ showToast('Could not delete the message.'); }
  }
  hideMenu();
};

window.showProfile=async function(){
  $('profileModal').classList.remove('hidden');
  try{
    const result=await postForm({mode:'get_my_profile'});
    if(checkResponse(result)){ const data=JSON.parse(result); $('editBio').value=data.bio||''; $('editPhone').value=data.phone||''; }
  }catch(e){ showToast('Could not load your profile.'); }
};
window.closeProfile=()=>$('profileModal').classList.add('hidden');
window.saveProfile=async function(){
  try{
    const result=await postForm({mode:'update_profile',bio:$('editBio').value,phone:$('editPhone').value});
    if(checkResponse(result)){ closeProfile(); showToast('Profile updated.'); fetchUsers(); }
  }catch(e){ showToast('Could not save your profile.'); }
};

const defaults={theme:'glass',density:'comfortable',fontSize:'normal',sound:true};
function preferences(){
  return {
    theme: document.body.dataset.theme || 'glass',
    density: document.body.dataset.density || 'comfortable',
    fontSize: document.body.dataset.fontSize || 'normal',
    sound: $('soundSetting') ? $('soundSetting').checked : true
  };
}
function savePreferences(){
  const p=preferences();
  try{ localStorage.setItem('ghostlink-dashboard-settings', JSON.stringify(p)); } catch(e){}
  document.querySelectorAll('.swatch').forEach(button=>button.setAttribute('aria-pressed', String(button.dataset.value===p.theme)));
}
function loadPreferences(){
  let saved={};
  try{ saved=JSON.parse(localStorage.getItem('ghostlink-dashboard-settings') || '{}'); } catch(e){}
  const p={...defaults,...saved};
  document.body.dataset.theme = p.theme;
  document.body.dataset.density = p.density;
  document.body.dataset.fontSize = p.fontSize;
  $('densitySetting').value = p.density;
  $('fontSetting').value = p.fontSize;
  $('soundSetting').checked = p.sound;
  document.querySelector('meta[name="theme-color"]').content = p.theme === 'aurora' ? '#071218' : p.theme === 'midnight' ? '#080a13' : p.theme === 'rose' ? '#140b15' : '#090d17';
  document.querySelectorAll('.swatch').forEach(button=>button.setAttribute('aria-pressed', String(button.dataset.value===p.theme)));
}
window.showSettings=()=>{ $('settingsModal').classList.remove('hidden'); loadPreferences(); };
window.closeSettings=()=>$('settingsModal').classList.add('hidden');

document.querySelectorAll('.swatch').forEach(button=>button.addEventListener('click',()=>{
  document.body.dataset.theme=button.dataset.value; savePreferences();
}));
$('densitySetting').addEventListener('change',()=>{ document.body.dataset.density=$('densitySetting').value; savePreferences(); });
$('fontSetting').addEventListener('change',()=>{ document.body.dataset.fontSize=$('fontSetting').value; savePreferences(); });
$('soundSetting').addEventListener('change',savePreferences);

$('inputZone').addEventListener('submit',event=>{ event.preventDefault(); sendMessage(); });
$('attachButton').addEventListener('click',()=>$('fileInput').click());
$('fileInput').addEventListener('change',uploadFile);
$('searchUser').addEventListener('input',fetchUsers);
$('secretKey').addEventListener('input',()=>{ if(currentTargetId) fetchMessages(); });

document.querySelectorAll('.modal-overlay').forEach(overlay=>overlay.addEventListener('click',event=>{ if(event.target===overlay) overlay.classList.add('hidden'); }));
window.addEventListener('keydown',event=>{ if(event.key==='Escape'){ hideMenu(); $('profileModal').classList.add('hidden'); $('settingsModal').classList.add('hidden'); $('sidebar').classList.remove('active'); }});

loadPreferences();
fetchUsers();
setInterval(fetchUsers,7000);
</script>
</body>
</html>
