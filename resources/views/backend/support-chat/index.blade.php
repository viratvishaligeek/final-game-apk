@extends('backend.include.layout')

@section('content')
    <style>
        .admin-chat-shell {
            min-height: 76vh;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 28px rgba(15, 23, 42, .06)
        }

        .admin-chat-sidebar {
            background: #fff;
            border-right: 1px solid #e5e7eb;
            min-height: 76vh
        }

        .admin-chat-sidebar .card-header {
            padding: 1rem;
            background: #fff
        }

        .admin-chat-inbox {
            max-height: calc(76vh - 220px);
            min-height: 260px;
            overflow-y: auto
        }

        .admin-chat-main {
            min-height: 76vh;
            background: #efeae2
        }

        .admin-chat-title {
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            padding: 1rem
        }

        #messages {
            height: calc(76vh - 150px) !important;
            min-height: 360px;
            background-color: #efeae2 !important;
            background-image: radial-gradient(rgba(15, 23, 42, .035) .7px, transparent .7px);
            background-size: 14px 14px
        }

        .admin-chat-bubble {
            max-width: min(78%, 560px);
            padding: .65rem .85rem;
            border-radius: 14px;
            box-shadow: 0 1px 1px rgba(15, 23, 42, .08);
            overflow-wrap: anywhere
        }

        .admin-chat-bubble--incoming {
            align-self: flex-start;
            background: #fff;
            border-top-left-radius: 4px
        }

        .admin-chat-bubble--outgoing {
            align-self: flex-end;
            background: #d9fdd3;
            border-top-right-radius: 4px
        }

        .admin-chat-search-results .list-group-item {
            border-left: 0;
            border-right: 0
        }

        .admin-chat-search-results .list-group-item:first-child {
            border-top: 0
        }

        .admin-chat-search-results .list-group-item:last-child {
            border-bottom: 0
        }

        @media(max-width:767.98px) {
            .admin-chat-shell {
                min-height: auto;
                border-radius: 12px
            }

            .admin-chat-sidebar,
            .admin-chat-main {
                min-height: 0
            }

            .admin-chat-inbox {
                max-height: 260px;
                min-height: 120px
            }

            #messages {
                height: 52vh !important;
                min-height: 300px
            }

            .admin-chat-bubble {
                max-width: 90%
            }
        }
    </style>
    <div class="container-fluid px-3 px-lg-4 py-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <h2 class="fw-bold mb-1">Live Support Chat</h2>
                <p class="text-muted mb-0">Private user conversations, attachments and voice replies.</p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <span class="badge bg-secondary" id="connection-status">Connecting…</span>
                <button type="button" class="btn btn-sm btn-outline-primary" id="enable-desktop-alerts">Enable desktop & push
                    alerts</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="disable-admin-push">Disable admin
                    push</button>
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
                    <form id="send-form"
                        class="card-footer bg-white border-top d-flex flex-wrap gap-2 align-items-center p-3">
                        <input id="attachment" type="file" class="form-control form-control-sm"
                            accept="image/*,video/mp4,video/webm,audio/*" style="max-width:190px">
                        <button id="record-voice" type="button" class="btn btn-outline-secondary btn-sm"
                            title="Start voice recording"><i class="fas fa-microphone"></i> Record</button>
                        <span id="recording-status" class="small text-danger d-none">Recording… press Stop to attach</span>
                        <input id="body" class="form-control" maxlength="5000" placeholder="Write a reply…"
                            aria-label="Reply message">
                        <button class="btn btn-warning" type="submit">Send</button>
                    </form>
                    <div class="px-3 pb-2 small text-muted">Maximum attachment size: 20 MB. Keep this tab open for desktop
                        alerts.</div>
                </div>
            </div>
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

                var active = null;
                var lastId = 0;
                var busy = false;

                var recordedFile = null;
                var recorder = null;
                var mediaStream = null;
                var chunks = [];

                var conversationVersion = 0;
                var pendingMessageRefresh = false;
                var pendingMessageReset = false;

                var conversationSearchTimer = null;
                var userSearchTimer = null;

                var userSearchRequest = 0;
                var conversationSearchRequest = 0;

                var realtimeConnected = false;

                var $conversations = $('#conversations');
                var $messages = $('#messages');
                var $status = $('#connection-status');

                var $conversationSearch = $('#conversation-search');
                var $userSearch = $('#user-search');
                var $userSearchResults = $('#user-search-results');

                var $recordButton = $('#record-voice');
                var $recordingStatus = $('#recording-status');

                var initialConversationOpened = false;

                // -------------------------------------------------
                // API REQUEST
                // -------------------------------------------------

                function request(url, options) {

                    options = options || {};

                    var body = options.body;
                    var isFormData = body instanceof FormData;
                    var isJson = typeof body === 'string';

                    var ajaxOptions = {
                        url: url,
                        type: options.method || 'GET',
                        dataType: 'json',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    };

                    if (body !== undefined) {
                        ajaxOptions.data = body;
                    }

                    if (isFormData) {
                        ajaxOptions.processData = false;
                        ajaxOptions.contentType = false;
                    } else if (isJson) {
                        ajaxOptions.processData = false;
                        ajaxOptions.contentType = 'application/json; charset=UTF-8';
                    }

                    if (options.headers) {
                        $.extend(ajaxOptions.headers, options.headers);
                    }

                    return $.ajax(ajaxOptions);
                }

                function showError(xhr, fallback) {

                    var message = fallback || 'Request failed.';

                    if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr && xhr.status) {
                        message += ' (HTTP ' + xhr.status + ')';
                    }

                    return message;
                }

                function setStatus(message, connected) {

                    $status
                        .text(message)
                        .removeClass('bg-success bg-secondary bg-danger')
                        .addClass(connected ? 'bg-success' : 'bg-secondary');
                }

                // -------------------------------------------------
                // CONVERSATIONS
                // -------------------------------------------------

                function loadConversations(snapshot) {

                    var requestId = ++conversationSearchRequest;

                    var phone = $.trim($conversationSearch.val() || '');

                    var url = root + '/conversations';

                    if (phone) {
                        url += '?phone=' + encodeURIComponent(phone);
                    }

                    var ajaxRequest;

                    if (snapshot && !phone) {
                        ajaxRequest = $.Deferred().resolve(snapshot).promise();
                    } else {
                        ajaxRequest = request(url);
                    }

                    ajaxRequest.done(function(result) {

                        if (requestId !== conversationSearchRequest) {
                            return;
                        }

                        $conversations.empty();

                        var items = $.isArray(result.data) ? result.data : [];

                        if (!items.length) {

                            $('<div>')
                                .addClass('list-group-item text-muted small py-3')
                                .text(
                                    phone ?
                                    'No conversations found for this phone number.' :
                                    'No conversations yet.'
                                )
                                .appendTo($conversations);

                            return;
                        }

                        $.each(items, function(index, conversation) {

                            var isActive = Number(active) === Number(conversation.id);

                            var $button = $('<button>', {
                                type: 'button',
                                class: 'list-group-item list-group-item-action' +
                                    (isActive ? ' active' : '')
                            });

                            var $heading = $('<div>').addClass(
                                'd-flex justify-content-between gap-2'
                            );

                            $('<span>')
                                .addClass('fw-bold')
                                .text(
                                    conversation.user && conversation.user.name ?
                                    conversation.user.name :
                                    'User #' + conversation.id
                                )
                                .appendTo($heading);

                            if (conversation.unread_count) {

                                $('<span>')
                                    .addClass('badge bg-danger')
                                    .text(conversation.unread_count)
                                    .appendTo($heading);
                            }

                            $button.append($heading);

                            $('<small>')
                                .text(
                                    conversation.user && conversation.user.phone ?
                                    conversation.user.phone :
                                    ''
                                )
                                .appendTo($button);

                            $('<div>')
                                .addClass('small text-muted text-truncate')
                                .text(conversation.last_message_preview || 'No messages yet')
                                .appendTo($button);

                            $button.on('click.adminChat', function() {
                                selectConversation(conversation);
                            });

                            $conversations.append($button);

                        });

                        // Open conversation from URL only once.

                        if (!initialConversationOpened) {

                            var params = new URLSearchParams(window.location.search);
                            var requestedId = params.get('conversation_id');

                            if (requestedId) {

                                $.each(items, function(index, item) {

                                    if (Number(item.id) === Number(requestedId)) {

                                        initialConversationOpened = true;
                                        selectConversation(item);

                                        return false;
                                    }

                                });
                            }
                        }

                    }).fail(function(xhr) {

                        if (requestId !== conversationSearchRequest) {
                            return;
                        }

                        setStatus(
                            showError(xhr, 'Unable to load conversations.'),
                            false
                        );

                    });
                }

                // -------------------------------------------------
                // USER SEARCH
                // -------------------------------------------------

                function searchUsers() {

                    var requestId = ++userSearchRequest;

                    var term = $.trim($userSearch.val() || '');

                    $userSearchResults.empty();

                    if (!term) {

                        $('<div>')
                            .addClass('list-group-item small text-muted')
                            .text('Search by name, phone number or user ID.')
                            .appendTo($userSearchResults);

                        return;
                    }

                    $('<div>')
                        .addClass('list-group-item small text-muted')
                        .text('Searching registered users...')
                        .appendTo($userSearchResults);

                    request(
                        root + '/users?q=' + encodeURIComponent(term)
                    ).done(function(result) {

                        if (requestId !== userSearchRequest) {
                            return;
                        }

                        $userSearchResults.empty();

                        var users = $.isArray(result.data) ? result.data : [];

                        if (!users.length) {

                            $('<div>')
                                .addClass('list-group-item small text-muted')
                                .text('No registered users found.')
                                .appendTo($userSearchResults);

                            return;
                        }

                        $.each(users, function(index, user) {

                            var unread = Number(user.unread_count) || 0;

                            var stateClass = unread > 0 ?
                                'bg-danger' :
                                (user.has_conversation ? 'bg-success' : 'bg-primary');

                            var stateText = unread > 0 ?
                                unread + ' unread' :
                                (user.has_conversation ? 'Existing chat' : 'Start chat');

                            var $button = $('<button>', {
                                type: 'button',
                                class: 'list-group-item list-group-item-action py-2'
                            });

                            var $title = $('<div>').addClass(
                                'd-flex justify-content-between gap-2'
                            );

                            $('<strong>')
                                .text(user.name || 'User #' + user.id)
                                .appendTo($title);

                            $('<span>')
                                .addClass('badge ' + stateClass)
                                .text(stateText)
                                .appendTo($title);

                            $button.append($title);

                            $('<small>')
                                .addClass('text-muted d-block')
                                .text(
                                    (user.phone || 'No phone') +
                                    ' · User #' + user.id +
                                    ' · ' + (user.status || 'unknown')
                                )
                                .appendTo($button);

                            $button.on('click.adminChat', function() {

                                var $clicked = $(this);

                                $clicked.prop('disabled', true);

                                request(root + '/conversations', {
                                    method: 'POST',
                                    body: JSON.stringify({
                                        user_id: user.id
                                    })
                                }).done(function(started) {

                                    var conversation = {
                                        id: started.data.id,
                                        user: started.data.user || {
                                            id: user.id,
                                            name: user.name,
                                            phone: user.phone
                                        }
                                    };

                                    loadConversations();

                                    selectConversation(conversation);

                                    $userSearchResults.empty();
                                    $userSearch.val('');

                                }).fail(function(xhr) {

                                    window.alert(
                                        showError(xhr,
                                            'Unable to open conversation.')
                                    );

                                }).always(function() {

                                    $clicked.prop('disabled', false);

                                });

                            });

                            $userSearchResults.append($button);

                        });

                    }).fail(function(xhr) {

                        if (requestId !== userSearchRequest) {
                            return;
                        }

                        $userSearchResults.empty();

                        $('<div>')
                            .addClass('list-group-item small text-danger')
                            .text(showError(xhr, 'User search failed.'))
                            .appendTo($userSearchResults);

                    });
                }

                $userSearch.on('input.adminChat', function() {

                    clearTimeout(userSearchTimer);

                    userSearchTimer = window.setTimeout(function() {
                        searchUsers();
                    }, 300);

                });

                // -------------------------------------------------
                // SELECT CONVERSATION
                // -------------------------------------------------

                function selectConversation(conversation) {

                    active = conversation.id;

                    lastId = 0;

                    conversationVersion++;

                    $('#chat-title').text(
                        (conversation.user && conversation.user.name ?
                            conversation.user.name :
                            'User') +
                        ' · ' +
                        (conversation.user && conversation.user.phone ?
                            conversation.user.phone :
                            '')
                    );

                    $messages.empty();

                    $('<p>')
                        .addClass('text-muted text-center my-auto')
                        .text('Loading messages...')
                        .appendTo($messages);

                    loadMessages(true);

                    loadConversations();
                }

                // -------------------------------------------------
                // MESSAGE RENDERING
                // -------------------------------------------------

                function renderMessage(message) {

                    var outgoing = message.sender === 'admin';

                    var $bubble = $('<div>').addClass(
                        'admin-chat-bubble ' +
                        (outgoing ?
                            'admin-chat-bubble--outgoing' :
                            'admin-chat-bubble--incoming')
                    );

                    if (message.body) {

                        $('<div>')
                            .css('white-space', 'pre-wrap')
                            .text(message.body)
                            .appendTo($bubble);
                    }

                    if (message.media_url) {

                        var mime = String(message.media_mime || '');
                        var url = String(message.media_url);

                        if (mime.indexOf('image/') === 0) {

                            $('<img>', {
                                alt: 'Attachment',
                                src: url
                            }).css({
                                maxWidth: 'min(260px, 65vw)',
                                maxHeight: '240px',
                                borderRadius: '8px'
                            }).appendTo($bubble);

                        } else if (mime.indexOf('video/') === 0) {

                            $('<video>', {
                                controls: true,
                                playsinline: true,
                                src: url
                            }).css({
                                maxWidth: 'min(300px, 65vw)',
                                maxHeight: '240px'
                            }).appendTo($bubble);

                        } else if (mime.indexOf('audio/') === 0) {

                            $('<audio>', {
                                controls: true,
                                preload: 'metadata',
                                src: url
                            }).appendTo($bubble);

                        } else {

                            $('<a>', {
                                    href: url,
                                    target: '_blank',
                                    rel: 'noopener noreferrer'
                                })
                                .text('Open attachment')
                                .appendTo($bubble);
                        }
                    }

                    $('<small>')
                        .addClass('d-block text-muted mt-2 text-end')
                        .text(
                            (message.created_at || '') +
                            (message.read_at ? ' · Seen' : '')
                        )
                        .appendTo($bubble);

                    return $bubble;
                }

                // -------------------------------------------------
                // NOTIFICATIONS
                // -------------------------------------------------

                function notifyAdmin(message) {

                    var event;

                    if (typeof window.CustomEvent === 'function') {

                        event = new CustomEvent('admin:incoming-chat-message', {
                            detail: $.extend({}, message, {
                                sender: 'user'
                            })
                        });

                        window.dispatchEvent(event);
                    }

                    if (
                        'Notification' in window &&
                        Notification.permission === 'granted'
                    ) {

                        var notification = new Notification(
                            'New support message', {
                                body: message.body || 'User sent an attachment.'
                            }
                        );

                        $(notification).on('click.adminChat', function() {
                            window.focus();
                            notification.close();
                        });
                    }
                }

                // -------------------------------------------------
                // LOAD MESSAGES
                // -------------------------------------------------

                function loadMessages(reset) {

                    reset = !!reset;

                    if (!active) {
                        return;
                    }

                    if (busy) {

                        pendingMessageRefresh = true;
                        pendingMessageReset = pendingMessageReset || reset;

                        return;
                    }

                    busy = true;

                    var requestedConversation = active;
                    var requestedVersion = conversationVersion;

                    var messageElement = $messages.get(0);

                    var wasNearBottom = messageElement ?
                        (
                            messageElement.scrollHeight -
                            $messages.scrollTop() -
                            $messages.innerHeight()
                        ) < 100 :
                        true;

                    var query = reset ?
                        '' :
                        '?after_id=' + encodeURIComponent(String(lastId));

                    request(
                        root + '/' + requestedConversation + '/messages' + query
                    ).done(function(result) {

                        if (
                            requestedVersion !== conversationVersion ||
                            Number(requestedConversation) !== Number(active)
                        ) {
                            return;
                        }

                        var all = $.isArray(result.data) ? result.data : [];

                        var previousId = lastId;

                        $(window).trigger('admin:refresh-chat-unread');

                        if (!reset && previousId > 0) {

                            $.each(all, function(index, message) {

                                if (
                                    message.sender === 'user' &&
                                    Number(message.id) > previousId
                                ) {
                                    notifyAdmin(message);
                                }

                            });
                        }

                        var $rendered = $();

                        $.each(all, function(index, message) {

                            $rendered = $rendered.add(
                                renderMessage(message)
                            );

                        });

                        var hasPlaceholder = $messages.find(
                            'p.text-muted.text-center'
                        ).length > 0;

                        if (reset || hasPlaceholder) {

                            $messages.empty();

                            if ($rendered.length) {

                                $messages.append($rendered);

                            } else {

                                $('<p>')
                                    .addClass('text-muted text-center my-auto')
                                    .text('No messages yet. Send the first reply.')
                                    .appendTo($messages);
                            }

                        } else if ($rendered.length) {

                            $messages.append($rendered);
                        }

                        if (all.length) {

                            $.each(all, function(index, message) {

                                var id = Number(message.id) || 0;

                                if (id > lastId) {
                                    lastId = id;
                                }

                            });
                        }

                        if (!reset && all.length >= 200) {
                            pendingMessageRefresh = true;
                        }

                        if (reset || wasNearBottom) {

                            $messages.scrollTop(
                                $messages.get(0).scrollHeight
                            );
                        }

                        setStatus('Connected · refreshes automatically', true);

                    }).fail(function(xhr) {

                        setStatus('Reconnecting...', false);

                        console.warn(
                            'Support chat sync failed:',
                            showError(xhr)
                        );

                    }).always(function() {

                        busy = false;

                        var shouldRefresh = pendingMessageRefresh && !!active;
                        var shouldReset = pendingMessageReset;

                        pendingMessageRefresh = false;
                        pendingMessageReset = false;

                        if (shouldRefresh) {
                            loadMessages(shouldReset);
                        }

                    });
                }

                // -------------------------------------------------
                // CONVERSATION SEARCH
                // -------------------------------------------------

                $conversationSearch.on('input.adminChat', function() {

                    clearTimeout(conversationSearchTimer);

                    conversationSearchTimer = window.setTimeout(function() {

                        loadConversations();

                    }, 250);

                });

                // -------------------------------------------------
                // DESKTOP NOTIFICATION PERMISSION
                // -------------------------------------------------

                $('#enable-desktop-alerts').on('click.adminChat', function() {

                    if (!('Notification' in window)) {

                        window.alert(
                            'Notifications are not supported by this browser.'
                        );

                        return;
                    }

                    Notification.requestPermission().then(function(permission) {

                        if (permission !== 'granted') {

                            window.alert(
                                'Please allow notifications in browser settings.'
                            );

                            return;
                        }

                        if (
                            !('serviceWorker' in navigator) ||
                            !window.firebase ||
                            !window.FIREBASE_CONFIG ||
                            !window.FIREBASE_VAPID_KEY
                        ) {

                            window.alert(
                                'Desktop alerts are enabled while this tab is open, but background push configuration is unavailable.'
                            );

                            return;
                        }

                        navigator.serviceWorker.register(
                            '/firebase-messaging-sw.js'
                        ).then(function(registration) {

                            return navigator.serviceWorker.ready;

                        }).then(function(registration) {

                            var firebaseApp = window.firebase.apps.length ?
                                window.firebase.app() :
                                window.firebase.initializeApp(
                                    window.FIREBASE_CONFIG
                                );

                            var messaging = window.firebase.messaging(firebaseApp);

                            return messaging.getToken({
                                vapidKey: window.FIREBASE_VAPID_KEY,
                                serviceWorkerRegistration: registration
                            });

                        }).then(function(pushToken) {

                            if (!pushToken) {
                                throw new Error(
                                'Firebase did not return a push token.');
                            }

                            return request(
                                root.replace(/\/support-chat\/?$/,
                                    '/push-subscribe'), {
                                    method: 'POST',
                                    body: JSON.stringify({
                                        token: pushToken,
                                        platform: 'web'
                                    })
                                }
                            );

                        }).done(function() {

                            window.alert(
                                'Background push alerts enabled for this admin account.'
                            );

                        }).catch(function(error) {

                            window.alert(
                                'Push registration failed: ' +
                                (error.message || 'Unknown error')
                            );

                        });

                    });

                });

                // -------------------------------------------------
                // DISABLE ADMIN PUSH
                // -------------------------------------------------

                $('#disable-admin-push').on('click.adminChat', function() {

                    if (
                        !window.firebase ||
                        !window.FIREBASE_CONFIG ||
                        !window.FIREBASE_VAPID_KEY
                    ) {

                        window.alert('Firebase configuration is unavailable.');

                        return;
                    }

                    if (!('serviceWorker' in navigator)) {

                        window.alert('Service workers are not supported.');

                        return;
                    }

                    navigator.serviceWorker.getRegistration('/').then(
                        function(registration) {

                            if (!registration) {
                                throw new Error('No admin push registration found.');
                            }

                            var firebaseApp = window.firebase.apps.length ?
                                window.firebase.app() :
                                window.firebase.initializeApp(
                                    window.FIREBASE_CONFIG
                                );

                            var messaging = window.firebase.messaging(firebaseApp);

                            return messaging.getToken({
                                vapidKey: window.FIREBASE_VAPID_KEY,
                                serviceWorkerRegistration: registration
                            });

                        }
                    ).then(function(pushToken) {

                        if (!pushToken) {
                            throw new Error('No active push token found.');
                        }

                        return request(
                            root.replace(/\/support-chat\/?$/, '/push-subscribe'), {
                                method: 'DELETE',
                                body: JSON.stringify({
                                    token: pushToken
                                })
                            }
                        );

                    }).done(function() {

                        window.alert(
                            'Admin background push disabled. Browser permission remains unchanged.'
                        );

                    }).catch(function(error) {

                        window.alert(
                            'Unable to disable admin push: ' +
                            (error.message || 'Unknown error')
                        );

                    });

                });

                // -------------------------------------------------
                // VOICE RECORDING
                // -------------------------------------------------

                $recordButton.on('click.adminChat', function() {

                    if (recorder && recorder.state === 'recording') {

                        recorder.stop();

                        $recordButton
                            .text('Record')
                            .removeClass('btn-danger')
                            .addClass('btn-outline-secondary');

                        $recordingStatus
                            .text('Preparing voice attachment...')
                            .removeClass('d-none');

                        return;
                    }

                    if (
                        !navigator.mediaDevices ||
                        !navigator.mediaDevices.getUserMedia ||
                        typeof MediaRecorder === 'undefined'
                    ) {

                        window.alert(
                            'Voice recording is not supported by this browser.'
                        );

                        return;
                    }

                    navigator.mediaDevices.getUserMedia({
                        audio: true
                    }).then(function(newStream) {

                        mediaStream = newStream;

                        var mimeTypes = [
                            'audio/webm;codecs=opus',
                            'audio/webm',
                            'audio/mp4'
                        ];

                        var selectedMime = '';

                        $.each(mimeTypes, function(index, mime) {

                            if (
                                MediaRecorder.isTypeSupported &&
                                MediaRecorder.isTypeSupported(mime)
                            ) {
                                selectedMime = mime;
                                return false;
                            }

                        });

                        recorder = selectedMime ?
                            new MediaRecorder(mediaStream, {
                                mimeType: selectedMime
                            }) :
                            new MediaRecorder(mediaStream);

                        chunks = [];
                        recordedFile = null;

                        recorder.ondataavailable = function(event) {

                            if (event.data && event.data.size > 0) {
                                chunks.push(event.data);
                            }
                        };

                        recorder.onstop = function() {

                            var type = recorder.mimeType || 'audio/webm';

                            var extension = type.indexOf('mp4') !== -1 ?
                                'mp4' :
                                'webm';

                            var blob = new Blob(chunks, {
                                type: type
                            });

                            recordedFile = new File(
                                [blob],
                                'voice-message.' + extension, {
                                    type: type
                                }
                            );

                            $('#attachment').val('');

                            $recordingStatus
                                .text('Voice message ready to send')
                                .removeClass('d-none');

                            if (mediaStream) {

                                mediaStream.getTracks().forEach(function(track) {
                                    track.stop();
                                });

                                mediaStream = null;
                            }
                        };

                        recorder.start();

                        $recordButton
                            .text('Stop')
                            .removeClass('btn-outline-secondary')
                            .addClass('btn-danger');

                        $recordingStatus
                            .text('Recording... press Stop to attach')
                            .removeClass('d-none');

                    }).catch(function() {

                        window.alert(
                            'Microphone permission was denied or unavailable.'
                        );

                    });

                });

                // -------------------------------------------------
                // FILE ATTACHMENT
                // -------------------------------------------------

                $('#attachment').on('change.adminChat', function() {

                    recordedFile = null;

                    $recordingStatus.addClass('d-none');

                });

                // -------------------------------------------------
                // SEND MESSAGE
                // -------------------------------------------------

                $('#send-form').on('submit.adminChat', function(event) {

                    event.preventDefault();

                    if (!active) {

                        window.alert('Select a conversation first.');

                        return;
                    }

                    var $body = $('#body');
                    var $file = $('#attachment');

                    var message = $.trim($body.val() || '');

                    var selectedFile = $file.get(0).files[0];

                    if (!message && !selectedFile && !recordedFile) {
                        return;
                    }

                    if (recorder && recorder.state === 'recording') {

                        window.alert('Stop recording before sending.');

                        return;
                    }

                    var formData = new FormData();

                    if (message) {
                        formData.append('body', message);
                    }

                    var attachment = recordedFile || selectedFile;

                    if (attachment) {
                        formData.append('attachment', attachment);
                    }

                    var conversationId = active;

                    var $sendButton = $('#send-form button[type="submit"]');

                    $sendButton.prop('disabled', true);

                    request(
                        root + '/' + conversationId + '/send', {
                            method: 'POST',
                            body: formData
                        }
                    ).done(function() {

                        if (Number(active) !== Number(conversationId)) {
                            return;
                        }

                        $body.val('');
                        $file.val('');

                        recordedFile = null;

                        $recordingStatus.addClass('d-none');

                        loadMessages(true);

                        loadConversations();

                    }).fail(function(xhr) {

                        window.alert(
                            showError(xhr, 'Unable to send message.')
                        );

                    }).always(function() {

                        $sendButton.prop('disabled', false);

                    });

                });

                // -------------------------------------------------
                // REALTIME SUPPORT MESSAGE EVENT
                // -------------------------------------------------

                $(window).on(
                    'admin:support-message.adminChat',
                    function(event, payload) {

                        if (
                            !payload &&
                            event.originalEvent &&
                            event.originalEvent.detail
                        ) {
                            payload = event.originalEvent.detail;
                        }

                        payload = payload || {};

                        if (
                            active !== null &&
                            Number(payload.conversation_id) === Number(active)
                        ) {
                            loadMessages(false);
                        }

                    }
                );

                // -------------------------------------------------
                // REALTIME CONVERSATION SNAPSHOT
                // -------------------------------------------------

                $(window).on(
                    'admin:conversations-snapshot.adminChat',
                    function(event, snapshot) {

                        if ($.trim($conversationSearch.val() || '')) {
                            return;
                        }

                        if (
                            !snapshot &&
                            event.originalEvent &&
                            event.originalEvent.detail
                        ) {
                            snapshot = event.originalEvent.detail;
                        }

                        if (snapshot) {
                            loadConversations(snapshot);
                        }

                    }
                );

                // -------------------------------------------------
                // PUSHER CONNECTION STATE
                // -------------------------------------------------

                $(window).on(
                    'admin:broadcast-state.adminChat',
                    function(event, state) {

                        if (
                            !state &&
                            event.originalEvent &&
                            event.originalEvent.detail
                        ) {
                            state = event.originalEvent.detail;
                        }

                        state = state || {};

                        realtimeConnected = state.current === 'connected';

                        if (realtimeConnected) {

                            if (active) {
                                loadMessages(false);
                            }

                            setStatus('Live · Pusher', true);

                        } else {

                            setStatus('Reconnecting...', false);

                        }

                    }
                );

                // -------------------------------------------------
                // RECOVERY SYNC
                // -------------------------------------------------

                $(window).on(
                    'admin:recovery-tick.adminChat',
                    function() {

                        if (!realtimeConnected && active) {
                            loadMessages(false);
                        }

                    }
                );

                // -------------------------------------------------
                // INITIAL LOAD
                // -------------------------------------------------

                loadConversations();

                if (
                    window.adminPusher &&
                    window.adminPusher.connection &&
                    window.adminPusher.connection.state === 'connected'
                ) {
                    realtimeConnected = true;
                }

                // -------------------------------------------------
                // CLEANUP
                // -------------------------------------------------

                $(window).one('beforeunload.adminChat', function() {

                    clearTimeout(userSearchTimer);
                    clearTimeout(conversationSearchTimer);

                    if (recorder && recorder.state === 'recording') {
                        recorder.stop();
                    }

                    if (mediaStream) {

                        mediaStream.getTracks().forEach(function(track) {
                            track.stop();
                        });

                        mediaStream = null;
                    }

                });

            });

        })(jQuery, window, document);
    </script>
@endsection
