<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Public Push Notifications</title>
    <style>
        body{font-family:system-ui,sans-serif;background:#f3f5f8;color:#1d2733;margin:0;padding:24px}
        main{max-width:720px;margin:4vh auto;background:#fff;border-radius:16px;padding:28px;box-shadow:0 12px 36px #15253812}
        h1{margin-top:0;font-size:24px}label{display:block;font-weight:650;margin:18px 0 7px}
        input,textarea{box-sizing:border-box;width:100%;border:1px solid #ccd5df;border-radius:9px;padding:12px;font:inherit}
        textarea{min-height:140px;resize:vertical}button{margin-top:18px;background:#16886c;color:white;border:0;border-radius:9px;padding:12px 18px;font-weight:700;cursor:pointer}
        .notice{padding:12px;border-radius:9px;background:#e7f7ef;color:#126442;margin-bottom:16px}.error{background:#fff0ef;color:#9b2820}
        small{color:#667585}
    </style>
</head>
<body>
<main>
    <h1>Public Push Notifications</h1>
    <p>Send an announcement to all registered browser and app push subscribers. Signed-in users will also see it in their notification list.</p>
    @if (session('success')) <div class="notice">{{ session('success') }}</div> @endif
    @if ($errors->any()) <div class="notice error">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div> @endif
    <form method="POST" action="{{ route('admin.push-notifications.broadcast') }}">
        @csrf
        <label for="subject">Notification title</label>
        <input id="subject" name="subject" maxlength="255" required value="{{ old('subject') }}" placeholder="Important update">
        <label for="message">Message</label>
        <textarea id="message" name="message" maxlength="4000" required placeholder="Write your announcement...">{{ old('message') }}</textarea>
        <small>Delivery requires FCM project credentials and a configured web/mobile client.</small><br>
        <button type="submit">Send to all subscribers</button>
    </form>
</main>
</body>
</html>
