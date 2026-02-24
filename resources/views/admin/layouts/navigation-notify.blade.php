@if(auth()->user()?->myrole === 3)

    {{-- BEKLEYEN SİPARİŞ UYARISI --}}
    <div id="pendingAlert" style="display:none; margin:10px 0;">
        <a class="text-primary" href="{{ url('admin/live-orders') }}">
            🔔 Bekleyen Siparişler Var – Görüntüle
        </a>
    </div>

    <audio id="alarmSound" loop>
        <source src="/beep.mp3" type="audio/mpeg">
    </audio>

    <script>
        let audio = document.getElementById('alarmSound');
        let alertBox = document.getElementById('pendingAlert');

        audio.pause();
        audio.currentTime = 0;

        function checkPendingOrders() {
            fetch(`/restaurant/is-order/{{ auth()->user()->restaurant->id }}`)
                .then(res => res.json())
                .then(data => {
                    if (data.pending) {
                        alertBox.style.display = "block";
                        if (audio.paused) audio.play();
                    } else {
                        alertBox.style.display = "none";
                        audio.pause();
                        audio.currentTime = 0;
                    }
                });
        }

        checkPendingOrders();
        setInterval(checkPendingOrders, 5000);
    </script>

    {{-- RESTORAN DURUM ALANI --}}
    <div class="flex items-center justify-between py-2 px-3 border border-gray-200">

        {{-- SOL TARAF --}}
        <div class="flex items-center space-x-3">

            @if ($restaurant->permanently_closed)
                <button id="openRestaurantBtn"
                        class="bg-green-500 text-white px-3 mr-4 py-1 rounded">
                    Restoranı Aç
                </button>
            @else
                <button id="openModalBtn"
                        class="bg-primary text-white px-3 mr-4 py-1 rounded">
                    Kapat
                </button>
            @endif

            @php
                $restaurant   = auth()->user()->restaurant;
                $closedUntil  = $restaurant->temporary_closed_until
                    ? \Carbon\Carbon::parse($restaurant->temporary_closed_until)
                    : null;
                $now          = \Carbon\Carbon::now();
                $nowDay       = strtolower($now->format('l'));
                $activeSlots  = $restaurant->timeSlots->where('status', 5)->where('day', $nowDay);
                $isOpenNow    = false;
                foreach ($activeSlots as $slot) {
                    if (\App\Helpers\isNowInTimeRange::isNowInTimeRange($slot->start_time, $slot->end_time, $now)) {
                        $isOpenNow = true;
                        break;
                    }
                }
            @endphp

            <div class="mr-4 font-bold">
                @if ($restaurant->permanently_closed)
                    <p class="text-red-500 font-semibold">Şu An Kapalı</p>

                @elseif ($closedUntil && $closedUntil->isFuture())
                    <p class="text-red-700">
                        {{ __('frontend.close_now') }} ({{ $closedUntil->diffForHumans() }} sonra açılacak)
                    </p>

                @elseif ($isOpenNow)
                    <p class="text-green-500 font-semibold">{{ __('frontend.open_now') }}</p>

                @else
                    <p class="text-red-700">{{ __('frontend.close_now') }}</p>
                @endif
            </div>
        </div>

        {{-- SAĞ TARAF Değişti --}}
        {{--
         <div class="text-gray-600 whitespace-nowrap px-2">
            ({{ date('H:i', strtotime(auth()->user()->restaurant->opening_time)) }}
            -
            {{ date('H:i', strtotime(auth()->user()->restaurant->closing_time)) }})
        </div>
        --}}
    </div>

    {{-- MODAL --}}
    <div id="closeModal"
         class="fixed inset-0 flex items-center justify-center hidden"
         style="background-color:rgba(0,0,0,0.5);">

        <div class="bg-white p-6 rounded-lg w-80">
            <h2 class="text-lg font-semibold mb-4">Restoranı Kapat</h2>
            <p class="mb-3">Ne kadar süreyle kapatmak istiyorsunuz?</p>

            <div class="flex flex-col space-y-2">
                <button class="close-btn bg-gray-200 hover:bg-primary hover:text-white py-2 rounded" data-duration="15">15 Dk</button>
                <button class="close-btn bg-gray-200 hover:bg-primary hover:text-white py-2 rounded" data-duration="30">30 Dk</button>
                <button class="close-btn bg-gray-200 hover:bg-primary hover:text-white py-2 rounded" data-duration="45">45 Dk</button>
                <button class="close-btn bg-gray-200 hover:bg-primary hover:text-white py-2 rounded" data-duration="60">1 Saat</button>
                <button id="permanentCloseBtn" class="bg-red-500 text-white py-2 rounded hover:bg-red-700">Süresiz Kapat</button>
            </div>

            <button id="cancelModal" class="mt-4 text-red-500">İptal</button>
        </div>
    </div>

    {{-- SWITCH AJAX --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const statusSwitch = document.getElementById('statusSwitch');
            const restaurantId = "{{ auth()->user()->restaurant->id }}";

            if (!statusSwitch) return;

            statusSwitch.addEventListener('change', function () {
                fetch(`/restaurant/close-status/${restaurantId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        permanently_closed: this.checked ? 0 : 1
                    })
                }).then(() => location.reload());
            });
        });
    </script>

    {{-- MODAL JS --}}
    <script>
        const modal = document.getElementById('closeModal');
        const cancelModal = document.getElementById('cancelModal');
        const restaurantId = "{{ auth()->user()->restaurant->id }}";
        const csrfToken = '{{ csrf_token() }}';

        const openModalBtn = document.getElementById('openModalBtn');
        if (openModalBtn) openModalBtn.addEventListener('click', () => modal.classList.remove('hidden'));
        cancelModal.addEventListener('click', () => modal.classList.add('hidden'));

        document.querySelectorAll('.close-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                fetch(`/restaurant/close/${restaurantId}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ duration: btn.dataset.duration })
                }).then(() => location.reload());
            });
        });

        const permanentCloseBtn = document.getElementById('permanentCloseBtn');
        if (permanentCloseBtn) {
            permanentCloseBtn.addEventListener('click', () => {
                fetch(`/restaurant/close-status/${restaurantId}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ permanently_closed: 1 })
                }).then(() => location.reload());
            });
        }

        const openRestaurantBtn = document.getElementById('openRestaurantBtn');
        if (openRestaurantBtn) {
            openRestaurantBtn.addEventListener('click', () => {
                fetch(`/restaurant/close-status/${restaurantId}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ permanently_closed: 0 })
                }).then(() => location.reload());
            });
        }
    </script>

    {{-- SWITCH STYLE --}}
    <style>
        .switch {
            position: relative;
            width: 60px;
            height: 28px;
            display: inline-block;
        }
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            inset: 0;
            background: #e5e7eb;
            border-radius: 999px;
            transition: .3s;
        }
        .slider:before {
            content: "";
            position: absolute;
            width: 24px;
            height: 24px;
            left: 2px;
            top: 2px;
            background: white;
            border-radius: 50%;
            transition: .3s;
        }
        input:checked + .slider {
            background: #1fde74;
        }
        input:checked + .slider:before {
            transform: translateX(32px);
        }
    </style>

@endif
