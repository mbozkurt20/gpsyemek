<aside class="db-sidebar">
    <div class="db-sidebar-header">
        <a href="{{ route('home') }}" class="w-24"><img src="{{ themeSetting('site_logo') ? themeSetting('site_logo')->logo : asset('images/seeder/settings/logo.png') }}" alt="logo"></a>
        <button class="fa-solid fa-xmark xmark-btn"></button>
    </div>
    <nav class="db-sidebar-nav">
        @isset($backendMenus)
            @foreach ($backendMenus as $menu)

                @if ($menu['link'] === '#')
                    @if (isset($menu['child']))
                        <h5 class="db-sidebar-nav-title">{{ trans('menu.' . $menu['name']) }}</h5>
                        <ul class="db-sidebar-nav-list">
                            @foreach ($menu['child'] as $child)
                                @if(!(auth()->user()->myrole == 3 && in_array($child['name'], ['cuisines', 'categories', 'qr_builder','tables','reservations'])))
                                    <li class="db-sidebar-nav-item {{ Request::is('admin/' . $child['link']) || Request::is('admin/' . $child['link'] . '/*') ? 'active' : '' }}">
                                        <a href="{{ url('admin/' . $child['link']) }}" class="db-sidebar-nav-menu">
                                            <i class="{{ $child['icon'] }} text-sm"></i>
                                            <span class="">{{ trans('menu.' . $child['name']) }} </span>
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                @else
                    <ul class="db-sidebar-nav-list">
                        <li class="db-sidebar-nav-item {{ Request::is('admin/' . $menu['link'] . '*') ? 'active' : '' }}">
                            <a href="{{ url('admin/' . $menu['link']) }}" class="db-sidebar-nav-menu">
                                <i class="{{ $menu['icon'] }} text-sm"></i>
                                <span class="">{{ trans('menu.' . $menu['name']) }}</span>
                            </a>
                        </li>
                    </ul>
                @endif

            @endforeach
        @endisset
    </nav>
</aside>
