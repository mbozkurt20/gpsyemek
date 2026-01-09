<div class="col-md-6" wire:key="{{ $menu_item['id'] }}">
    <div class="product-card">
        <figure class="product-card-media d-flex justify-content-center align-items-center">
            <img data-src="{{ $menu_item['image'] }}" class="lazy" alt="product">
            <div class="loader-container">
                <img src="{{ asset('frontend/images/default/loader.gif') }}" class="loader" alt="loading">
            </div>
        </figure>

        @php
            $status = \App\Helpers\RestaurantHelper::getStatus($restaurant);

            $closedUntil = $restaurant->temporary_closed_until ? \Carbon\Carbon::parse($restaurant->temporary_closed_until) : null;
            $restaurantUrl = '';
        @endphp

        <div class="product-card-content">
           @if ($status != 'open')
                <h4 class="product-card-title showClosedNotification">
                    {{ \Illuminate\Support\Str::limit($menu_item['name'], 20) }}
                </h4>
            @else
                <a href="#variation-{{ $menu_item['id'] }}" wire:click.prevent="addToCartModal({{ $menu_item['id'] }})">
                    <h4 class="product-card-title">
                        {{ \Illuminate\Support\Str::limit($menu_item['name'], 20) }}
                    </h4>
                </a>
            @endif

            <p class="product-card-text">
                {!! \Illuminate\Support\Str::limit(strip_tags($menu_item['description']), 70) !!}
            </p>

            <div class="product-card-info">
                <div class="product-card-price">
                    @if ($menu_item['discount_price'] > 0)
                        <del>{{ setting('currency_code') }}{{ $menu_item['unit_price'] }}</del>
                        <span>{{ setting('currency_code') }}{{ $menu_item['unit_price'] - $menu_item['discount_price'] }}</span>
                    @else
                        <span>{{ setting('currency_code') }}{{ $menu_item['unit_price'] - $menu_item['discount_price'] }}</span>
                    @endif
                </div>

               @if ($status != 'open')
                    <button class="product-card-add showClosedNotification">
                        <span>{{ __('frontend.add') }}</span>
                    </button>
                @else
                    <a href="#variation-{{ $menu_item['id'] }}" class="product-card-add" wire:click.prevent="addToCartModal({{ $menu_item['id'] }})">
                        <span>{{ __('frontend.add') }}</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
