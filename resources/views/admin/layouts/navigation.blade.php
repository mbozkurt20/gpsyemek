<header class="db-header">
    <a href="{{ route('home') }}" class="w-32 flex-shrink-0"><img class="w-full"
                                                                  src="{{ themeSetting('site_logo') ? themeSetting('site_logo')->logo : asset('images/seeder/settings/logo.png') }}"
                                                                  alt="logo"></a>
    <div class="flex items-center justify-end w-full gap-2">
        {{--
               <div class="sub-header flex items-center gap-4 transition xh:justify-between xh:fixed xh:left-0 xh:w-full xh:p-4 xh:border-y xh:border-[#EFF0F6] xh:bg-white">
            <div class="flex items-center justify-between md:justify-center gap-4">

                <div class="language-group dropdown-group relative">
                    @foreach ($backendLanguage as $lang)
                        @if (Session()->has('applocale') and Session()->get('applocale') and setting('locale'))
                            @if (Session()->get('applocale') == $lang->code)
                            <button class="dropdown-btn flex items-center gap-2 h-9 px-3 rounded-lg bg-primary-light">
                                <span> {{ $lang->flag_icon }} </span>
                                <span class="hidden md:block whitespace-nowrap text-xs font-medium capitalize text-heading">{{ $lang->name }}</span>
                            </button>
                            @endif
                        @elseif (setting('locale') == $lang->code)
                            <button class="dropdown-btn flex items-center gap-2 h-9 px-3 rounded-lg bg-primary-light">
                                <span> {{ $lang->flag_icon }} </span>
                                <span class="hidden md:block whitespace-nowrap text-xs font-medium capitalize text-heading">{{ $lang->name }}</span>
                            </button>
                        @endif
                    @endforeach
                    <ul class="p-2 min-w-[180px] rounded-lg shadow-xl absolute top-14 ltr:left-0 rtl:right-0 z-10 border border-gray-200 bg-white hidden dropdown-list">
                        @foreach ($backendLanguage as $lang)
                        <li class="flex items-center gap-2 rounded-md cursor-pointer hover:bg-gray-100">
                            <a href="{{ route('lang.index', $lang->code) }}" class="py-1.5 px-2.5">
                                <span class="pr-2">{{ $lang->flag_icon }}</span>
                                <span class="text-heading capitalize text-sm">{{ $lang->name }}</span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        --}}
        @if(auth()->user()->myrole ==3)
            <div class="flex items-center py-2 px-3 border border-gray-200">
                <div class="flex items-center space-x-2 pr-12">
                    <span class="pr-2">Restoran</span>
                    <form id="statusForm" method="GET" action="">
                        @csrf
                        <label class="switch">
                            <input type="checkbox" id="statusSwitch"
                                {{ auth()->user()->restaurant->current_status == 5 ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </form>

                    <script>
                        const switchInput = document.getElementById('statusSwitch');
                        const form = document.getElementById('statusForm');
                        const restaurantId = "{{ auth()->user()->restaurant->id }}";

                        switchInput.addEventListener('change', function() {
                            // Duruma göre status belirle
                            let status = this.checked ? 5 : 0; // 5 açık, 0 kapalı gibi
                            // Form action'ı ayarla
                            form.action = `/restaurant/status${status}/${restaurantId}`;
                            form.submit();
                        });
                    </script>
                </div>
                <span class="ml-3 text-gray-600">
                    ({{ date('H:i', strtotime(auth()->user()->restaurant->opening_time)) }} - {{ date('H:i', strtotime(auth()->user()->restaurant->closing_time)) }})
                </span>
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
        <button class="fa-solid fa-align-left db-header-nav w-9 h-9 rounded-lg text-primary bg-primary/5"></button>
        <button data-account="#profileSidebar" class="flex items-center gap-1 sm:gap-2">
            <img class="flex-shrink w-9 h-9 object-cover rounded-lg" src="{{ auth()->user()->image }}" alt="avatar">
            <h3 class="whitespace-nowrap overflow-hidden text-ellipsis text-sm capitalize text-left leading-[17px]">{{ auth()->user()->getrole->name }}
                <b class="block whitespace-nowrap overflow-hidden text-ellipsis font-semibold">{{ __('Hi,') }} {{ auth()->user()->name }}</b>
            </h3>
            <i class="fa-solid fa-caret-down text-xs"></i>
        </button>
    </div>
</header>
