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
    <style>
        /* Shared admin table treatment: clear grid lines, readable headers and safe wrapping. */
        #layoutSidenav_content .table,
        #layoutSidenav_content table:not(.table) {
            width: 100%;
            border-collapse: collapse;
            font-size: .94rem;
        }
        #layoutSidenav_content .table > :not(caption) > * > *,
        #layoutSidenav_content table:not(.table) > :not(caption) > * > * {
            border: 1px solid #d6deea;
            padding: .7rem .8rem;
            vertical-align: middle;
        }
        #layoutSidenav_content .table thead th,
        #layoutSidenav_content table:not(.table) thead th {
            background: #eef3f9;
            color: #1f2937;
            font-weight: 700;
            white-space: nowrap;
        }
        #layoutSidenav_content .table tbody td,
        #layoutSidenav_content table:not(.table) tbody td {
            overflow-wrap: anywhere;
        }
        #layoutSidenav_content .table-responsive {
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        #layoutSidenav_content .btn {
            min-height: 34px;
        }
        #layoutSidenav_content .badge {
            font-weight: 700;
            line-height: 1.25;
        }
        .profit-loss-summary > [class*="col-"] {
            display: flex;
        }
        .profit-loss-summary .card {
            width: 100%;
            min-width: 0;
        }
        .profit-loss-summary .card-body,
        .profit-loss-summary .card-body > .d-flex {
            min-width: 0;
        }
        .profit-loss-summary .h4 {
            font-size: clamp(.95rem, 2.5vw, 1.35rem);
            overflow-wrap: anywhere;
        }
        @media (max-width: 420px) {
            .profit-loss-summary > [class*="col-"] {
                padding-left: .4rem;
                padding-right: .4rem;
            }
            .profit-loss-summary .card-body {
                padding: .8rem;
            }
        }
    </style>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
    <script src="{{ URL::asset('backend') }}/js/scripts.js"></script>
    @include('backend.partials.toastr')
    @yield('script')
</body>

</html>
