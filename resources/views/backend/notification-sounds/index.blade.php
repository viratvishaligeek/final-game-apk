@extends('backend.include.layout')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-3">
        <h2 class="fw-bold mb-1">Notification Sound Settings</h2>
        <p class="text-muted mb-0">Choose whether each in-app event plays the bundled default sound. This does not override OS-level push sound settings.</p>
    </div>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <form method="POST" action="{{ route('admin.notification-sounds.update') }}" class="card border-0 shadow-sm">
        @csrf
        <div class="card-body">
            @php
                $events = [
                    'add_money' => 'Add money / deposit',
                    'withdraw' => 'Withdrawal',
                    'debit' => 'Wallet debit / bid',
                    'credit' => 'Wallet credit',
                    'winning' => 'Winner / prize',
                    'result' => 'Game result',
                    'chat' => 'Live support chat',
                ];
            @endphp
            @foreach ($events as $key => $label)
                <div class="row align-items-center py-3 border-bottom">
                    <div class="col-md-5 fw-semibold">{{ $label }}</div>
                    <div class="col-md-7">
                        <select class="form-select" name="notification_sound_{{ $key }}">
                            <option value="default" @selected(($sounds[$key] ?? 'default') === 'default')>Play default notification sound</option>
                            <option value="mute" @selected(($sounds[$key] ?? 'default') === 'mute')>Mute this event</option>
                        </select>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="card-footer bg-white text-end">
            <button class="btn btn-primary" type="submit">Save sound settings</button>
        </div>
    </form>
</div>
@endsection
