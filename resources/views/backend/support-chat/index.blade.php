@extends('backend.include.layout')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div><h2 class="fw-bold mb-1">Live Support Chat</h2><p class="text-muted mb-0">Reply to users from separate, persisted conversations.</p></div>
        <span class="badge bg-success">Live updates · 3 seconds</span>
    </div>
    <div class="row g-3" style="min-height:65vh">
        <div class="col-md-4"><div class="card h-100"><div class="card-header fw-bold">Conversations</div><div id="conversations" class="list-group list-group-flush overflow-auto" style="max-height:65vh"></div></div></div>
        <div class="col-md-8"><div class="card h-100 d-flex flex-column"><div id="chat-title" class="card-header fw-bold">Select a conversation</div><div id="messages" class="card-body overflow-auto d-flex flex-column gap-2" style="height:50vh"></div><form id="send-form" class="card-footer d-flex gap-2 align-items-center"><input id="attachment" type="file" class="form-control" accept="image/*,video/mp4,video/webm,audio/*" style="max-width:220px"><input id="body" class="form-control" maxlength="5000" placeholder="Write a reply…"><button class="btn btn-warning" type="submit">Send</button></form></div></div>
    </div>
</div>
<script>
(() => {
 const root = @json(url('/admin/support-chat'));
 const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
 let active = null, lastId = 0, busy = false;
 const conversations = document.getElementById('conversations');
 const messages = document.getElementById('messages');
 const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
 async function request(url, options={}) {
   const response = await fetch(url, {credentials:'same-origin', ...options, headers:{'X-CSRF-TOKEN':token, 'Accept':'application/json', ...(options.headers||{})}});
   if (!response.ok) throw new Error('Request failed ('+response.status+')');
   return response.json();
 }
 async function loadConversations() {
   const result = await request(root+'/conversations');
   conversations.innerHTML = '';
   result.data.forEach(c => {
     const button = document.createElement('button');
     button.type='button'; button.className='list-group-item list-group-item-action '+(active===c.id?'active':'');
     button.innerHTML='<div class="fw-bold">'+esc(c.user?.name || ('User #'+c.id))+'</div><small>'+esc(c.user?.phone || '')+'</small>'+(c.unread_count ? ' <span class="badge bg-danger">'+c.unread_count+'</span>' : '');
     button.onclick=()=>selectConversation(c);
     conversations.appendChild(button);
   });
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
     const result=await request(root+'/'+active+'/messages');
     const all=result.data || [];
     if (reset) messages.innerHTML='';
     messages.innerHTML=all.map(m=>{
       const media=m.media_url ? (String(m.media_mime||'').startsWith('image/')?'<img alt="Attachment" src="'+esc(m.media_url)+'" style="max-width:220px;max-height:220px">':String(m.media_mime||'').startsWith('video/')?'<video controls src="'+esc(m.media_url)+'" style="max-width:260px"></video>':'<audio controls src="'+esc(m.media_url)+'"></audio>') : '';
       return '<div class="p-2 rounded border '+(m.sender==='admin'?'align-self-end bg-warning-subtle':'align-self-start bg-light')+'" style="max-width:85%">'+(m.body?'<div style="white-space:pre-wrap">'+esc(m.body)+'</div>':'')+media+'<small class="d-block text-muted mt-1">'+esc(m.created_at||'')+'</small></div>';
     }).join('');
     lastId=all.length?all[all.length-1].id:0;
     messages.scrollTop=messages.scrollHeight;
   } finally { busy=false; }
 }
 document.getElementById('send-form').addEventListener('submit', async event=>{
   event.preventDefault(); if(!active) return alert('Select a conversation first.');
   const body=document.getElementById('body'), file=document.getElementById('attachment');
   if(!body.value.trim()&&!file.files.length) return;
   const data=new FormData(); data.append('body',body.value); if(file.files[0]) data.append('attachment',file.files[0]);
   try { await request(root+'/'+active+'/send',{method:'POST',body:data}); body.value=''; file.value=''; await loadMessages(true); await loadConversations(); }
   catch(error){ alert(error.message); }
 });
 async function tick(){ try { await loadConversations(); if(active) await loadMessages(); } catch(e) { console.warn('Chat reconnect retry',e); } }
 loadConversations().catch(console.error); setInterval(tick,3000);
})();
</script>
@endsection
