<header class="db-header flex items-center justify-between
               px-4 py-2 border-b border-gray-200 bg-white
               ml-[260px]">

{{-- SOL TARAF --}}
    <div class="flex items-center gap-6">

        {{-- LOGO --}}
        <a href="{{ route('home') }}" class="w-32 flex-shrink-0">
            <img class="w-full"
                 src="{{ themeSetting('site_logo') ? themeSetting('site_logo')->logo : asset('images/seeder/settings/logo.png') }}"
                 alt="logo">
        </a>

        @if(auth()->user()?->myrole === 3)
            {{-- RESTORAN DURUMU --}}
            <div class="flex items-center gap-3 px-3 py-1.5 rounded-lg ml-4">

                <span class="font-bold">Restoran Durumu</span>

                <label class="switch">
                    <input
                        type="checkbox"
                        id="statusSwitch"
                        {{ auth()->user()->restaurant->permanently_closed ? '' : 'checked' }}
                    >
                    <span class="slider"></span>
                </label>
            </div>
        @endif
    </div>


    {{-- SAĞ TARAF --}}
    <div class="flex items-center gap-4">
        {{-- NOTIFY --}}
        @include('admin.layouts.navigation-notify')

        {{-- MENU --}}
        <button class="fa-solid fa-align-left db-header-nav w-9 h-9 rounded-lg text-primary bg-primary/5"></button>

        {{-- USER --}}
        <button data-account="#profileSidebar" class="flex items-center gap-2">
            <img class="w-9 h-9 rounded-lg object-cover" src="{{ auth()->user()?->image }}" alt="avatar">
            <div class="text-left">
                <div class="text-xs capitalize">{{ auth()->user()?->getrole->name }}</div>
                <div class="font-semibold text-sm">Merhaba, {{ auth()->user()?->name }}</div>
            </div>
            <i class="fa-solid fa-caret-down text-xs"></i>
        </button>
    </div>
</header>
