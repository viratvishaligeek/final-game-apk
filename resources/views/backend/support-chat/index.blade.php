@extends('backend.include.layout')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h2 class="fw-bold mb-1">Live Support Chat</h2>
            <p class="text-muted mb-0">Private user conversations, attachments and voice replies.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <span class="badge bg-secondary" id="connection-status">Connecting…</span>
            <button type="button" class="btn btn-sm btn-outline-primary" id="enable-desktop-alerts">Enable desktop & push alerts</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="disable-admin-push">Disable admin push</button>
        </div>
    </div>
    <div class="row g-3" style="min-height:65vh">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white">
                    <label for="user-search" class="form-label fw-bold mb-2">Find any registered user</label>
                    <input id="user-search" type="search" class="form-control form-control-sm mb-2"
                        placeholder="Search name, phone or user ID…" autocomplete="off">
                    <div id="user-search-results" class="list-group mb-3" aria-live="polite">
                        <div class="list-group-item small text-muted">Search users to start a new conversation.</div>
                    </div>
                    <label for="conversation-search" class="form-label fw-bold mb-2">Existing conversations</label>
                    <input id="conversation-search" type="search" class="form-control form-control-sm"
                        placeholder="Search registered phone number…" inputmode="tel" autocomplete="off">
                </div>
                <div id="conversations" class="list-group list-group-flush overflow-auto" style="max-height:65vh"></div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card h-100 d-flex flex-column border-0 shadow-sm">
                <div id="chat-title" class="card-header fw-bold bg-white">Select a conversation</div>
                <div id="messages" class="card-body overflow-auto d-flex flex-column gap-2 bg-light" style="height:50vh">
                    <p class="text-muted text-center my-auto">Choose a conversation to view messages.</p>
                </div>
                <form id="send-form" class="card-footer bg-white d-flex flex-wrap gap-2 align-items-center">
                    <input id="attachment" type="file" class="form-control form-control-sm" accept="image/*,video/mp4,video/webm,audio/*" style="max-width:190px">
                    <button id="record-voice" type="button" class="btn btn-outline-secondary btn-sm" title="Start voice recording"><i class="fas fa-microphone"></i> Record</button>
                    <span id="recording-status" class="small text-danger d-none">Recording… press Stop to attach</span>
                    <input id="body" class="form-control" maxlength="5000" placeholder="Write a reply…" aria-label="Reply message">
                    <button class="btn btn-warning" type="submit">Send</button>
                </form>
                <div class="px-3 pb-2 small text-muted">Maximum attachment size: 20 MB. Keep this tab open for desktop alerts.</div>
            </div>
        </div>
    </div>
