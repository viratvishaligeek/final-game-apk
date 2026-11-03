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
                        <div class="col-md-6 small">{{ setting('copyright_text', '© ' . date('Y') . ' ' . setting('title', 'Play Online Khaiwal')) }}</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <div class="modal fade" id="adminPaymentFailureModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" style="width:90vw;max-width:1100px">
            <div class="modal-content border-0 shadow-lg" style="min-height:80vh;border:5px solid #dc3545!important">
                <div class="modal-header bg-danger text-white">
                    <h4 class="modal-title fw-bold"><i class="fas fa-triangle-exclamation me-2"></i>Automatic Payment Failed</h4>
                </div>
                <div class="modal-body d-flex flex-column justify-content-center p-4 p-md-5">
                    <div class="text-center text-danger mb-4">
                        <div style="font-size:clamp(3rem,9vw,6rem);line-height:1"><i class="fas fa-circle-exclamation"></i></div>
                        <h2 class="fw-bold mt-3">Payment requires attention</h2>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-6"><div class="border rounded p-3 h-100"><small class="text-muted d-block">User</small><strong id="payment-alert-user">—</strong><div class="small text-muted" id="payment-alert-user-meta"></div></div></div>
                        <div class="col-12 col-md-6"><div class="border rounded p-3 h-100"><small class="text-muted d-block">Transaction ID</small><strong id="payment-alert-transaction">—</strong></div></div>
                        <div class="col-12 col-md-6"><div class="border rounded p-3 h-100"><small class="text-muted d-block">Amount</small><strong class="text-danger fs-3" id="payment-alert-amount">—</strong></div></div>
                        <div class="col-12 col-md-6"><div class="border rounded p-3 h-100"><small class="text-muted d-block">Gateway / Failed at</small><strong id="payment-alert-gateway">—</strong><div class="small text-muted" id="payment-alert-time"></div></div></div>
                        <div class="col-12"><div class="border border-danger rounded p-3"><small class="text-muted d-block">Failure reason</small><div class="fw-semibold" id="payment-alert-reason">Not provided by gateway.</div></div></div>
                    </div>
                    <div class="text-center mt-5"><button type="button" class="btn btn-danger btn-lg px-5 fw-bold" id="payment-alert-acknowledge">Mark as Read</button></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
    <script src="{{ URL::asset('backend') }}/js/scripts.js"></script>
    @include('backend.partials.toastr')

    @if (in_array(config('broadcasting.default'), ['pusher', 'reverb'], true))
        <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    @endif
    <script>
        window.ADMIN_PAYMENT_ALERT_BROADCAST = {
            key: @json(config('broadcasting.connections.' . config('broadcasting.default') . '.key')),
            cluster: @json(config('broadcasting.connections.pusher.options.cluster') ?? null),
            host: @json(config('broadcasting.connections.reverb.options.host') ?? null),
            port: @json(config('broadcasting.connections.reverb.options.port') ?? 443),
            forceTLS: @json((config('broadcasting.connections.reverb.options.scheme') ?? 'https') === 'https')
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
        let sound = null;

        const setText = (id, value) => {
            const element = document.getElementById(id);
            if (element) element.textContent = value == null || value === '' ? '—' : String(value);
        };
        const stopSound = () => {
            if (!sound) return;
            sound.pause();
            sound.currentTime = 0;
            sound = null;
        };
        const startSound = () => {
            stopSound();
            sound = new Audio('/notification-tune.wav');
            sound.loop = true;
            sound.play().catch(() => {});
        };
        const formatDate = value => {
            if (!value) return '—';
            const date = new Date(value);
            return Number.isNaN(date.getTime()) ? String(value) : date.toLocaleString('en-IN');
        };
        const presentNext = () => {
            if (activeAlert || queued.size === 0) return;
            const [id, alert] = queued.entries().next().value;
            queued.delete(id);
            activeAlert = alert;
            setText('payment-alert-user', alert.user_name);
            setText('payment-alert-user-meta', 'User ID: ' + alert.user_id + (alert.phone ? ' · ' + alert.phone : ''));
            setText('payment-alert-transaction', alert.transaction_id);
            setText('payment-alert-amount', '₹' + Number(alert.amount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            setText('payment-alert-gateway', alert.gateway);
            setText('payment-alert-time', formatDate(alert.failed_at));
            setText('payment-alert-reason', alert.failure_reason || 'Not provided by gateway.');
            modal.show();
            startSound();
        };
        const addAlert = alert => {
            if (!alert?.id || acknowledged.has(Number(alert.id)) || Number(activeAlert?.id) === Number(alert.id)) return;
            queued.set(Number(alert.id), alert);
            presentNext();
        };
        const loadPending = async () => {
            try {
                const response = await fetch(endpoint + '/pending', {credentials: 'same-origin', headers: {'Accept': 'application/json'}});
                if (!response.ok) return;
                const payload = await response.json();
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
                modal.hide();
                presentNext();
                void loadPending();
            } catch (error) {
                console.error(error);
                window.alert('Unable to acknowledge this alert. Please try again.');
            } finally {
                button.disabled = false;
            }
        });

        void loadPending();

        const config = window.ADMIN_PAYMENT_ALERT_BROADCAST || {};
        if (config.key && window.Pusher) {
            try {
                const options = {
                    forceTLS: config.forceTLS !== false,
                    authEndpoint: '/broadcasting/auth',
                    auth: {headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'}}
                };
                if (config.cluster) options.cluster = config.cluster;
                if (config.host) {
                    options.wsHost = config.host;
                    options.wsPort = Number(config.port || 80);
                    options.wssPort = Number(config.port || 443);
                    options.enabledTransports = ['ws', 'wss'];
                }
                const client = new Pusher(config.key, options);
                client.subscribe('private-admin.payment-alerts').bind('payment.failed', addAlert);
                client.subscribe('private-admin.user-presence').bind('presence.changed', payload => {
                    window.dispatchEvent(new CustomEvent('admin:user-presence', {detail: payload}));
                });
            } catch (error) {
                console.error('Admin payment alert realtime connection failed.', error);
            }
        }
    })();
    </script>

        @yield('script')
</body>

</html>
