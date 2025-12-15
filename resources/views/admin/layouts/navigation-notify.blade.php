@if(auth()->user()?->myrole === 3)

    <!-- BEKLEYEN SİPARİŞ UYARISI -->
    <div id="pendingAlert" class="hidden mt-2 md:mt-0">
        <a class="text-primary font-semibold" href="{{ url('admin/live-orders') }}">
            🔔 Bekleyen Siparişler Var – Görüntüle
        </a>
    </div>

    <audio id="alarmSound" loop>
        <source src="/beep.mp3" type="audio/mpeg">
    </audio>

    <script>
        const audio = document.getElementById('alarmSound');
        const alertBox = document.getElementById('pendingAlert');

        audio.pause();
        audio.currentTime = 0;

        function checkPendingOrders() {
            fetch(`/restaurant/is-order/{{ auth()->user()->restaurant->id }}`)
                .then(res => res.json())
                .then(data => {
                    if (data.pending) {
                        alertBox.classList.remove('hidden');
                        if (audio.paused) audio.play();
                    } else {
                        alertBox.classList.add('hidden');
                        audio.pause();
                        audio.currentTime = 0;
                    }
                });
        }

        checkPendingOrders();
        setInterval(checkPendingOrders, 5000);
    </script>

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
                })
                    .then(res => res.json())
                    .then(() => location.reload())
                    .catch(err => console.error(err));
            });
        });
    </script>

    @php
        $closedUntil = auth()->user()->restaurant->temporary_closed_until
            ? \Carbon\Carbon::parse(auth()->user()->restaurant->temporary_closed_until)
            : null;
    @endphp

        <!-- ANA BAR -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 py-3 px-4 border border-gray-200 rounded-lg">

        <!-- SOL TARAF -->
        <div class="flex flex-wrap items-center gap-3">
            <span class="font-medium">Restoran Durumu</span>

            <label class="switch">
                <input
                    type="checkbox"
                    id="statusSwitch"
                    {{ auth()->user()->restaurant->permanently_closed ? '' : 'checked' }}
                >
                <span class="slider"></span>
            </label>

            <button id="openModalBtn" class="bg-primary text-white px-3 py-1 rounded text-sm">
                Süreli Kapat
            </button>

            <!-- DURUM YAZISI -->
            <div class="text-sm">
                @if (auth()->user()->restaurant->permanently_closed)
                    <span class="text-red-600 font-semibold">Süresiz kapalı</span>
                @elseif ($closedUntil && $closedUntil->isFuture())
                    <span class="text-red-600">
                    {{ __('frontend.close_now') }} ({{ $closedUntil->diffForHumans() }})
                </span>
                @elseif (
                    auth()->user()->restaurant->opening_time < now()->format('H:i:s') &&
                    auth()->user()->restaurant->closing_time > now()->format('H:i:s')
                )
                    <span class="text-green-600 font-semibold">
                    {{ __('frontend.open_now') }}
                </span>
                @else
                    <span class="text-red-600">
                    {{ __('frontend.close_now') }}
                </span>
                @endif
            </div>
        </div>

        <!-- SAĞ TARAF -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 text-sm text-gray-600 text-right">
        <span>
            Açılış - Kapanış
            ({{ date('H:i', strtotime(auth()->user()->restaurant->opening_time)) }}
            -
            {{ date('H:i', strtotime(auth()->user()->restaurant->closing_time)) }})
        </span>
            <div id="pendingAlert" class="hidden">
                <a class="text-primary font-semibold" href="{{ url('admin/live-orders') }}">
                    🔔 Bekleyen Siparişler Var
                </a>
            </div>
        </div>
    </div>

    <!-- MODAL -->
    <div id="closeModal" class="fixed inset-0 hidden flex items-center justify-center bg-black/50 z-50">
        <div class="bg-white p-6 rounded-lg w-80">
            <h2 class="text-lg font-semibold mb-4">Restoranı Kapat</h2>
            <p class="mb-3">Ne kadar süreyle kapatmak istiyorsunuz?</p>

            <div class="flex flex-col space-y-2">
                <button class="close-btn bg-gray-200 hover:bg-primary hover:text-white py-2 rounded" data-duration="15">15 Dk</button>
                <button class="close-btn bg-gray-200 hover:bg-primary hover:text-white py-2 rounded" data-duration="30">30 Dk</button>
                <button class="close-btn bg-gray-200 hover:bg-primary hover:text-white py-2 rounded" data-duration="45">45 Dk</button>
                <button class="close-btn bg-gray-200 hover:bg-primary hover:text-white py-2 rounded" data-duration="60">1 Saat</button>
            </div>

            <button id="cancelModal" class="mt-4 text-red-500">İptal</button>
        </div>
    </div>

    <script>
        const openModalBtn = document.getElementById('openModalBtn');
        const modal = document.getElementById('closeModal');
        const cancelModal = document.getElementById('cancelModal');
        const restaurantId = "{{ auth()->user()->restaurant->id }}";

        openModalBtn.addEventListener('click', () => modal.classList.remove('hidden'));
        cancelModal.addEventListener('click', () => modal.classList.add('hidden'));

        document.querySelectorAll('.close-btn').forEach(button => {
            button.addEventListener('click', () => {
                fetch(`/restaurant/close/${restaurantId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ duration: button.dataset.duration })
                })
                    .then(() => location.reload());
            });
        });
    </script>

    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 28px;
        }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute;
            inset: 0;
            background: #e5e7eb;
            border-radius: 999px;
            transition: 0.3s;
        }
        .slider:before {
            content: "";
            position: absolute;
            height: 24px;
            width: 24px;
            left: 2px;
            top: 2px;
            background: #fff;
            border-radius: 50%;
            transition: 0.3s;
        }
        input:checked + .slider {
            background: #1fde74;
        }
        input:checked + .slider:before {
            transform: translateX(32px);
        }
    </style>

@endif
