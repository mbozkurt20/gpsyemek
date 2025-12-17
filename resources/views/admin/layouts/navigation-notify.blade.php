@if(auth()->user()?->myrole === 3)

    <div id="pendingAlert" style="display:none; margin:10px 0;">
        <a class="text-primary" href="{{ url('admin/live-orders') }}">
            🔔 Bekleyen Siparişler Var – Görüntüle
        </a>
    </div>

    <!-- bekleyen sipariş  durumu  -->
    <audio id="alarmSound" loop>
        <source src="/beep.mp3" type="audio/mpeg">
    </audio>

    <script>
        let audio = document.getElementById('alarmSound');
        let alertBox = document.getElementById('pendingAlert');

        // Açılışta SESİ KAPAT
        audio.pause();
        audio.currentTime = 0;

        function checkPendingOrders() {
            fetch(`/restaurant/is-order/{{ auth()->user()->restaurant->id }}`)
                .then(res => res.json())
                .then(data => {

                    if (data.pending) {
                        // Pending VAR → linki göster, sesi çal
                        alertBox.style.display = "block";

                        if (audio.paused) {
                            audio.play();
                        }

                    } else {
                        // Pending YOK → linki gizle, sesi durdur
                        alertBox.style.display = "none";
                        audio.pause();
                        audio.currentTime = 0;
                    }
                });
        }

        // İlk yüklemede hemen kontrol et
        checkPendingOrders();

        // Sonra 5 saniyede bir tekrar kontrol et
        setInterval(checkPendingOrders, 5000);
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const statusSwitch = document.getElementById('statusSwitch');
            const restaurantId = "{{ auth()->user()->restaurant->id }}";

            if (!statusSwitch) return;

            statusSwitch.addEventListener('change', function () {

                // switch açık mı?
                const isOpen = this.checked;

                fetch(`/restaurant/close-status/${restaurantId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        permanently_closed: isOpen ? 0 : 1
                    })
                })
                    .then(res => res.json())
                    .then(() => location.reload())
                    .catch(err => console.error(err));
            });

        });
    </script>

    <div class="flex items-center py-2 px-3 border border-gray-200">
        <div class="flex items-center space-x-2 pr-12">
            <span class="pr-2">Restoran Durumu</span>

            <label class="switch">
                <input
                    type="checkbox"
                    id="statusSwitch"
                    {{ auth()->user()->restaurant->permanently_closed ? '' : 'checked' }}
                >
                <span class="slider"></span>
            </label>


            <button id="openModalBtn" class="ml-2 bg-primary text-white px-3 py-1 rounded">Süreli Kapat</button>

            <!-- Modal -->
            <div id="closeModal" class="fixed inset-0  flex items-center justify-center hidden" style="background-color:rgba(0, 0, 0, 0.5);">>
                <div class="bg-white p-6 rounded-lg w-80">
                    <h2 class="text-lg font-semibold mb-4">Restoranı Kapat</h2>
                    <p class="mb-3">Ne kadar süreyle kapatmak istiyorsunuz?</p>
                    <div class="flex flex-col space-y-2">
                        <button class="close-btn bg-gray-200 mb-2 hover:bg-primary hover:text-white py-2 rounded" data-duration="15">15 Dk</button>
                        <button class="close-btn bg-gray-200 mb-2 hover:bg-primary hover:text-white py-2 rounded" data-duration="30">30 Dk</button>
                        <button class="close-btn bg-gray-200 mb-2 hover:bg-primary hover:text-white py-2 rounded" data-duration="45"> 45 Dk</button>
                        <button class="close-btn bg-gray-200 mb-2 hover:bg-primary hover:text-white py-2 rounded" data-duration="60">1 Saat</button>
                    </div>
                    <button id="cancelModal" class="mt-4 text-red-500">İptal</button>
                </div>
            </div>

            <!-- bekleyen sipariş  durumu  -->
            @php
                $closedUntil = auth()->user()->restaurant->temporary_closed_until
                    ? \Carbon\Carbon::parse(auth()->user()->restaurant->temporary_closed_until)
                    : null;
            @endphp

            <div class="ml-2">
                @if (auth()->user()->restaurant->permanently_closed)
                    <p  style="color: red" class="text-red-700 font-semibold">Süresiz kapalı</p>

                @elseif ($closedUntil && $closedUntil->isFuture())
                    <p style="color: red" class="text-red-700">
                        {{ __('frontend.close_now') }} ({{ $closedUntil->diffForHumans() }} sonra açılacak)
                    </p>

                @elseif (
                    auth()->user()->restaurant->opening_time < now()->format('H:i:s') &&
                    auth()->user()->restaurant->closing_time > now()->format('H:i:s')
                )
                    <p style="color: #116504" class="text-success font-semibold">{{ __('frontend.open_now') }}</p>

                @else
                    <p style="color: red" class="text-red-700">{{ __('frontend.close_now') }}</p>
                @endif
            </div>

            <script>
                const openModalBtn = document.getElementById('openModalBtn');
                const modal = document.getElementById('closeModal');
                const cancelModal = document.getElementById('cancelModal');
                const restaurantId = "{{ auth()->user()->restaurant->id }}";

                openModalBtn.addEventListener('click', () => {
                    modal.classList.remove('hidden');
                });

                cancelModal.addEventListener('click', () => {
                    modal.classList.add('hidden');
                });

                document.querySelectorAll('.close-btn').forEach(button => {
                    button.addEventListener('click', () => {
                        const duration = button.getAttribute('data-duration');
                        fetch(`/restaurant/close/${restaurantId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ duration: duration })
                        })
                            .then(res => res.json())
                            .then(data => {
                                modal.classList.add('hidden');
                                location.reload(); // sayfayı yeniler
                            })
                            .catch(err => console.error(err));
                    });
                });
            </script>
        </div>

        <span class="ml-3 text-gray-600">
                    Açılış - Kapanış({{ date('H:i', strtotime(auth()->user()->restaurant->opening_time)) }} - {{ date('H:i', strtotime(auth()->user()->restaurant->closing_time)) }})</span>
    </div>

    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;   /* Kısalttık */
            height: 28px;  /* Biraz daha ince */
        }
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background: #e5e7eb;
            border-radius: 999px;
            border: 1px solid #ccc;
            transition: 0.3s;
        }
        .slider:before {
            content: "";
            position: absolute;
            height: 24px;
            width: 24px;
            border-radius: 50%;
            left: 2px;
            top: 2px;
            background: #fff;
            box-shadow: 0 1px 4px rgba(0,0,0,0.2);
            transition: 0.3s;
        }
        input:checked + .slider {
            background: #1fde74;
            border-color: transparent;
        }
        input:checked + .slider:before {
            transform: translateX(32px); /* yeni genişliğe göre ayar */
        }
    </style>
@endif