</div>
<script src="/firebase-config.js"></script>
<script src="https://www.gstatic.com/firebasejs/11.10.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/11.10.0/firebase-messaging-compat.js"></script>
<script>
(() => {
 const root = @json(url('/admin/support-chat'));
 const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
 const playChatSound = @json(($chatSoundEnabled ?? true) === true);
 let active = null, lastId = 0, busy = false, recordedFile = null, recorder = null, stream = null, chunks = [];
 let pollTimer = null, pollDelay = 30000;
 const conversations = document.getElementById('conversations');
 const messages = document.getElementById('messages');
 const status = document.getElementById('connection-status');
 const conversationSearch = document.getElementById('conversation-search');
 let conversationSearchTimer = null;
 let userSearchTimer = null;
 let userSearchRequest = 0;
 const userSearch = document.getElementById('user-search');
 const userSearchResults = document.getElementById('user-search-results');
 let realtimeConnected = false;
 let conversationSearchRequest = 0;
 const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
 async function request(url, options={}) {
   const response = await fetch(url, {credentials:'same-origin', ...options, headers:{'X-CSRF-TOKEN':token, 'Accept':'application/json', ...(options.headers||{})}});
   if (!response.ok) throw new Error('Request failed ('+response.status+')');
   return response.json();
 }
 async function loadConversations() {
   const requestId = ++conversationSearchRequest;
   const phone = conversationSearch ? conversationSearch.value.trim() : '';
   const query = phone ? '?phone='+encodeURIComponent(phone) : '';
   const result = await request(root+'/conversations'+query);
   if (requestId !== conversationSearchRequest) return;
   conversations.replaceChildren();
   const items = Array.isArray(result.data) ? result.data : [];
   if (!items.length) {
     const empty = document.createElement('div');
     empty.className = 'list-group-item text-muted small py-3';
     empty.textContent = phone ? 'No conversations found for this phone number.' : 'No conversations yet.';
     conversations.appendChild(empty);
     return;
   }
   items.forEach(c => {
     const button = document.createElement('button');
     button.type='button'; button.className='list-group-item list-group-item-action '+(active===c.id?'active':'');
     button.innerHTML='<div class="d-flex justify-content-between gap-2"><span class="fw-bold">'+esc(c.user?.name || ('User #'+c.id))+'</span>'+(c.unread_count ? '<span class="badge bg-danger">'+c.unread_count+'</span>' : '')+'</div><small>'+esc(c.user?.phone || '')+'</small><div class="small text-muted text-truncate">'+esc(c.last_message_preview || 'No messages yet')+'</div>';
     button.onclick=()=>selectConversation(c);
     conversations.appendChild(button);
   });
 async function searchUsers() {
   const requestId = ++userSearchRequest;
   const term = userSearch ? userSearch.value.trim() : '';
   userSearchResults.replaceChildren();
   if (!term) {
     const empty = document.createElement('div');
     empty.className = 'list-group-item small text-muted';
     empty.textContent = 'Search by name, phone number or user ID.';
     userSearchResults.appendChild(empty);
     return;
   }
   const loading = document.createElement('div');
   loading.className = 'list-group-item small text-muted';
   loading.textContent = 'Searching registered users…';
   userSearchResults.appendChild(loading);
   try {
     const result = await request(root + '/users?q=' + encodeURIComponent(term));
     if (requestId !== userSearchRequest) return;
     userSearchResults.replaceChildren();
     const users = Array.isArray(result.data) ? result.data : [];
     if (!users.length) {
       const empty = document.createElement('div');
       empty.className = 'list-group-item small text-muted';
       empty.textContent = 'No registered users found.';
       userSearchResults.appendChild(empty);
       return;
     }
     users.forEach(user => {
       const button = document.createElement('button');
       button.type = 'button';
       button.className = 'list-group-item list-group-item-action py-2';
       const title = document.createElement('div');
       title.className = 'd-flex justify-content-between gap-2';
       const name = document.createElement('strong');
       name.textContent = user.name || ('User #' + user.id);
       const state = document.createElement('span');
       state.className = 'badge ' + (Number(user.unread_count) > 0 ? 'bg-danger' : user.has_conversation ? 'bg-success' : 'bg-primary');
       state.textContent = Number(user.unread_count) > 0 ? Number(user.unread_count) + ' unread' : user.has_conversation ? 'Existing chat' : 'Start chat';
       title.append(name, state);
       const meta = document.createElement('small');
       meta.className = 'text-muted d-block';
       meta.textContent = (user.phone || 'No phone') + ' · User #' + user.id + ' · ' + (user.status || 'unknown');
       button.append(title, meta);
       button.addEventListener('click', async () => {
         button.disabled = true;
         try {
           const started = await request(root + '/conversations', {
             method: 'POST',
             headers: { 'Content-Type': 'application/json' },
             body: JSON.stringify({ user_id: user.id })
           });
           await loadConversations();
           await selectConversation({
             id: started.data.id,
             user: started.data.user || { id: user.id, name: user.name, phone: user.phone }
           });
           userSearchResults.replaceChildren();
           userSearch.value = '';
         } catch (error) {
           window.alert(error.message || 'Unable to open this user conversation.');
         } finally {
           button.disabled = false;
         }
       });
       userSearchResults.appendChild(button);
     });
   } catch (error) {
     if (requestId !== userSearchRequest) return;
     userSearchResults.replaceChildren();
     const failure = document.createElement('div');
     failure.className = 'list-group-item small text-danger';
     failure.textContent = error.message || 'User search failed. Please retry.';
     userSearchResults.appendChild(failure);
   }
 }
 if (userSearch) {
   userSearch.addEventListener('input', () => {
     if (userSearchTimer) clearTimeout(userSearchTimer);
     userSearchTimer = window.setTimeout(() => { void searchUsers(); }, 300);
   });
 }

   const requestedId = new URLSearchParams(window.location.search).get('conversation_id');
   if (!window.__adminChatInitialConversationOpened && requestedId) {
     const requested = items.find(item => Number(item.id) === Number(requestedId));
     if (requested) {
       window.__adminChatInitialConversationOpened = true;
       void selectConversation(requested);
     }
   }
 }
 window.addEventListener('admin:support-message', event => {
   const payload = event.detail || {};
   void loadConversations().catch(error => console.warn('Realtime inbox refresh failed', error));
   if (active && Number(payload.conversation_id) === Number(active)) {
     void loadMessages(false);
   }
 });
 function notifyAdmin(message) {
   window.dispatchEvent(new CustomEvent('admin:incoming-chat-message', { detail: { ...message, sender: 'user' } }));
   if ('Notification' in window && Notification.permission === 'granted') {
     const notification = new Notification('New support message', {body: message.body || 'User sent an attachment.'});
     notification.onclick = () => window.focus();
   }
 }
 async function selectConversation(c) {
   active=c.id; lastId=0;
   document.getElementById('chat-title').textContent=(c.user?.name || 'User')+' · '+(c.user?.phone || '');
   await loadMessages(true); await loadConversations();
 }
 async function loadMessages(reset=false) {
   if (!active || busy) return;
   busy=true;
   try {
     const wasNearBottom = messages.scrollHeight - messages.scrollTop - messages.clientHeight < 100;
     const query = !reset ? '?after_id='+encodeURIComponent(String(lastId)) : '';
     const result=await request(root+'/'+active+'/messages'+query);
     const all=result.data || [];
     const previousId = lastId;
     if (!reset && previousId > 0) {
       all.filter(m => m.sender === 'user' && m.id > previousId).forEach(notifyAdmin);
     }
     const rendered=all.map(m=>{
       let media='';
       if (m.media_url) {
         if (String(m.media_mime||'').startsWith('image/')) media='<img alt="Attachment" src="'+esc(m.media_url)+'" style="max-width:min(260px,65vw);max-height:240px;border-radius:8px">';
         else if (String(m.media_mime||'').startsWith('video/')) media='<video controls playsinline src="'+esc(m.media_url)+'" style="max-width:min(300px,65vw);max-height:240px"></video>';
         else if (String(m.media_mime||'').startsWith('audio/')) media='<audio controls preload="metadata" src="'+esc(m.media_url)+'"></audio>';
         else media='<a href="'+esc(m.media_url)+'" target="_blank" rel="noopener">Open attachment</a>';
       }
       return '<div class="p-3 rounded-3 border '+(m.sender==='admin'?'align-self-end bg-warning-subtle':'align-self-start bg-white')+'" style="max-width:90%;overflow-wrap:anywhere">'+(m.body?'<div style="white-space:pre-wrap">'+esc(m.body)+'</div>':'')+media+'<small class="d-block text-muted mt-2">'+esc(m.created_at||'')+(m.read_at?' · Seen':'')+'</small></div>';
     }).join('');
     if (reset || messages.querySelector('p.text-muted.text-center')) {
       messages.innerHTML=rendered || '<p class="text-muted text-center my-auto">No messages yet. Send the first reply.</p>';
     } else if (rendered) {
       messages.insertAdjacentHTML('beforeend', rendered);
     }
     if (all.length) lastId=Math.max(lastId,...all.map(m=>Number(m.id)||0));
     if (reset || (rendered && wasNearBottom)) messages.scrollTop=messages.scrollHeight;
     status.textContent='Connected · refreshes automatically'; status.className='badge bg-success';
   } catch(e) {
     status.textContent='Reconnecting…'; status.className='badge bg-secondary';
     console.warn('Support chat sync failed',e);
   } finally { busy=false; }
 }
 if (conversationSearch) {
   conversationSearch.addEventListener('input', () => {
     if (conversationSearchTimer) clearTimeout(conversationSearchTimer);
     conversationSearchTimer = window.setTimeout(() => {
       loadConversations().catch(error => {
         status.textContent = 'Search unavailable';
         status.className = 'badge bg-danger';
         console.warn('Conversation search failed', error);
       });
     }, 250);
   });
 }
  document.getElementById('enable-desktop-alerts').addEventListener('click', async () => {
   if (!('Notification' in window)) return alert('Notifications are not supported by this browser.');
   const permission = await Notification.requestPermission();
   if (permission !== 'granted') return alert('Notifications were not enabled. Please allow notifications in browser settings.');

   if (!('serviceWorker' in navigator) || !window.firebase || !window.FIREBASE_CONFIG || !window.FIREBASE_VAPID_KEY) {
     return alert('Desktop alerts are enabled while this tab is open, but background push configuration is not available on this domain.');
   }

   try {
     const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
     await navigator.serviceWorker.ready;
     const firebaseApp = window.firebase.apps.length ? window.firebase.app() : window.firebase.initializeApp(window.FIREBASE_CONFIG);
     const messaging = window.firebase.messaging(firebaseApp);
     const token = await messaging.getToken({ vapidKey: window.FIREBASE_VAPID_KEY, serviceWorkerRegistration: registration });
     if (!token) throw new Error('Firebase did not return a push token.');
     await request(root.replace(/\/support-chat\/?$/, '/push-subscribe'), {
       method: 'POST',
       headers: { 'Content-Type': 'application/json' },
       body: JSON.stringify({ token, platform: 'web' }),
     });
     alert('Background push alerts enabled for this admin account.');
   } catch (error) {
     alert('Desktop alerts are enabled, but background push registration failed: ' + (error?.message || 'Unknown error'));
   }
 });
 document.getElementById('disable-admin-push').addEventListener('click', async () => {
   if (!window.firebase || !window.FIREBASE_CONFIG || !window.FIREBASE_VAPID_KEY) return alert('Firebase web push configuration is not available.');
   try {
     const registration = await navigator.serviceWorker.getRegistration('/');
     if (!registration) return alert('No admin push registration was found.');
     const firebaseApp = window.firebase.apps.length ? window.firebase.app() : window.firebase.initializeApp(window.FIREBASE_CONFIG);
     const messaging = window.firebase.messaging(firebaseApp);
     const token = await messaging.getToken({ vapidKey: window.FIREBASE_VAPID_KEY, serviceWorkerRegistration: registration });
     if (!token) return alert('No active push token was found.');
     await request(root.replace(/\/support-chat\/?$/, '/push-subscribe'), {
       method: 'DELETE',
       headers: { 'Content-Type': 'application/json' },
       body: JSON.stringify({ token }),
     });
     alert('Admin background push disabled. Browser permission was left unchanged so user-app notifications are not disturbed.');
   } catch (error) {
     alert('Unable to disable admin push: ' + (error?.message || 'Unknown error'));
   }
 });
 const recordButton = document.getElementById('record-voice');
 const recordingStatus = document.getElementById('recording-status');
 recordButton.addEventListener('click', async () => {
   if (recorder && recorder.state === 'recording') {
     recorder.stop();
     recordButton.textContent='Record';
     recordButton.classList.remove('btn-danger'); recordButton.classList.add('btn-outline-secondary');
     recordingStatus.textContent='Preparing voice attachment…';
     return;
   }
   if (!navigator.mediaDevices?.getUserMedia || typeof MediaRecorder === 'undefined') return alert('Voice recording is not supported by this browser.');
   try {
     stream=await navigator.mediaDevices.getUserMedia({audio:true});
     const mime=['audio/webm;codecs=opus','audio/webm','audio/mp4'].find(type=>MediaRecorder.isTypeSupported(type));
     recorder=new MediaRecorder(stream,mime?{mimeType:mime}:undefined); chunks=[]; recordedFile=null;
     recorder.ondataavailable=e=>{if(e.data.size)chunks.push(e.data)};
     recorder.onstop=()=>{
       const type=recorder.mimeType||'audio/webm'; const ext=type.includes('mp4')?'mp4':'webm';
       recordedFile=new File([new Blob(chunks,{type})],'voice-message.'+ext,{type});
       document.getElementById('attachment').value='';
       recordingStatus.textContent='Voice message ready to send'; recordingStatus.classList.remove('d-none');
       stream?.getTracks().forEach(t=>t.stop()); stream=null;
     };
     recorder.start(); recordButton.textContent='Stop'; recordButton.classList.remove('btn-outline-secondary'); recordButton.classList.add('btn-danger');
     recordingStatus.textContent='Recording… press Stop to attach'; recordingStatus.classList.remove('d-none');
   } catch(e) { alert('Microphone permission was denied or unavailable.'); }
 });
 document.getElementById('attachment').addEventListener('change',()=>{recordedFile=null;recordingStatus.classList.add('d-none');});
 document.getElementById('send-form').addEventListener('submit', async event=>{
   event.preventDefault(); if(!active) return alert('Select a conversation first.');
   const body=document.getElementById('body'), file=document.getElementById('attachment');
   if(!body.value.trim()&&!file.files.length&&!recordedFile) return;
   if (recorder && recorder.state === 'recording') return alert('Stop recording before sending.');
   const data=new FormData(); if(body.value.trim()) data.append('body',body.value.trim());
   const attachment = recordedFile || file.files[0]; if(attachment) data.append('attachment',attachment);
   try { await request(root+'/'+active+'/send',{method:'POST',body:data}); body.value=''; file.value=''; recordedFile=null; recordingStatus.classList.add('d-none'); await loadMessages(true); await loadConversations(); }
   catch(error){ alert(error.message); }
 });
 async function tick(){
   try {
     await loadConversations();
     if(active) await loadMessages();
     else {status.textContent='Connected';status.className='badge bg-success';}
     pollDelay = 30000;
   } catch(e) {
     pollDelay = Math.min(60000, pollDelay * 2);
     status.textContent='Reconnecting…'; status.className='badge bg-secondary';
   } finally {
     if (!realtimeConnected) pollTimer = window.setTimeout(tick, pollDelay);
   }
 }
 window.addEventListener('admin:broadcast-state', event => {
   const connected = event.detail?.current === 'connected';
   realtimeConnected = connected;
   if (connected) {
     if (pollTimer) clearTimeout(pollTimer);
     pollTimer = null;
     void loadConversations();
     if (active) void loadMessages();
     status.textContent = 'Live · Pusher';
     status.className = 'badge bg-success';
   } else if (!pollTimer) {
     pollTimer = window.setTimeout(tick, 30000);
   }
 });
 loadConversations().then(()=>{
   if (window.adminPusher?.connection?.state === 'connected') realtimeConnected = true;
   status.textContent = realtimeConnected ? 'Live · Pusher' : 'Connected · recovery sync';
   status.className = 'badge bg-success';
 }).catch(()=>{status.textContent='Reconnecting…';status.className='badge bg-secondary';});
 if (window.adminPusher?.connection?.state === 'connected') {
   realtimeConnected = true;
   if (pollTimer) clearTimeout(pollTimer);
   pollTimer = null;
 } else if (!realtimeConnected) {
   pollTimer=window.setTimeout(tick,pollDelay);
 }
 window.addEventListener('beforeunload',()=>{ if(pollTimer) clearTimeout(pollTimer); stream?.getTracks().forEach(t=>t.stop()); });
})();
</script>
@endsection
