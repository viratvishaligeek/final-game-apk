@extends('backend.include.layout')

@section('content')
<style>
    .admin-chat-shell{min-height:76vh;background:#fff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;box-shadow:0 8px 28px rgba(15,23,42,.06)}
    .admin-chat-sidebar{background:#fff;border-right:1px solid #e5e7eb;min-height:76vh}
    .admin-chat-sidebar .card-header{padding:1rem;background:#fff}
    .admin-chat-inbox{max-height:calc(76vh - 220px);min-height:260px;overflow-y:auto}
    .admin-chat-main{min-height:76vh;background:#efeae2}
    .admin-chat-title{background:#f8fafc;border-bottom:1px solid #e5e7eb;padding:1rem}
    #messages{height:calc(76vh - 150px)!important;min-height:360px;background-color:#efeae2!important;background-image:radial-gradient(rgba(15,23,42,.035) .7px,transparent .7px);background-size:14px 14px}
    .admin-chat-bubble{max-width:min(78%,560px);padding:.65rem .85rem;border-radius:14px;box-shadow:0 1px 1px rgba(15,23,42,.08);overflow-wrap:anywhere}
    .admin-chat-bubble--incoming{align-self:flex-start;background:#fff;border-top-left-radius:4px}
    .admin-chat-bubble--outgoing{align-self:flex-end;background:#d9fdd3;border-top-right-radius:4px}
    .admin-chat-search-results .list-group-item{border-left:0;border-right:0}
    .admin-chat-search-results .list-group-item:first-child{border-top:0}
    .admin-chat-search-results .list-group-item:last-child{border-bottom:0}
    @media(max-width:767.98px){.admin-chat-shell{min-height:auto;border-radius:12px}.admin-chat-sidebar,.admin-chat-main{min-height:0}.admin-chat-inbox{max-height:260px;min-height:120px}#messages{height:52vh!important;min-height:300px}.admin-chat-bubble{max-width:90%}}
</style>
<div class="container-fluid px-3 px-lg-4 py-3">
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
    <div class="row g-0 admin-chat-shell">
        <div class="col-12 col-lg-4 admin-chat-sidebar">
            <div class="card h-100 border-0 rounded-0">
                <div class="card-header bg-white">
                    <label for="user-search" class="form-label fw-bold mb-2">Find any registered user</label>
                    <input id="user-search" type="search" class="form-control form-control-sm mb-2"
                        placeholder="Search name, phone or user ID…" autocomplete="off">
                    <div id="user-search-results" class="list-group mb-3 admin-chat-search-results" aria-live="polite">
                        <div class="list-group-item small text-muted">Search users to start a new conversation.</div>
                    </div>
                    <label for="conversation-search" class="form-label fw-bold mb-2">Existing conversations</label>
                    <input id="conversation-search" type="search" class="form-control form-control-sm"
                        placeholder="Search registered phone number…" inputmode="tel" autocomplete="off">
                </div>
                <div id="conversations" class="list-group list-group-flush admin-chat-inbox"></div>
            </div>
        </div>
        <div class="col-12 col-lg-8 admin-chat-main">
            <div class="card h-100 d-flex flex-column border-0 shadow-sm">
                <div id="chat-title" class="card-header fw-bold admin-chat-title">Select a conversation</div>
                <div id="messages" class="card-body overflow-auto d-flex flex-column gap-2">
                    <p class="text-muted text-center my-auto">Choose a conversation to view messages.</p>
                </div>
                <form id="send-form" class="card-footer bg-white border-top d-flex flex-wrap gap-2 align-items-center p-3">
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
 let conversationVersion = 0, pendingMessageRefresh = false, pendingMessageReset = false;
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
 function request(url, options = {}) {
   const body = options.body;
   const isFormData = body instanceof FormData;
   const isJson = typeof body === 'string';
   return $.ajax({
     url: url,
     method: options.method || 'GET',
     data: isJson ? body : body,
     dataType: 'json',
     processData: !isFormData,
     contentType: isFormData ? false : (isJson ? 'application/json; charset=UTF-8' : undefined),
     headers: {'X-CSRF-TOKEN': token, 'Accept': 'application/json', ...(options.headers || {})}
   }).then(data => data, xhr => {
     const error = new Error(xhr.responseJSON?.message || 'Request failed (' + xhr.status + ')');
     error.status = xhr.status;
     error.response = xhr.responseJSON;
     throw error;
   });
 }
 async function loadConversations(snapshot = null) {
   const requestId = ++conversationSearchRequest;
   const phone = conversationSearch ? conversationSearch.value.trim() : '';
   const query = phone ? '?phone='+encodeURIComponent(phone) : '';
   const result = snapshot && !phone ? snapshot : await request(root+'/conversations'+query);
   if (requestId !== conversationSearchRequest) return;
   $(conversations).empty();
   const items = Array.isArray(result.data) ? result.data : [];
   if (!items.length) {
     const empty = document.createElement('div');
     empty.className = 'list-group-item text-muted small py-3';
     empty.textContent = phone ? 'No conversations found for this phone number.' : 'No conversations yet.';
     $(conversations).append(empty);
     return;
   }
   items.forEach(c => {
     const button = document.createElement('button');
     button.type='button'; button.className='list-group-item list-group-item-action '+(active===c.id?'active':'');
     button.innerHTML='<div class="d-flex justify-content-between gap-2"><span class="fw-bold">'+esc(c.user?.name || ('User #'+c.id))+'</span>'+(c.unread_count ? '<span class="badge bg-danger">'+c.unread_count+'</span>' : '')+'</div><small>'+esc(c.user?.phone || '')+'</small><div class="small text-muted text-truncate">'+esc(c.last_message_preview || 'No messages yet')+'</div>';
     button.onclick=()=>selectConversation(c);
     $(conversations).append(button);
   });
 async function searchUsers() {
   const requestId = ++userSearchRequest;
   const term = userSearch ? userSearch.value.trim() : '';
   $(userSearchResults).empty();
   if (!term) {
     const empty = document.createElement('div');
     empty.className = 'list-group-item small text-muted';
     empty.textContent = 'Search by name, phone number or user ID.';
     $(userSearchResults).append(empty);
     return;
   }
   const loading = document.createElement('div');
   loading.className = 'list-group-item small text-muted';
   loading.textContent = 'Searching registered users…';
   $(userSearchResults).append(loading);
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
       $(button).on('click.adminChat', async () => {
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
       $(userSearchResults).append(button);
     });
   } catch (error) {
     if (requestId !== userSearchRequest) return;
     userSearchResults.replaceChildren();
     const failure = document.createElement('div');
     failure.className = 'list-group-item small text-danger';
     failure.textContent = error.message || 'User search failed. Please retry.';
     $(userSearchResults).append(failure);
   }
 }
 if (userSearch) {
   $(userSearch).on('input.adminChat', () => {
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
 $(window).on('admin:support-message.adminChat', (event, payloadArg) => {
   const payload = event.originalEvent?.detail || event.detail || payloadArg || {};
   if (active && Number(payload.conversation_id) === Number(active)) {
     void loadMessages(false);
   }
 });
 $(window).on('admin:conversations-snapshot.adminChat', (event, snapshotArg) => {
   if (conversationSearch?.value.trim()) return;
   void loadConversations(event.originalEvent?.detail || event.detail || snapshotArg || null).catch(error => console.warn('Realtime inbox snapshot failed', error));
 });
 function notifyAdmin(message) {
   window.dispatchEvent(new CustomEvent('admin:incoming-chat-message', { detail: { ...message, sender: 'user' } }));
   if ('Notification' in window && Notification.permission === 'granted') {
     const notification = new Notification('New support message', {body: message.body || 'User sent an attachment.'});
     notification.onclick = () => window.focus();
   }
 }
 async function selectConversation(c) {
   active=c.id; lastId=0; conversationVersion += 1;
   document.getElementById('chat-title').textContent=(c.user?.name || 'User')+' · '+(c.user?.phone || '');
   await loadMessages(true); await loadConversations();
 }
 async function loadMessages(reset=false) {
   if (!active) return;
   if (busy) {
     pendingMessageRefresh = true;
     pendingMessageReset = pendingMessageReset || reset;
     return;
   }
   busy=true;
   const requestedConversation = active;
   const requestedVersion = conversationVersion;
   try {
     const wasNearBottom = messages.scrollHeight - messages.scrollTop - messages.clientHeight < 100;
     const query = !reset ? '?after_id='+encodeURIComponent(String(lastId)) : '';
     const result=await request(root+'/'+requestedConversation+'/messages'+query);
     if (requestedVersion !== conversationVersion || requestedConversation !== active) return;
     const all=result.data || [];
     window.dispatchEvent(new Event('admin:refresh-chat-unread'));
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
     if (reset || $(messages).find('p.text-muted.text-center').length > 0) {
       $(messages).html(rendered || '<p class="text-muted text-center my-auto">No messages yet. Send the first reply.</p>');
     } else if (rendered) {
       $(messages).append(rendered);
     }
     if (all.length) lastId=Math.max(lastId,...all.map(m=>Number(m.id)||0));
     if (!reset && all.length >= 200) pendingMessageRefresh = true;
     if (reset || (rendered && wasNearBottom)) messages.scrollTop=messages.scrollHeight;
     status.textContent='Connected · refreshes automatically'; status.className='badge bg-success';
   } catch(e) {
     status.textContent='Reconnecting…'; status.className='badge bg-secondary';
     console.warn('Support chat sync failed',e);
   } finally {
     busy=false;
     const shouldRefresh = pendingMessageRefresh && !!active;
     const shouldReset = pendingMessageReset;
     pendingMessageRefresh = false;
     pendingMessageReset = false;
     if (shouldRefresh) void loadMessages(shouldReset);
   }
 }
 if (conversationSearch) {
   $(conversationSearch).on('input.adminChat', () => {
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
  $('#enable-desktop-alerts').on('click.adminChat', async () => {
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
 $('#disable-admin-push').on('click.adminChat', async () => {
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
 $(recordButton).on('click.adminChat', async () => {
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
 $('#attachment').on('change.adminChat',()=>{recordedFile=null;recordingStatus.classList.add('d-none');});
 $('#send-form').on('submit.adminChat', async event=>{
   event.preventDefault(); if(!active) return alert('Select a conversation first.');
   const body=document.getElementById('body'), file=document.getElementById('attachment');
   if(!body.value.trim()&&!file.files.length&&!recordedFile) return;
   if (recorder && recorder.state === 'recording') return alert('Stop recording before sending.');
   const data=new FormData(); if(body.value.trim()) data.append('body',body.value.trim());
   const attachment = recordedFile || file.files[0]; if(attachment) data.append('attachment',attachment);
   try { await request(root+'/'+active+'/send',{method:'POST',body:data}); body.value=''; file.value=''; recordedFile=null; recordingStatus.classList.add('d-none'); await loadMessages(true); await loadConversations(); }
   catch(error){ alert(error.message); }
 });
 $(window).on('admin:broadcast-state.adminChat', (event, stateArg) => {
   const state = event.originalEvent?.detail || event.detail || stateArg || {};
   const connected = state.current === 'connected';
   realtimeConnected = connected;
   if (connected) {
     if (active) void loadMessages();
     status.textContent = 'Live · Pusher';
     status.className = 'badge bg-success';
   } else {
     status.textContent = 'Reconnecting…';
     status.className = 'badge bg-secondary';
   }
 });
 $(window).on('admin:recovery-tick.adminChat', () => {
   if (!realtimeConnected && active) void loadMessages(false);
 });
 loadConversations().then(()=>{
   if (window.adminPusher?.connection?.state === 'connected') realtimeConnected = true;
   status.textContent = realtimeConnected ? 'Live · Pusher' : 'Connected · recovery sync';
   status.className = 'badge bg-success';
 }).catch(()=>{status.textContent='Reconnecting…';status.className='badge bg-secondary';});
 $(window).one('beforeunload.adminChat',()=>{ stream?.getTracks().forEach(t=>t.stop()); });
})();
</script>
@endsection
