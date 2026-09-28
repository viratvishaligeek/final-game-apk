@php

    $number = $item['number'];

    $displayNumber = $item['display_number'] ?? $number;

    $hasBet = $item['total_bets'] > 0;

    $modalId = 'bidModal_' . md5($type . '_' . $number);

    $bidDetails = $details->get($number, collect());

@endphp


{{-- ============================================================
NUMBER CARD
============================================================= --}}

<button type="button"
    class="
        number-card
        {{ $hasBet ? 'has-bet' : 'no-bet' }}
    "
    data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">

    <span class="number-status"></span>


    <div class="number-type">

        {{ $type }}

    </div>


    <div class="number-value">

        {{ $displayNumber }}

    </div>


    <div class="
            number-amount
            {{ !$hasBet ? 'zero' : '' }}
        ">

        ₹{{ number_format($item['total_amount'], 0) }}

    </div>


    <div class="number-meta">

        <strong>
            {{ $item['total_bets'] }}
        </strong>
        Bets

        ·

        <strong>
            {{ $item['total_users'] }}
        </strong>
        Users

    </div>

</button>


{{-- ============================================================
MODAL
============================================================= --}}

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">

    <div
        class="
            modal-dialog
            modal-dialog-centered
            modal-dialog-scrollable
            modal-lg
        ">

        <div class="modal-content border-0 rounded-4 overflow-hidden">


            {{-- HEADER --}}

            <div class="modal-header bg-dark text-white">

                <div>

                    <div
                        class="
                            text-warning
                            fw-bold
                            small
                        ">
                        {{ strtoupper($type) }}
                    </div>

                    <h3 class="fw-bold mb-0">
                        {{ $displayNumber }}
                    </h3>

                    <small class="text-white-50">
                        {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                    </small>

                </div>


                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>

            </div>


            {{-- SUMMARY --}}

            <div class="modal-body bg-light">


                <div class="row g-2 mb-3">


                    <div class="col-4">

                        <div
                            class="
                                bg-white
                                rounded-3
                                p-3
                                text-center
                            ">

                            <small class="text-muted">
                                Total
                            </small>

                            <div
                                class="
                                    fw-bold
                                    text-success
                                ">

                                ₹{{ number_format($item['total_amount'], 2) }}

                            </div>

                        </div>

                    </div>


                    <div class="col-4">

                        <div
                            class="
                                bg-white
                                rounded-3
                                p-3
                                text-center
                            ">

                            <small class="text-muted">
                                Bets
                            </small>

                            <div class="fw-bold">

                                {{ $item['total_bets'] }}

                            </div>

                        </div>

                    </div>


                    <div class="col-4">

                        <div
                            class="
                                bg-white
                                rounded-3
                                p-3
                                text-center
                            ">

                            <small class="text-muted">
                                Users
                            </small>

                            <div class="fw-bold">

                                {{ $item['total_users'] }}

                            </div>

                        </div>

                    </div>

                </div>


                {{-- USER LIST --}}

                @if ($bidDetails->count())

                    <div class="mb-2">

                        <small
                            class="
                                fw-bold
                                text-muted
                            ">
                            USER BIDS
                        </small>

                    </div>


                    @foreach ($bidDetails as $bid)
                        <div
                            class="
                                user-bid
                                bg-white
                                rounded-4
                                p-3
                                mb-2
                                shadow-sm
                            ">

                            <div
                                class="
                                    d-flex
                                    align-items-center
                                    gap-3
                                ">


                                {{-- AVATAR --}}

                                <div
                                    class="
                                        user-avatar
                                        bg-warning
                                        text-dark
                                    ">

                                    {{ strtoupper(substr($bid->user?->name ?? 'U', 0, 1)) }}

                                </div>


                                {{-- USER --}}

                                <div class="flex-grow-1 min-w-0">

                                    <div class="fw-bold">

                                        {{ $bid->user?->name ?? 'Unknown User' }}

                                    </div>


                                    <small
                                        class="
                                            text-muted
                                            d-block
                                        ">

                                        📱
                                        {{ $bid->phone }}

                                    </small>


                                    <small
                                        class="
                                            text-muted
                                            d-block
                                        ">

                                        🧾
                                        {{ $bid->order_no }}

                                    </small>


                                    <small
                                        class="
                                            text-muted
                                        ">

                                        🕐
                                        {{ $bid->created_at->format('d M Y, h:i A') }}

                                    </small>

                                </div>


                                {{-- AMOUNT --}}

                                <div class="text-end">

                                    <div
                                        class="
                                            fw-bold
                                            fs-5
                                            text-success
                                        ">

                                        ₹{{ number_format($bid->amount, 2) }}

                                    </div>


                                    <span
                                        class="
                                            badge
                                            {{ $bid->status === 'pending' ? 'bg-warning text-dark' : 'bg-success' }}
                                        ">

                                        {{ ucfirst($bid->status) }}

                                    </span>

                                </div>

                            </div>

                        </div>
                    @endforeach
                @else
                    <div
                        class="
                            text-center
                            py-5
                            text-muted
                        ">

                        <div class="fs-1">
                            📭
                        </div>

                        <div class="fw-bold">
                            No Bets
                        </div>

                        <small>
                            No user has placed a bet on this number.
                        </small>

                    </div>

                @endif

            </div>

        </div>

    </div>

</div>
