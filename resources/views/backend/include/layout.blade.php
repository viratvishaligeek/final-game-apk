<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="{{ setting('title', 'Play Online Khaiwal') }}" />
    <meta name="robots" content="noindex,nofollow" />
    <title>{{ $pageName ?? 'Dashboard' }} - {{ setting('title', 'Play Online Khaiwal') }} Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css" rel="stylesheet" />
    <link href="{{ URL::asset('backend') }}/css/styles.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="{{ URL::asset('backend') }}/img/favicon.png" />
    <script defer="" src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/js/all.min.js" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js" crossorigin="anonymous">
    </script>
</head>

<body class="nav-fixed">
    @include('backend.include.header')
    <div id="layoutSidenav">
        @include('backend.include.sidebar')
        <div id="layoutSidenav_content">
            <main>
                @yield('page-header')
                <!-- Main page content-->
                <div class="container-xl">
                    @yield('content')
                </div>
                <!-- Main page content-->
            </main>
            <footer class="footer-admin mt-auto footer-light">
                <div class="container-xl px-4">
                    <div class="row">
                        <div class="col-md-6 small">
                            {{ setting('copyright_text', '© ' . date('Y') . ' ' . setting('title', 'Play Online Khaiwal')) }}
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <div class="modal fade" id="adminPaymentFailureModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" style="width:90vw;max-width:1100px">
            <div class="modal-content border-0 shadow-lg" style="min-height:90vh;border:5px solid #dc3545!important">
                <div class="modal-header bg-danger text-white">
                    <h4 class="modal-title fw-bold"><i class="fas fa-triangle-exclamation me-2"></i>Automatic Payment
                        Failed</h4>
                </div>
                <div class="modal-body d-flex flex-column justify-content-center p-4 p-md-5">
                    <div class="text-center text-danger mb-4">
                        <div style="font-size:clamp(3rem,9vw,6rem);line-height:1"><i
                                class="fas fa-circle-exclamation"></i></div>
                        <h2 class="fw-bold mt-3">Payment requires attention</h2>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100"><small class="text-muted d-block">User</small><strong
                                    id="payment-alert-user">—</strong>
                                <div class="small text-muted" id="payment-alert-user-meta"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100"><small class="text-muted d-block">Transaction
                                    ID</small><strong id="payment-alert-transaction">—</strong></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100"><small
                                    class="text-muted d-block">Amount</small><strong class="text-danger fs-3"
                                    id="payment-alert-amount">—</strong></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100"><small class="text-muted d-block">Gateway / Failed
                                    at</small><strong id="payment-alert-gateway">—</strong>
                                <div class="small text-muted" id="payment-alert-time"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100"><small class="text-muted d-block">Internal payment
                                    attempt ID</small><strong id="payment-alert-attempt">—</strong></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100"><small class="text-muted d-block">Gateway order
                                    ID</small><strong id="payment-alert-order">—</strong></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100"><small class="text-muted d-block">Error category /
                                    severity</small><strong id="payment-alert-category">—</strong>
                                <div class="small text-muted" id="payment-alert-severity"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100"><small class="text-muted d-block">Error code / HTTP
                                    status</small><strong id="payment-alert-code">—</strong></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100"><small class="text-muted d-block">Processing
                                    stage</small><strong id="payment-alert-stage">—</strong></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100"><small class="text-muted d-block">Diagnostic
                                    reference</small><strong id="payment-alert-diagnostic">—</strong></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100"><small
                                    class="text-muted d-block">Occurrences</small><strong
                                    id="payment-alert-occurrences">—</strong>
                                <div class="small text-muted" id="payment-alert-occurrence-range"></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="border border-danger rounded p-3"><small class="text-muted d-block">Failure
                                    reason</small>
                                <div class="fw-semibold" id="payment-alert-reason">Not provided by gateway.</div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-5"><button type="button" class="btn btn-danger btn-lg px-5 fw-bold"
                            id="payment-alert-acknowledge">Mark as Read</button></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
    <script src="{{ URL::asset('backend') }}/js/scripts.js"></script>
    @include('backend.partials.toastr')

    @if (config('broadcasting.default') === 'pusher')
        <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    @endif
    <script>
        window.ADMIN_PAYMENT_ALERT_BROADCAST = {
            driver: @json(config('broadcasting.default')),
            siteKey: @json(config('sites.current_key', config('sites.default', 'play-online-khaiwal'))),
            configured: @json(filled(config('broadcasting.connections.pusher.app_id')) && filled(config('broadcasting.connections.pusher.key')) && filled(config('broadcasting.connections.pusher.secret')) && filled(config('broadcasting.connections.pusher.options.cluster'))),
            key: @json(config('broadcasting.connections.' . config('broadcasting.default') . '.key')),
            cluster: @json(config('broadcasting.connections.pusher.options.cluster') ?? null),
            host: @json(config('broadcasting.connections.pusher.options.host') ?? null),
            port: @json(config('broadcasting.connections.pusher.options.port') ?? 443),
            forceTLS: @json(config('broadcasting.connections.pusher.options.useTLS', true))
        };
    </script>

    <script>
    (() => {
        const modalElement = document.getElementById('adminPaymentFailureModal');
        if (!modalElement || !window.bootstrap) return;

        const modal = new bootstrap.Modal(modalElement, {backdrop: 'static', keyboard: false});
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const endpoint = '/admin/payment-alerts';
        const acknowledged = new Set();
        const queued = new Map();
        let activeAlert = null;
        let modalClosing = false;
        let paymentAlertActive = false;
        const paymentAudio = new Audio('/sound/wow.wav');
        const chatAudio = new Audio('/sound/wow.wav');
        paymentAudio.loop = true;
        const playedChatMessages = new Set();
        const lastSeenChatMessageIds = new Map();
        let chatSnapshotInitialized = false;
        const chatSoundEnabled = @json((string) setting('notification_sound_chat', 'default') !== 'mute');
        const soundPreferenceEnabled = (() => { try { return localStorage.getItem('admin_notification_sound_enabled') === '1'; } catch (error) { return false; } })();
        const setSoundStatus = value => {
            const label = document.getElementById('admin-sound-status');
            if (label) label.textContent = value;
        };
        if (soundPreferenceEnabled) setSoundStatus('Sound enabled');

        const setText = (id, value) => {
            const element = document.getElementById(id);
            if (element) element.textContent = value == null || value === '' ? '—' : String(value);
        };
        const stopSound = () => {
            paymentAudio.pause();
            paymentAudio.currentTime = 0;
            paymentAlertActive = false;
        };
        const startSound = () => {
            paymentAlertActive = true;
            paymentAudio.currentTime = 0;
            paymentAudio.play().catch(() => setSoundStatus('Tap to enable sound'));
        };
        const playChatNotification = payload => {
            if (!chatSoundEnabled) return;
            const sender = payload?.sender || payload?.sender_type;
            const messageId = Number(payload?.message_id || payload?.id || 0);
            if (sender !== 'user' || !messageId || playedChatMessages.has(messageId)) return;
            playedChatMessages.add(messageId);
            if (playedChatMessages.size > 500) playedChatMessages.delete(playedChatMessages.values().next().value);
            if (paymentAlertActive) return;
            chatAudio.currentTime = 0;
            chatAudio.play().catch(() => setSoundStatus('Tap to enable sound'));
        };
        document.getElementById('admin-enable-alert-sounds')?.addEventListener('click', async () => {
            try {
                for (const audio of [paymentAudio, chatAudio]) {
                    const wasPlaying = !audio.paused;
                    audio.volume = 0;
                    await audio.play();
                    if (!wasPlaying) {
                        audio.pause();
                        audio.currentTime = 0;
                    }
                    audio.volume = 1;
                }
                try { localStorage.setItem('admin_notification_sound_enabled', '1'); } catch (error) { console.warn('Unable to persist notification sound preference.', error); }
                setSoundStatus('Sound enabled');
            } catch (error) {
                paymentAudio.volume = 1;
                chatAudio.volume = 1;
                setSoundStatus('Tap to enable sound');
            }
        });
        window.addEventListener('admin:incoming-chat-message', event => playChatNotification(event.detail || {}));
        window.addEventListener('admin:refresh-chat-unread', () => { void refreshHeaderChatNotifications(); });
        const formatDate = value => {
            if (!value) return '—';
            const date = new Date(value);
            return Number.isNaN(date.getTime()) ? String(value) : date.toLocaleString('en-IN');
        };
        const presentNext = () => {
            if (activeAlert || modalClosing || queued.size === 0) return;
            const [id, alert] = queued.entries().next().value;
            queued.delete(id);
            activeAlert = alert;
            paymentAlertActive = true;
            setText('payment-alert-user', alert.user_name);
            setText('payment-alert-user-meta', 'User ID: ' + alert.user_id + (alert.phone ? ' · ' + alert.phone : ''));
            setText('payment-alert-transaction', alert.transaction_id || 'Not generated');
            setText('payment-alert-attempt', alert.attempt_id);
            setText('payment-alert-order', alert.gateway_order_id);
            setText('payment-alert-category', alert.error_category);
            setText('payment-alert-severity', alert.severity);
            setText('payment-alert-code', (alert.error_code || '—') + (alert.http_status ? ' · HTTP ' + alert.http_status : ''));
            setText('payment-alert-stage', alert.processing_stage);
            setText('payment-alert-diagnostic', alert.diagnostic_reference);
            setText('payment-alert-occurrences', (alert.related_occurrences || 1) + ' correlated attempts · ' + (alert.occurrence_count || 1) + ' events for this attempt');
            setText('payment-alert-occurrence-range', formatDate(alert.related_first_occurred_at || alert.first_occurred_at) + ' – ' + formatDate(alert.related_latest_occurred_at || alert.latest_occurred_at));
            setText('payment-alert-amount', '₹' + Number(alert.amount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            setText('payment-alert-gateway', alert.gateway);
            setText('payment-alert-time', formatDate(alert.failed_at));
            setText('payment-alert-reason', alert.failure_reason || 'Not provided by gateway.');
            modal.show();
            startSound();
        };
        modalElement.addEventListener('hidden.bs.modal', () => {
            if (!modalClosing) return;
            modalClosing = false;
            presentNext();
        });
        const addAlert = alert => {
            if (!alert?.id || acknowledged.has(Number(alert.id)) || Number(activeAlert?.id) === Number(alert.id)) return;
            if (alert.fingerprint && activeAlert?.fingerprint === alert.fingerprint) {
                activeAlert = {...activeAlert, ...alert};
                setText('payment-alert-occurrences', (alert.related_occurrences || 1) + ' correlated attempts · ' + (alert.occurrence_count || 1) + ' events for this attempt');
                setText('payment-alert-occurrence-range', formatDate(alert.related_first_occurred_at || alert.first_occurred_at) + ' – ' + formatDate(alert.related_latest_occurred_at || alert.latest_occurred_at));
                return;
            }
            if (alert.fingerprint) {
                for (const [queuedId, queuedAlert] of queued.entries()) {
                    if (queuedAlert.fingerprint === alert.fingerprint) {
                        queued.set(queuedId, {...queuedAlert, ...alert});
                        return;
                    }
                }
            }
            queued.set(Number(alert.id), alert);
            presentNext();
        };
        const loadPending = async () => {
            try {
                const response = await fetch(endpoint + '/pending', {credentials: 'same-origin', headers: {'Accept': 'application/json'}});
                if (!response.ok) return;
                const payload = await response.json();
                window.dispatchEvent(new CustomEvent('admin:conversations-snapshot', {detail: payload}));
                (payload.data || []).forEach(addAlert);
            } catch (error) {
                console.warn('Unable to restore pending payment alerts.', error);
            }
        };

        document.getElementById('payment-alert-acknowledge')?.addEventListener('click', async () => {
            if (!activeAlert) return;
            const alert = activeAlert;
            const button = document.getElementById('payment-alert-acknowledge');
            button.disabled = true;
            try {
                const response = await fetch(endpoint + '/' + encodeURIComponent(alert.id) + '/acknowledge', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf},
                    body: JSON.stringify({})
                });
                if (!response.ok) throw new Error('Acknowledgement failed');
                acknowledged.add(Number(alert.id));
                activeAlert = null;
                stopSound();
                modalClosing = true;
                modal.hide();
                void loadPending();
            } catch (error) {
                console.error(error);
                window.alert('Unable to acknowledge this alert. Please try again.');
            } finally {
                button.disabled = false;
            }
        });

        void loadPending();

        const refreshHeaderChatNotifications = async () => {
            const badge = document.getElementById('admin-chat-unread-badge');
            const container = document.getElementById('admin-chat-notification-items');
            if (!badge || !container) return;
            try {
                const response = await fetch('/admin/support-chat/conversations', {
                    credentials: 'same-origin',
                    headers: {'Accept': 'application/json'}
                });
                if (!response.ok) return;
                const payload = await response.json();
                (payload.data || []).forEach(item => {
                    const messageId = Number(item.last_message_id || 0);
                    const previousId = lastSeenChatMessageIds.get(Number(item.id)) || 0;
                    if (chatSnapshotInitialized && messageId > previousId &&
                        item.last_message_sender === 'user' && Number(item.unread_count) > 0) {
                        playChatNotification({ message_id: messageId, sender: 'user' });
                    }
                    lastSeenChatMessageIds.set(Number(item.id), messageId);
                });
                chatSnapshotInitialized = true;
                const unread = (payload.data || []).filter(item => Number(item.unread_count) > 0);
                const total = Number(payload.meta?.total_unread_count ?? unread.reduce((sum, item) => sum + Number(item.unread_count || 0), 0));
                badge.textContent = total > 99 ? '99+' : String(total);
                badge.classList.toggle('d-none', total === 0);
                container.replaceChildren();
                if (!unread.length) {
                    const empty = document.createElement('div');
                    empty.className = 'dropdown-item small text-muted';
                    empty.textContent = 'No unread support messages.';
                    container.appendChild(empty);
                    return;
                }
                unread.slice(0, 6).forEach(item => {
                    const link = document.createElement('a');
                    link.className = 'dropdown-item dropdown-notifications-item';
                    link.href = '/admin/support-chat?conversation_id=' + encodeURIComponent(item.id);
                    const copy = document.createElement('div');
                    copy.className = 'dropdown-notifications-item-content';
                    const preview = document.createElement('div');
                    preview.className = 'dropdown-notifications-item-content-text';
                    preview.textContent = item.last_message_preview || 'New support message';
                    const details = document.createElement('div');
                    details.className = 'dropdown-notifications-item-content-details';
                    details.textContent = (item.user?.name || 'User') + ' · ' + (item.user?.phone || '') + ' · ' + item.unread_count + ' unread';
                    copy.append(preview, details);
                    link.appendChild(copy);
                    container.appendChild(link);
                });
            } catch (error) {
                console.warn('Unable to refresh support message notifications.', error);
            }
        };
        void refreshHeaderChatNotifications();

        const config = window.ADMIN_PAYMENT_ALERT_BROADCAST || {};
        let fallbackRefreshTimer = null;
        const startRealtimeFallback = () => {
            if (fallbackRefreshTimer) return;
            fallbackRefreshTimer = window.setInterval(() => {
                void loadPending();
                void refreshHeaderChatNotifications();
                window.dispatchEvent(new Event('admin:recovery-tick'));
            }, 30000);
        };
        const stopRealtimeFallback = () => {
            if (fallbackRefreshTimer) window.clearInterval(fallbackRefreshTimer);
            fallbackRefreshTimer = null;
        };
        window.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                void loadPending();
                void refreshHeaderChatNotifications();
                window.dispatchEvent(new Event('admin:recovery-tick'));
            }
        });
        if (config.driver === 'pusher' && config.configured && config.key && window.Pusher) {
            try {
                if (!config.cluster) throw new Error('Pusher is selected but PUSHER_APP_CLUSTER is missing.');
                const options = {
                    cluster: config.cluster,
                    forceTLS: config.forceTLS !== false,
                    authEndpoint: '/broadcasting/auth',
                    auth: {headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'}}
                };
                const client = window.adminPusher || new Pusher(config.key, options);
                window.adminPusher = client;
                if (!window.adminPusherLifecycleBound) {
                    window.adminPusherLifecycleBound = true;
                    client.connection.bind('state_change', state => {
                        window.dispatchEvent(new CustomEvent('admin:broadcast-state', {detail: state}));
                        if (state.current === 'connected') {
                            stopRealtimeFallback();
                            void loadPending();
                            void refreshHeaderChatNotifications();
                        } else {
                            startRealtimeFallback();
                            window.dispatchEvent(new Event('admin:recovery-tick'));
                        }
                    });
                }
                if (!window.adminPusherChannelsBound) {
                    window.adminPusherChannelsBound = true;
                    client.subscribe('private-admin.payment-alerts.' + config.siteKey).bind('payment.failed', addAlert);
                    client.subscribe('private-admin.user-presence.' + config.siteKey).bind('presence.changed', payload => {
                        window.dispatchEvent(new CustomEvent('admin:user-presence', {detail: payload}));
                    });
                    client.subscribe('private-admin.support-chat.' + config.siteKey).bind('support.message.created', payload => {
                        playChatNotification(payload);
                        window.dispatchEvent(new CustomEvent('admin:support-message', {detail: payload}));
                        void refreshHeaderChatNotifications();
                    });
                }
                if (client.connection.state !== 'connected') startRealtimeFallback();
                else {
                    void loadPending();
                }
                window.dispatchEvent(new CustomEvent('admin:broadcast-state', {
                    detail: {current: client.connection.state}
                }));
            } catch (error) {
                console.error('Admin Pusher realtime connection failed.', error);
                startRealtimeFallback();
            }
        } else {
            if (config.driver !== 'null') {
                console.warn('Admin realtime broadcasting is not configured for Pusher. Pending alerts use the HTTP recovery fallback.');
            }
            startRealtimeFallback();
        }
    })();
    </script>

        @yield('script')
</body>

</html>
