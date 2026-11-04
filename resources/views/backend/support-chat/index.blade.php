@extends('backend.include.layout')

@section('content')
<style>
    .wa-page{padding:1rem 0 1.5rem}
    .wa-shell{height:calc(100vh - 190px);min-height:570px;max-height:900px;display:flex;overflow:hidden;background:#fff;border:1px solid #dfe3e6;border-radius:12px;box-shadow:0 8px 28px rgba(15,23,42,.07)}
    .wa-sidebar{width:360px;min-width:310px;max-width:38%;display:flex;flex-direction:column;border-right:1px solid #e9edef;background:#fff}
    .wa-side-head{padding:18px 18px 12px;background:#f0f2f5}
    .wa-brand-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:15px}
    .wa-brand{font-size:1.2rem;font-weight:700;color:#111b21;margin:0}
    .wa-search-wrap{position:relative}
    .wa-search{height:42px;padding-left:40px;border:0;border-radius:9px;background:#fff;font-size:.9rem;box-shadow:none!important}
    .wa-search-icon{position:absolute;left:14px;top:13px;color:#667781;z-index:2}
    .wa-search-clear{position:absolute;right:10px;top:8px;border:0;background:transparent;color:#667781;padding:3px 7px}
    .wa-search-results{display:none;max-height:270px;overflow:auto;background:#fff;border:1px solid #e9edef;border-radius:9px;margin-top:8px;box-shadow:0 8px 24px rgba(15,23,42,.10)}
    .wa-search-results.is-visible{display:block}
    .wa-search-result{width:100%;border:0;border-bottom:1px solid #f0f2f5;background:#fff;text-align:left;padding:11px 13px;display:block}
    .wa-search-result:last-child{border-bottom:0}
    .wa-search-result:hover,.wa-search-result:focus{background:#f5f7f8}
    .wa-search-result strong{display:block;color:#111b21;font-size:.9rem}
    .wa-search-result small{color:#667781}
    .wa-section-label{padding:13px 18px 8px;color:#667781;font-size:.75rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase}
    .wa-contact-list{flex:1;min-height:0;overflow-y:auto}
    .wa-contact{width:100%;display:flex;align-items:center;gap:12px;text-align:left;border:0;border-bottom:1px solid #f0f2f5;background:#fff;padding:12px 16px;transition:background .15s}
    .wa-contact:hover{background:#f5f6f6}
    .wa-contact.is-active{background:#f0f2f5}
    .wa-avatar{width:46px;height:46px;flex:0 0 46px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#dfe7e9;color:#43545a;font-weight:700;font-size:1rem;text-transform:uppercase}
    .wa-contact-copy{min-width:0;flex:1}
    .wa-contact-top,.wa-contact-bottom{display:flex;align-items:center;justify-content:space-between;gap:8px}
    .wa-contact-name{font-size:.93rem;font-weight:600;color:#111b21;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .wa-contact-time{font-size:.7rem;color:#667781;white-space:nowrap}
    .wa-contact-preview{font-size:.78rem;color:#667781;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:3px}
    .wa-unread{min-width:19px;height:19px;padding:0 5px;border-radius:20px;background:#25d366;color:#fff;font-size:.68rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center}
    .wa-main{min-width:0;flex:1;display:flex;flex-direction:column;background:#efeae2}
    .wa-chat-head{height:68px;flex:0 0 68px;display:flex;align-items:center;gap:12px;padding:10px 18px;background:#f0f2f5;border-bottom:1px solid #e9edef}
    .wa-chat-person{min-width:0;flex:1}
    .wa-chat-name{font-size:.98rem;font-weight:600;color:#111b21;margin:0}
    .wa-chat-meta{font-size:.75rem;color:#667781;margin-top:2px}
    .wa-chat-actions{display:flex;align-items:center;gap:8px}
    .wa-icon-btn{width:36px;height:36px;border:0;border-radius:50%;background:transparent;color:#54656d;display:inline-flex;align-items:center;justify-content:center}
    .wa-icon-btn:hover{background:#e4e7e9}
    .wa-back{display:none}
    .wa-messages{flex:1;min-height:0;overflow-y:auto;padding:22px clamp(14px,3vw,52px);display:flex;flex-direction:column;gap:7px;background-color:#efeae2;background-image:radial-gradient(rgba(15,23,42,.035) .7px,transparent .7px);background-size:14px 14px}
    .wa-date-divider{align-self:center;margin:8px 0 12px;padding:6px 12px;border-radius:8px;background:#fff;color:#54656d;font-size:.72rem;box-shadow:0 1px 1px rgba(0,0,0,.06)}
    .wa-message-row{width:100%;display:flex}
    .wa-message-row.is-outgoing{justify-content:flex-end}
    .wa-bubble{position:relative;max-width:min(76%,620px);min-width:95px;padding:7px 10px 5px;border-radius:8px;background:#fff;color:#111b21;box-shadow:0 1px 1px rgba(0,0,0,.08);overflow-wrap:anywhere}
    .wa-message-row.is-incoming .wa-bubble{border-top-left-radius:2px}
    .wa-message-row.is-outgoing .wa-bubble{background:#d9fdd3;border-top-right-radius:2px}
    .wa-message-body{font-size:.9rem;line-height:1.42;white-space:pre-wrap}
    .wa-message-meta{display:flex;align-items:center;justify-content:flex-end;gap:4px;margin:3px 0 0 12px;min-height:14px}
    .wa-message-time{font-size:.66rem;color:#667781;white-space:nowrap}
    .wa-ticks{font-size:.9rem;line-height:1;color:#8696a0;letter-spacing:-.28em;padding-right:3px}
    .wa-ticks.is-read{color:#34b7f1}
    .wa-attachment{display:block;max-width:min(330px,65vw);max-height:280px;border-radius:6px;margin:3px 0}
    .wa-file-link{color:#027eb5;text-decoration:none;font-size:.85rem}
    .wa-composer{display:flex;align-items:flex-end;gap:9px;padding:12px 16px;background:#f0f2f5}
    .wa-composer .form-control{border:0;border-radius:9px;padding:11px 14px;resize:none;box-shadow:none!important;max-height:120px}
    .wa-send{height:42px;width:44px;flex:0 0 44px;border:0;border-radius:50%;background:#00a884;color:#fff}
    .wa-send:disabled{opacity:.55}
    .wa-attach-label{width:38px;height:40px;display:flex;align-items:center;justify-content:center;color:#54656d;cursor:pointer;font-size:1.15rem}
    .wa-attach-label input{display:none}
    .wa-record{height:40px;width:38px;border:0;background:transparent;color:#54656d}
    .wa-empty{margin:auto;text-align:center;color:#667781;max-width:340px;padding:24px}
    .wa-empty i{font-size:3rem;color:#aebac1;margin-bottom:15px}
    .wa-empty h5{font-size:1.15rem;color:#41525a}
    .wa-connection{font-size:.72rem;color:#667781}
    .wa-composer-preview{display:flex;align-items:center;gap:12px;margin:0;padding:10px 14px;background:#f0f2f5;border-top:1px solid #e5e9eb}
    .wa-composer-preview.wa-hidden{display:none!important}
    .wa-preview-thumb{width:54px;height:54px;flex:0 0 54px;object-fit:cover;border-radius:8px;background:#e3e8eb}
    .wa-preview-file-icon{width:54px;height:54px;flex:0 0 54px;display:flex;align-items:center;justify-content:center;border-radius:8px;background:#e3e8eb;color:#54656d;font-size:1.35rem}
    .wa-preview-copy{min-width:0;flex:1}
    .wa-preview-name{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#263238;font-size:.82rem;font-weight:600}
    .wa-preview-meta{display:block;color:#667781;font-size:.72rem;margin-top:2px}
    .wa-preview-play{width:38px;height:38px;flex:0 0 38px;border:0;border-radius:50%;background:#fff;color:#54656d}
    .wa-preview-remove{width:34px;height:34px;flex:0 0 34px;border:0;border-radius:50%;background:transparent;color:#667781}
    .wa-preview-remove:hover{background:#e2e7e9;color:#dc3545}
    .wa-hidden{display:none!important}
    @media(max-width:991.98px){.wa-sidebar{width:320px;min-width:280px}.wa-bubble{max-width:88%}}
    @media(max-width:767.98px){
        .wa-page{padding:.25rem 0 1rem}
        .wa-shell{height:calc(100dvh - 155px);min-height:470px;max-height:none;border-radius:8px}
        .wa-sidebar{width:100%;max-width:none;min-width:0;border-right:0}
        .wa-main{display:none;width:100%}
        .wa-shell.has-active-chat .wa-sidebar{display:none}
        .wa-shell.has-active-chat .wa-main{display:flex}
        .wa-back{display:inline-flex}
        .wa-chat-head{height:60px;flex-basis:60px;padding:8px 10px}
        .wa-messages{padding:15px 10px}
        .wa-bubble{max-width:90%}
        .wa-composer{padding:8px;gap:5px}
        .wa-attach-label,.wa-record{width:32px}
        .wa-composer .form-control{font-size:.9rem;padding:10px}
    }
</style>

<div class="container-fluid px-2 px-lg-3 wa-page">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h2 class="fw-bold mb-1">Live Support Chat</h2>
            <p class="text-muted small mb-0">Manage private conversations with your users.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="wa-connection" id="connection-status">Connecting…</span>
            <button type="button" class="btn btn-sm btn-outline-primary" id="enable-desktop-alerts"><i class="fas fa-bell me-1"></i>Enable alerts</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="disable-admin-push"><i class="fas fa-bell-slash me-1"></i>Disable push</button>
        </div>
    </div>

    <div class="wa-shell" id="wa-shell">
        <aside class="wa-sidebar">
            <div class="wa-side-head">
                <div class="wa-brand-row">
                    <h3 class="wa-brand">Chats</h3>
                    <span class="small text-muted" id="conversation-count"></span>
                </div>
                <div class="wa-search-wrap">
                    <i class="fas fa-search wa-search-icon"></i>
                    <input id="user-search" type="search" class="form-control wa-search" placeholder="Search or start a new chat" autocomplete="off" aria-label="Search users">
                    <button type="button" id="clear-user-search" class="wa-search-clear wa-hidden" aria-label="Clear search"><i class="fas fa-times"></i></button>
                </div>
                <div id="user-search-results" class="wa-search-results" aria-live="polite"></div>
            </div>
            <div class="wa-section-label">Recent conversations</div>
            <div id="conversations" class="wa-contact-list" aria-live="polite">
                <div class="wa-empty"><i class="fas fa-comments"></i><div>Loading conversations…</div></div>
            </div>
        </aside>

        <section class="wa-main" aria-label="Conversation">
            <header class="wa-chat-head">
                <button type="button" class="wa-icon-btn wa-back" id="back-to-chats" aria-label="Back to chats"><i class="fas fa-arrow-left"></i></button>
                <div class="wa-avatar" id="chat-avatar">?</div>
                <div class="wa-chat-person">
                    <h4 class="wa-chat-name" id="chat-name">Select a conversation</h4>
                    <div class="wa-chat-meta" id="chat-meta">Your private support inbox</div>
                </div>
                <div class="wa-chat-actions"><span class="small text-muted d-none d-md-inline">Private support conversation</span></div>
            </header>
            <div id="messages" class="wa-messages">
                <div class="wa-empty"><i class="fas fa-comments"></i><h5>Welcome to Live Support</h5><p class="mb-0">Choose a recent conversation or search for any registered user to start chatting.</p></div>
            </div>
            <div id="composer-preview" class="wa-composer-preview wa-hidden" aria-live="polite"></div>
            <form id="send-form" class="wa-composer">
                <label class="wa-attach-label mb-0" title="Attach a file"><i class="fas fa-paperclip"></i><input id="attachment" type="file" accept="image/*,video/mp4,video/webm,audio/*"></label>
                <button id="record-voice" type="button" class="wa-record" title="Record voice message"><i class="fas fa-microphone"></i></button>
                <textarea id="body" class="form-control" rows="1" maxlength="5000" placeholder="Type a message" aria-label="Reply message"></textarea>
                <button class="wa-send" type="submit" aria-label="Send message" disabled><i class="fas fa-paper-plane"></i></button>
            </form>
        </section>
    </div>
</div>
@endsection

@section('script')
<script src="/firebase-config.js"></script>
<script src="https://www.gstatic.com/firebasejs/11.10.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/11.10.0/firebase-messaging-compat.js"></script>
<script>
(function($, window, document) {
    'use strict';
    $(function() {
        var root = @json(url('/admin/support-chat'));
        var csrfToken = $('meta[name="csrf-token"]').attr('content') || '';
        var active = null, lastId = 0, busy = false, conversationVersion = 0;
        var pendingRefresh = false, pendingReset = false, realtimeConnected = false;
        var searchTimer = null, searchRequestId = 0, conversationRequestId = 0;
        var recordedFile = null, recorder = null, mediaStream = null, chunks = [];
        var previewObjectUrl = null, recordingStartedAt = 0;
        var $shell = $('#wa-shell'), $contacts = $('#conversations'), $messages = $('#messages');
        var $search = $('#user-search'), $searchResults = $('#user-search-results');
        var $sendButton = $('#send-form button[type="submit"]');
        var initialConversationOpened = false;

        function api(url, options) {
            options = options || {};
            var data = options.data, form = data instanceof FormData, json = typeof data === 'string';
            var settings = {url:url, type:options.method || 'GET', dataType:'json',
                headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken}};
            if (data !== undefined) settings.data = data;
            if (form) { settings.processData = false; settings.contentType = false; }
            if (json) { settings.processData = false; settings.contentType = 'application/json; charset=UTF-8'; }
            return $.ajax(settings);
        }
        function errorText(xhr, fallback) {
            return xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message :
                (fallback || 'Request failed.') + (xhr && xhr.status ? ' (HTTP ' + xhr.status + ')' : '');
        }
        function setConnection(text, connected) {
            $('#connection-status').text(text).toggleClass('text-success',!!connected).toggleClass('text-danger',!connected);
        }
        function initials(name) {
            var parts = String(name || 'U').trim().split(/\s+/);
            return (parts.length > 1 ? parts[0].charAt(0) + parts[1].charAt(0) : parts[0].slice(0,2)).toUpperCase();
        }
        function formatTime(value) {
            if (!value) return '';
            var date = new Date(value);
            if (isNaN(date.getTime())) return '';
            return date.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
        }
        function dateKey(value) {
            var date = new Date(value);
            if (isNaN(date.getTime())) return 'unknown';
            return date.getFullYear() + '-' + (date.getMonth()+1) + '-' + date.getDate();
        }
        function dateLabel(value) {
            var date = new Date(value), today = new Date(), yesterday = new Date();
            if (isNaN(date.getTime())) return 'Date unavailable';
            today.setHours(0,0,0,0); yesterday.setDate(yesterday.getDate()-1); yesterday.setHours(0,0,0,0);
            var day = new Date(date.getFullYear(),date.getMonth(),date.getDate());
            if (day.getTime() === today.getTime()) return 'Today';
            if (day.getTime() === yesterday.getTime()) return 'Yesterday';
            return date.toLocaleDateString([], {day:'numeric',month:'long',year:'numeric'});
        }
        function renderDateDivider(value) {
            return $('<div>',{class:'wa-date-divider','data-date-key':dateKey(value)}).text(dateLabel(value));
        }
        function showEmpty($target, icon, title, detail) {
            $target.empty().append($('<div>',{class:'wa-empty'})
                .append($('<i>',{class:'fas '+icon}))
                .append($('<h5>').text(title))
                .append($('<p>',{class:'mb-0'}).text(detail)));
        }
        function updateSendButton() {
            var hasBody = !!$.trim($('#body').val() || '');
            var input = $('#attachment').get(0);
            var hasFile = !!(input && input.files && input.files.length) || !!recordedFile;
            $sendButton.prop('disabled', !active || (!hasBody && !hasFile));
        }

        function formatDuration(seconds) {
            seconds = Math.max(0, Math.floor(Number(seconds) || 0));
            var minutes = Math.floor(seconds / 60);
            return String(minutes).padStart(2, '0') + ':' + String(seconds % 60).padStart(2, '0');
        }

        function clearComposerAttachment() {
            if (previewObjectUrl) {
                URL.revokeObjectURL(previewObjectUrl);
                previewObjectUrl = null;
            }
            recordedFile = null;
            $('#attachment').val('');
            $('#composer-preview').empty().addClass('wa-hidden');
            updateSendButton();
        }

        function renderComposerPreview(file, isVoiceRecording) {
            var $preview = $('#composer-preview').empty();
            if (!file) { $preview.addClass('wa-hidden'); updateSendButton(); return; }
            if (previewObjectUrl) URL.revokeObjectURL(previewObjectUrl);
            previewObjectUrl = URL.createObjectURL(file);
            var mime = String(file.type || ''), name = file.name || (isVoiceRecording ? 'Voice message' : 'Attachment');
            var $copy = $('<div>', {class:'wa-preview-copy'});
            var $remove = $('<button>', {type:'button',class:'wa-preview-remove',title:'Remove attachment','aria-label':'Remove attachment'})
                .append($('<i>',{class:'fas fa-times'}));
            $remove.on('click.adminChat', clearComposerAttachment);

            if (isVoiceRecording) {
                var audio = new Audio(previewObjectUrl);
                var $play = $('<button>',{type:'button',class:'wa-preview-play','aria-label':'Play voice recording'})
                    .append($('<i>',{class:'fas fa-play'}));
                var $duration = $('<span>',{class:'wa-preview-meta'}).text('Loading duration…');
                $copy.append($('<span>',{class:'wa-preview-name'}).text('Voice message'));
                $copy.append($duration);
                $play.on('click.adminChat',function(){
                    if (audio.paused) audio.play().catch(function(){ $duration.text('Unable to play recording'); });
                    else audio.pause();
                });
                $(audio).on('loadedmetadata.adminChat timeupdate.adminChat',function(){
                    var total = Number.isFinite(audio.duration) ? audio.duration : 0;
                    $duration.text(formatDuration(audio.currentTime) + ' / ' + formatDuration(total));
                }).on('play.adminChat',function(){ $play.html('<i class="fas fa-pause"></i>').attr('aria-label','Pause voice recording'); })
                  .on('pause.adminChat ended.adminChat',function(){ $play.html('<i class="fas fa-play"></i>').attr('aria-label','Play voice recording'); });
                $preview.append($play,$copy,$remove).removeClass('wa-hidden');
            } else {
                var $visual;
                if (mime.indexOf('image/') === 0) {
                    $visual=$('<img>',{src:previewObjectUrl,alt:name,class:'wa-preview-thumb'});
                } else if (mime.indexOf('video/') === 0) {
                    $visual=$('<video>',{src:previewObjectUrl,controls:true,preload:'metadata',class:'wa-preview-thumb'}).attr('controlsList','nodownload');
                } else if (mime.indexOf('audio/') === 0) {
                    $visual=$('<div>',{class:'wa-preview-file-icon'}).append($('<i>',{class:'fas fa-file-audio'}));
                    $copy.append($('<audio>',{src:previewObjectUrl,controls:true,preload:'metadata',class:'w-100 mt-1'}).attr('controlsList','nodownload'));
                } else {
                    $visual=$('<div>',{class:'wa-preview-file-icon'}).append($('<i>',{class:'fas fa-file'}));
                }
                $copy.prepend($('<span>',{class:'wa-preview-name'}).text(name));
                if (!mime.startsWith('audio/')) $copy.append($('<span>',{class:'wa-preview-meta'}).text((file.size/1024).toFixed(1)+' KB · Ready to send'));
                $preview.append($visual,$copy,$remove).removeClass('wa-hidden');
            }
            updateSendButton();
        }

        function renderContacts(items) {
            $contacts.empty();
            $('#conversation-count').text(items.length ? items.length + ' chats' : '');
            if (!items.length) {
                showEmpty($contacts,'fa-comments','No conversations yet','Search for a registered user above to start a new conversation.');
                return;
            }
            $.each(items,function(_, item) {
                var user = item.user || {}, name = user.name || 'User #' + item.id;
                var $button = $('<button>',{type:'button',class:'wa-contact'+(Number(active)===Number(item.id)?' is-active':'')});
                $button.append($('<div>',{class:'wa-avatar'}).text(initials(name)));
                var $copy = $('<div>',{class:'wa-contact-copy'});
                var $top = $('<div>',{class:'wa-contact-top'});
                $top.append($('<span>',{class:'wa-contact-name'}).text(name));
                $top.append($('<span>',{class:'wa-contact-time'}).text(formatTime(item.last_message_at)));
                var $bottom = $('<div>',{class:'wa-contact-bottom'});
                $bottom.append($('<div>',{class:'wa-contact-preview'}).text(item.last_message_preview || 'Start a conversation'));
                if (Number(item.unread_count)>0) $bottom.append($('<span>',{class:'wa-unread'}).text(item.unread_count>99?'99+':item.unread_count));
                $copy.append($top,$bottom); $button.append($copy);
                $button.on('click.adminChat',function(){ selectConversation(item); });
                $contacts.append($button);
            });
        }

        function loadConversations() {
            var requestId = ++conversationRequestId;
            api(root+'/conversations').done(function(result) {
                if (requestId !== conversationRequestId) return;
                renderContacts($.isArray(result.data)?result.data:[]);
                if (!initialConversationOpened) {
                    var requested = new URLSearchParams(window.location.search).get('conversation_id');
                    if (requested) {
                        var match = (result.data || []).find(function(item){return Number(item.id)===Number(requested);});
                        if (match) { initialConversationOpened = true; selectConversation(match); }
                    }
                }
            }).fail(function(xhr) {
                if (requestId !== conversationRequestId) return;
                showEmpty($contacts,'fa-triangle-exclamation','Unable to load conversations',errorText(xhr,'Please refresh and try again.'));
                setConnection('Inbox unavailable',false);
            });
        }

        function renderSearchResults(users) {
            $searchResults.empty().addClass('is-visible');
            if (!users.length) {
                $searchResults.append($('<div>',{class:'p-3 small text-muted'}).text('No registered users found.'));
                return;
            }
            $.each(users,function(_,user) {
                var name = user.name || 'User #'+user.id;
                var $button = $('<button>',{type:'button',class:'wa-search-result'});
                $button.append($('<strong>').text(name));
                $button.append($('<small>').text((user.phone || 'No phone')+' · User #'+user.id+(user.has_conversation?' · Existing chat':' · Start chat')));
                $button.on('click.adminChat',function(){
                    $button.prop('disabled',true);
                    api(root+'/conversations',{method:'POST',data:JSON.stringify({user_id:user.id})})
                        .done(function(response){
                            var conversation={id:response.data.id,user:response.data.user || {id:user.id,name:user.name,phone:user.phone}};
                            $search.val(''); $searchResults.removeClass('is-visible').empty();
                            $('#clear-user-search').addClass('wa-hidden');
                            loadConversations(); selectConversation(conversation);
                        }).fail(function(xhr){window.alert(errorText(xhr,'Unable to open conversation.'));})
                        .always(function(){$button.prop('disabled',false);});
                });
                $searchResults.append($button);
            });
        }
        function searchUsers() {
            var term = $.trim($search.val() || ''), requestId = ++searchRequestId;
            $('#clear-user-search').toggleClass('wa-hidden',!term);
            if (!term) { $searchResults.removeClass('is-visible').empty(); loadConversations(); return; }
            $searchResults.addClass('is-visible').empty().append($('<div>',{class:'p-3 small text-muted'}).text('Searching users…'));
            api(root+'/users?q='+encodeURIComponent(term)).done(function(result){
                if (requestId !== searchRequestId || $.trim($search.val() || '') !== term) return;
                renderSearchResults($.isArray(result.data)?result.data:[]);
            }).fail(function(xhr){
                if (requestId !== searchRequestId) return;
                $searchResults.empty().append($('<div>',{class:'p-3 small text-danger'}).text(errorText(xhr,'User search failed.')));
            });
        }
        $search.on('input.adminChat',function(){clearTimeout(searchTimer);searchTimer=window.setTimeout(searchUsers,250);});
        $('#clear-user-search').on('click.adminChat',function(){$search.val('').trigger('input').trigger('focus');});
        $(document).on('click.adminChat',function(event){
            if (!$(event.target).closest('.wa-search-wrap').length) $searchResults.removeClass('is-visible');
        });
        $search.on('focus.adminChat',function(){if($.trim($search.val()))$searchResults.addClass('is-visible');});

        function selectConversation(conversation) {
            active = Number(conversation.id); lastId = 0; conversationVersion++;
            updateSendButton();
            $shell.addClass('has-active-chat');
            var user = conversation.user || {}, name = user.name || 'User';
            $('#chat-name').text(name); $('#chat-meta').text(user.phone || 'Support conversation');
            $('#chat-avatar').text(initials(name));
            $messages.empty().append($('<div>',{class:'wa-empty'}).text('Loading messages…'));
            loadMessages(true); loadConversations();
        }
        $('#back-to-chats').on('click.adminChat',function(){$shell.removeClass('has-active-chat');});

        function renderMessage(message) {
            var outgoing = message.sender === 'admin';
            var $row = $('<div>',{class:'wa-message-row '+(outgoing?'is-outgoing':'is-incoming')});
            var $bubble = $('<div>',{class:'wa-bubble'});
            if (message.body) $bubble.append($('<div>',{class:'wa-message-body'}).text(message.body));
            if (message.media_url) {
                var mime = String(message.media_mime || ''), url = String(message.media_url);
                if (mime.indexOf('image/')===0) $bubble.append($('<a>',{href:url,target:'_blank',rel:'noopener noreferrer'}).append($('<img>',{src:url,alt:message.media_name||'Image attachment',class:'wa-attachment'})));
                else if (mime.indexOf('video/')===0) $bubble.append($('<video>',{src:url,controls:true,playsinline:true,class:'wa-attachment'}));
                else if (mime.indexOf('audio/')===0) $bubble.append($('<audio>',{src:url,controls:true,preload:'metadata',class:'w-100'}).attr('controlsList','nodownload'));
                else $bubble.append($('<a>',{href:url,target:'_blank',rel:'noopener noreferrer',class:'wa-file-link'}).text(message.media_name || 'Open attachment'));
            }
            var $meta = $('<div>',{class:'wa-message-meta'});
            if (outgoing) {
                // The existing schema exposes read_at, but no separate recipient-delivery receipt.
                // Show one sent tick until the recipient opens the chat, then the blue read ticks.
                var read = !!message.read_at;
                $meta.append($('<span>',{class:'wa-ticks'+(read?' is-read':'') ,title:read?'Read':'Sent'}).text(read?'✓✓':'✓'));
            }
            $meta.append($('<span>',{class:'wa-message-time'}).text(formatTime(message.created_at)));
            $bubble.append($meta); $row.append($bubble); return $row;
        }

        function loadMessages(reset) {
            if (!active) return;
            if (busy) {pendingRefresh=true;pendingReset=pendingReset||!!reset;return;}
            busy=true;
            var requested=active, version=conversationVersion, element=$messages.get(0);
            var nearBottom=element ? (element.scrollHeight-$messages.scrollTop()-$messages.innerHeight()<110) : true;
            var url=root+'/'+requested+'/messages'+(reset?'':'?after_id='+encodeURIComponent(lastId));
            api(url).done(function(result){
                if (version!==conversationVersion || Number(active)!==Number(requested)) return;
                var messages=$.isArray(result.data)?result.data:[];
                if (messages.length && !reset) $messages.children('.wa-empty').remove();
                var previous=lastId;
                $(window).trigger('admin:refresh-chat-unread');
                if (!reset && previous>0) $.each(messages,function(_,message){if(message.sender==='user' && Number(message.id)>previous) notifyAdmin(message);});
                if (reset) $messages.empty();
                if (!messages.length && reset) showEmpty($messages,'fa-comment-dots','No messages yet','Send the first reply to this user.');
                $.each(messages,function(_,message){
                    var key=dateKey(message.created_at);
                    var $lastDivider=$messages.children('.wa-date-divider').last();
                    if (!$lastDivider.length || $lastDivider.attr('data-date-key')!==key) $messages.append(renderDateDivider(message.created_at));
                    $messages.append(renderMessage(message));
                    lastId=Math.max(lastId,Number(message.id)||0);
                });
                if (!reset && messages.length>=200) pendingRefresh=true;
                if (reset || nearBottom) $messages.scrollTop(element.scrollHeight);
                setConnection(realtimeConnected?'Live · Pusher':'Connected',true);
            }).fail(function(xhr){setConnection('Reconnecting…',false);console.warn('Support chat sync failed:',errorText(xhr));})
            .always(function(){
                busy=false;
                var again=pendingRefresh, resetAgain=pendingReset;
                pendingRefresh=false;pendingReset=false;
                if(again && active) loadMessages(resetAgain);
            });
        }

        function notifyAdmin(message) {
            if ('Notification' in window && Notification.permission==='granted') {
                var notification=new Notification('New support message',{body:message.body||'User sent an attachment.'});
                notification.onclick=function(){window.focus();notification.close();};
            }
        }

        $('#body').on('input.adminChat',function(){
            this.style.height='auto';this.style.height=Math.min(this.scrollHeight,120)+'px';
            updateSendButton();
        });
        $('#attachment').on('change.adminChat',function(){
            recordedFile=null;
            var file=this.files && this.files[0];
            renderComposerPreview(file,false);
        });

        $('#send-form').on('submit.adminChat',function(event){
            event.preventDefault();
            if(!active)return;
            if(recorder && recorder.state==='recording'){window.alert('Stop recording before sending.');return;}
            var body=$.trim($('#body').val()||''), selected=$('#attachment').get(0).files[0], attachment=recordedFile||selected;
            if(!body && !attachment)return;
            var data=new FormData(); if(body)data.append('body',body); if(attachment)data.append('attachment',attachment);
            var conversationId=active; $sendButton.prop('disabled',true);
            api(root+'/'+conversationId+'/send',{method:'POST',data:data})
                .done(function(response){
                    if(Number(active)!==Number(conversationId))return;
                    $('#body').val('').css('height','auto');
                    clearComposerAttachment();
                    loadMessages(false); loadConversations();
                }).fail(function(xhr){window.alert(errorText(xhr,'Unable to send message.'));})
                .always(function(){$sendButton.prop('disabled',false);$('#body').trigger('input');});
        });

        $('#record-voice').on('click.adminChat',function(){
            if(recorder && recorder.state==='recording'){
                recorder.stop(); $(this).removeClass('text-danger').html('<i class="fas fa-microphone"></i>');
                $('#recording-status').addClass('wa-hidden'); return;
            }
            if(!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia || typeof MediaRecorder==='undefined'){
                window.alert('Voice recording is not supported by this browser.');return;
            }
            clearComposerAttachment();
            navigator.mediaDevices.getUserMedia({audio:true}).then(function(stream){
                mediaStream=stream; chunks=[]; recordingStartedAt=Date.now();
                var types=['audio/webm;codecs=opus','audio/webm','audio/mp4'], mime='';
                $.each(types,function(_,type){if(MediaRecorder.isTypeSupported && MediaRecorder.isTypeSupported(type)){mime=type;return false;}});
                recorder=mime?new MediaRecorder(stream,{mimeType:mime}):new MediaRecorder(stream);
                recorder.ondataavailable=function(event){if(event.data && event.data.size)chunks.push(event.data);};
                recorder.onstop=function(){
                    var type=recorder.mimeType||'audio/webm', ext=type.indexOf('mp4')!==-1?'mp4':'webm';
                    recordedFile=new File([new Blob(chunks,{type:type})],'voice-message.'+ext,{type:type});
                    renderComposerPreview(recordedFile,true);
                    if(mediaStream){mediaStream.getTracks().forEach(function(track){track.stop();});mediaStream=null;}
                };
                recorder.start(); $('#record-voice').addClass('text-danger').html('<i class="fas fa-stop"></i>');
            }).catch(function(){window.alert('Microphone permission was denied or unavailable.');});
        });

        $('#enable-desktop-alerts').on('click.adminChat',function(){
            if(!('Notification' in window)){window.alert('Notifications are not supported by this browser.');return;}
            Notification.requestPermission().then(function(permission){
                if(permission!=='granted'){window.alert('Please allow notifications in browser settings.');return;}
                if(!('serviceWorker' in navigator)||!window.firebase||!window.FIREBASE_CONFIG||!window.FIREBASE_VAPID_KEY){
                    window.alert('Desktop alerts are enabled while this tab is open, but background push configuration is unavailable.');return;
                }
                navigator.serviceWorker.register('/firebase-messaging-sw.js').then(function(){return navigator.serviceWorker.ready;})
                    .then(function(registration){
                        var app=window.firebase.apps.length?window.firebase.app():window.firebase.initializeApp(window.FIREBASE_CONFIG);
                        return window.firebase.messaging(app).getToken({vapidKey:window.FIREBASE_VAPID_KEY,serviceWorkerRegistration:registration});
                    }).then(function(token){
                        if(!token)throw new Error('Firebase did not return a push token.');
                        return api(root.replace(/\/support-chat\/?$/,'/push-subscribe'),{method:'POST',data:JSON.stringify({token:token,platform:'web'})});
                    }).then(function(){window.alert('Background push alerts enabled for this admin account.');})
                    .catch(function(error){window.alert('Push registration failed: '+(error.message||'Unknown error'));});
            });
        });
        $('#disable-admin-push').on('click.adminChat',function(){
            if(!window.firebase||!window.FIREBASE_CONFIG||!window.FIREBASE_VAPID_KEY||!('serviceWorker' in navigator)){
                window.alert('Firebase push configuration is unavailable.');return;
            }
            navigator.serviceWorker.getRegistration('/').then(function(registration){
                if(!registration)throw new Error('No admin push registration found.');
                var app=window.firebase.apps.length?window.firebase.app():window.firebase.initializeApp(window.FIREBASE_CONFIG);
                return window.firebase.messaging(app).getToken({vapidKey:window.FIREBASE_VAPID_KEY,serviceWorkerRegistration:registration});
            }).then(function(token){
                if(!token)throw new Error('No active push token found.');
                return api(root.replace(/\/support-chat\/?$/,'/push-subscribe'),{method:'DELETE',data:JSON.stringify({token:token})});
            }).then(function(){window.alert('Admin background push disabled. Browser permission remains unchanged.');})
            .catch(function(error){window.alert('Unable to disable admin push: '+(error.message||'Unknown error'));});
        });

        $(window).on('admin:support-message.adminChat',function(event,payload){
            payload=payload || (event.originalEvent && event.originalEvent.detail) || {};
            if(active && Number(payload.conversation_id)===Number(active)) loadMessages(false);
            loadConversations();
        });
        $(window).on('admin:broadcast-state.adminChat',function(event,state){
            state=state || (event.originalEvent && event.originalEvent.detail) || {};
            realtimeConnected=state.current==='connected';
            if(realtimeConnected){if(active)loadMessages(false);setConnection('Live · Pusher',true);}
            else setConnection('Reconnecting…',false);
        });
        $(window).on('admin:recovery-tick.adminChat',function(){if(!realtimeConnected && active)loadMessages(false);loadConversations();});
        if(window.adminPusher && window.adminPusher.connection && window.adminPusher.connection.state==='connected') realtimeConnected=true;

        loadConversations();
        if(realtimeConnected)setConnection('Live · Pusher',true);
        $(window).one('beforeunload.adminChat',function(){
            clearTimeout(searchTimer);
            if(recorder && recorder.state==='recording')recorder.stop();
            if(mediaStream)mediaStream.getTracks().forEach(function(track){track.stop();});
        });
    });
})(jQuery,window,document);
</script>
@endsection