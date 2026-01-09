@extends('admin.app')
@push('css')
    <link rel="stylesheet" href="{{ asset('backend/lib/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/lib/bootstrap-social/bootstrap-social.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/lib/summernote/summernote-bs4.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/lib/bootstrap-timepicker/css/bootstrap-timepicker.min.css') }}">

    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 60px; /* Kısalttık */
            height: 28px; /* Biraz daha ince */
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
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
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
@endpush

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="custome-breadcrumb">
                {{ Breadcrumbs::render('restaurant/edit') }}
            </div>
        </div>

        <div class="col-12">
            <form action="{{ route('admin.restaurants.update', $restaurant) }}" method="POST"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-6">
                        <div class="db-card">
                            <div class="db-card-header">
                                <h3 class="db-card-title">{{ __('restaurant.restaurant_information') }}</h3>
                            </div>
                            <div class="db-card-body">
                                <div class="row">

                                    <div class="form-col-12 sm:form-col-12 md:form-col-12">
                                        <label class="db-field-title required"
                                               for="name">{{ __('levels.name') }}</label>
                                        <input type="text" name="name" id="name"
                                               class="db-field-control @error('name') invalid @enderror"
                                               value="{{ old('name', $restaurant->name) }}">

                                        @error('name')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-6 sm:form-col-6 md:form-col-6">
                                        <label class="db-field-title"
                                               for="opening_time">{{ __('levels.opening_time') }}</label>
                                        <input type="time" name="opening_time" id="opening_time"
                                               class="db-field-control @error('opening_time') invalid @enderror"
                                               value="{{ old('opening_time', $restaurant->opening_time) }}">

                                        @error('opening_time')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-6 sm:form-col-6 md:form-col-6">
                                        <label class="db-field-title"
                                               for="closing_time">{{ __('levels.closing_time') }}</label>
                                        <input type="time" name="closing_time" id="closing_time"
                                               class="db-field-control @error('closing_time') invalid @enderror"
                                               value="{{ old('closing_time', $restaurant->closing_time) }}">

                                        @error('closing_time')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-12 sm:form-col-12 md:form-col-12">
                                        <label class="db-field-title" for="cuisines">{{ __('levels.cuisines') }}</label>
                                        <div class="db-field-down-arrow">
                                            <select name="cuisines[]" id="cuisines"
                                                    class="db-field-control select2 appearance-none @error('cuisines') invalid @enderror"
                                                    multiple="multiple">
                                                <option value="">---</option>
                                                @if(!blank($cuisines))
                                                    @foreach($cuisines as $cuisine)
                                                        @if(in_array($cuisine->id, $restaurant_cuisines))
                                                            <option value="{{ $cuisine->id }}"
                                                                    selected>{{ $cuisine->name }}</option>
                                                        @else
                                                            <option
                                                                value="{{ $cuisine->id }}">{{ $cuisine->name }}</option>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        @error('cuisines')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-12">
                                        <label class="db-field-title required"
                                               for="restaurantaddress">{{ __('levels.restaurant_address') }}</label>
                                        <input type="text" name="restaurantaddress"
                                               class="db-field-control @error('restaurantaddress') invalid @enderror"
                                               id="restaurantaddress"
                                               value="{{ old('restaurantaddress', $restaurant->address) }}"></input>
                                        @error('restaurantaddress')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-12">
                                        <label class="db-field-title"
                                               for="description">{{ __('levels.description') }}</label>
                                        <textarea name="description"
                                                  class="db-field-control @error('description') invalid @enderror"
                                                  style="height: 5rem"
                                                  id="editor">{{ old('description', $restaurant->description) }}</textarea>
                                        @error('description')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-12 sm:form-col-12 md:form-col-12">
                                        <label class="db-field-title"
                                               for="customFile">{{ __('restaurant.logo') }}</label>

                                        <input type="file" name="restaurant_logo" id="customFile"
                                               class="db-field-control @error('restaurant_logo') invalid @enderror">

                                        @if ($errors->has('restaurant_logo'))
                                            <small
                                                class="db-field-alert">{{ $errors->first('restaurant_logo') }}</small>
                                        @endif
                                    </div>

                                    <div class="form-col-12 sm:form-col-12 md:form-col-12">
                                        <label class="db-field-title"
                                               for="customFile">{{ __('restaurant.background_image') }}</label>

                                        <input type="file" name="image" id="customFile"
                                               class="db-field-control @error('image') invalid @enderror">

                                        @if ($errors->has('image'))
                                            <small class="db-field-alert">{{ $errors->first('image') }}</small>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="db-card mt-5 p-4">
                            <div class="mb-5">
                                <label for="cap_address" class="block text-sm font-medium text-gray-700">CAP
                                    Adresi</label>
                                <input value="{{$restaurant->payInformation?->cap_address}}" required type="text"
                                       id="cap_address" name="cap_address"
                                       class="mt-1 db-field-control block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Örn: cap@firma.com">
                            </div>

                            <div class="mb-5">
                                <label for="iban_no" class="block text-sm font-medium text-gray-700">IBAN
                                    Numarası</label>
                                <input value="{{$restaurant->payInformation?->iban_no}}" required type="text"
                                       id="iban_no" name="iban_no"
                                       class="mt-1 db-field-control block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="TR00 0000 0000 0000 0000 0000 00">
                            </div>

                            <div class="mb-5">
                                <label for="iban_name" class="block text-sm font-medium text-gray-700">IBAN Adı</label>
                                <input value="{{$restaurant->payInformation?->iban_name}}" required type="text"
                                       id="iban_name" name="iban_name"
                                       class="mt-1  db-field-control block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Hesap Sahibinin Adı">
                            </div>

                            <div class="mb-5">
                                <label for="mersis_no" class="block text-sm font-medium text-gray-700">MERSİS
                                    Numarası</label>
                                <input value="{{$restaurant->payInformation?->mersis_no}}" type="text" id="mersis_no"
                                       name="mersis_no"
                                       class="mt-1 db-field-control block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Örn: 0123456789012345">
                            </div>
                        </div>

                        <div class="db-card mt-5">
                            <div class="db-card-header">
                                <h3 class="db-card-title">{{ __('restaurant.restaurant_status') }}</h3>
                            </div>
                            <div class="db-card-body">
                                <div class="row">

                                    <div class="form-col-12 sm:form-col-6 md:form-col-6">
                                        <label class="db-field-title required">{{ __('levels.delivery') }}</label>
                                        <div class="db-field-down-arrow">
                                            <select name="delivery_status"
                                                    class="db-field-control appearance-none @error('delivery_status') invalid @enderror">
                                                <option value="">---</option>
                                                @foreach(trans('delivery_statuses') as $delivery_statusKey => $delivery_status)
                                                    <option value="{{ $delivery_statusKey }}"
                                                        {{ (old('delivery_status', $restaurant->delivery_status) == $delivery_statusKey) ? 'selected' : '' }}>
                                                        {{ $delivery_status }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        @error('delivery_status')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-12 sm:form-col-6 md:form-col-6">
                                        <label class="db-field-title required">{{ __('levels.pickup') }}</label>
                                        <div class="db-field-down-arrow">
                                            <select name="pickup_status"
                                                    class="db-field-control appearance-none @error('pickup_status') invalid @enderror">
                                                <option value="">---</option>
                                                @foreach(trans('pickup_statuses') as $pickup_statusKey => $pickup_status)
                                                    <option value="{{ $pickup_statusKey }}"
                                                        {{ (old('pickup_status', $restaurant->pickup_status) == $pickup_statusKey) ? 'selected' : '' }}>
                                                        {{ $pickup_status }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        @error('pickup_status')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-12 sm:form-col-6 md:form-col-6">
                                        <label class="db-field-title required">{{ __('levels.table') }}</label>
                                        <div class="db-field-down-arrow">
                                            <select name="table_status"
                                                    class="db-field-control appearance-none @error('table_status') invalid @enderror">
                                                <option value="">---</option>
                                                @foreach(trans('table_statuses') as $table_statusKey => $table_status)
                                                    <option value="{{ $table_statusKey }}"
                                                        {{ (old('table_status', $restaurant->table_status) == $table_statusKey) ? 'selected' : '' }}>
                                                        {{ $table_status }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        @error('table_status')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-12 sm:form-col-6 md:form-col-6">
                                        <label class="db-field-title required">{{ __('levels.current_status') }}</label>
                                        <div class="db-field-down-arrow">
                                            <select name="current_status"
                                                    class="db-field-control appearance-none @error('current_status') invalid @enderror">
                                                <option value="">---</option>
                                                @foreach(trans('current_statuses') as $current_statusKey => $current_status)
                                                    <option value="{{ $current_statusKey }}"
                                                        {{ (old('current_status', $restaurant->current_status) == $current_statusKey) ? 'selected' : '' }}>
                                                        {{ $current_status }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        @error('current_status')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-12 sm:form-col-6 md:form-col-6">
                                        <label class="db-field-title required">{{ __('levels.status') }}</label>
                                        <div class="db-field-down-arrow">
                                            <select name="status"
                                                    class="db-field-control appearance-none @error('status') invalid @enderror">
                                                <option value="">---</option>
                                                @foreach(trans('statuses') as $statusKey => $status)
                                                    <option value="{{ $statusKey }}"
                                                        {{ (old('status', $restaurant->status) == $statusKey) ? 'selected' : '' }}>{{ $status }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        @error('status')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-12 sm:form-col-6 md:form-col-6">
                                        <label class="db-field-title required">{{ __('levels.waiter_status') }}</label>
                                        <div class="db-field-down-arrow">
                                            <select name="waiter_status"
                                                    class="db-field-control appearance-none @error('waiter_status') invalid @enderror">
                                                <option value="">---</option>
                                                @foreach(trans('waiter_statuses') as $waiter_statusKey => $waiter_status)
                                                    <option value="{{ $waiter_statusKey }}"
                                                        {{ (old('waiter_status', $restaurant->waiter_status) == $waiter_statusKey) ? 'selected' : '' }}>{{ $waiter_status }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        @error('waiter_status')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="col-12 mt-4">
                                        <button type="submit" class="db-btn text-white bg-primary">
                                            <i class="fa-solid fa-circle-check"></i>
                                            <span>{{ __('levels.save') }}</span>
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="db-card">
                            <div class="db-card-header">
                                <h3 class="db-card-title">{{ __('restaurant.restaurant_location') }}</h3>
                            </div>
                            <div class="db-card-body">
                                <div class="row">
                                    <div class="form-col-12 sm:form-col-12 md:form-col-12">
                                        <div id="googleMap"></div>
                                    </div>

                                    <div class="form-col-6 sm:form-col-6 md:form-col-6">
                                        <label class="db-field-title required"
                                               for="name">{{ __('levels.latitude') }}</label>
                                        <input type="text" name="lat" id="lat"
                                               class="db-field-control @error('lat') invalid @enderror"
                                               value="{{ old('lat', $restaurant->lat) }}">

                                        @error('lat')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-6 sm:form-col-6 md:form-col-6">
                                        <label class="db-field-title required"
                                               for="name">{{ __('levels.longitude') }}</label>
                                        <input type="text" name="long" id="long"
                                               class="db-field-control @error('long') invalid @enderror"
                                               value="{{ old('long', $restaurant->long) }}">

                                        @error('long')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-12 sm:form-col-12 md:form-col-12">
                                        <label class="db-field-title required" for="address">Adres</label>
                                        <textarea name="address" id="address-input"
                                                  class="db-field-control">{{ old('address', $restaurant->address) }}</textarea>
                                        @error('address')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror

                                        <button type="button" id="show-on-map"
                                                class="db-btn rounded-full text-white mt-2" style="background: #14179e">
                                            Haritada Göster
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="db-card mt-5">
                            <div class="db-card-header">
                                <h3 class="db-card-title">{{ __('restaurant.restaurant_owner_information') }}</h3>
                            </div>
                            <div class="db-card-body">
                                <div class="row">

                                    <div class="form-col-6 sm:form-col-6 md:form-col-6">
                                        <label class="db-field-title required"
                                               for="first_name">{{ __('levels.first_name') }}</label>
                                        <input type="text" name="first_name" id="first_name"
                                               class="db-field-control @error('first_name') invalid @enderror"
                                               value="{{ old('first_name', $restaurant->user->first_name) }}">

                                        @error('first_name')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-6 sm:form-col-6 md:form-col-6">
                                        <label class="db-field-title required"
                                               for="last_name">{{ __('levels.last_name') }}</label>
                                        <input type="text" name="last_name" id="last_name"
                                               class="db-field-control @error('last_name') invalid @enderror"
                                               value="{{ old('last_name', $restaurant->user->last_name) }}">

                                        @error('last_name')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-6 sm:form-col-6 md:form-col-6">
                                        <label class="db-field-title required"
                                               for="email">{{ __('levels.email') }}</label>
                                        <input type="text" name="email" id="email"
                                               class="db-field-control @error('email') invalid @enderror"
                                               value="{{ old('email', $restaurant->user->email) }}">

                                        @error('email')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-6 sm:form-col-6 md:form-col-6">
                                        <label class="db-field-title" for="username">{{ __('levels.username') }}</label>
                                        <input type="text" name="username" id="username"
                                               class="db-field-control @error('username') invalid @enderror"
                                               value="{{ old('username', $restaurant->user->username) }}">

                                        @error('username')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-6 sm:form-col-6 md:form-col-6">
                                        <label class="db-field-title required"
                                               for="password">{{ __('levels.password') }}</label>
                                        <input type="password" name="password" id="password"
                                               class="db-field-control @error('password') invalid @enderror"
                                               value="{{ old('password') }}">

                                        @error('password')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-6 sm:form-col-6 md:form-col-6">
                                        <label class="db-field-title required"
                                               for="phone">{{ __('levels.phone') }}</label>
                                        <input type="text" name="phone" id="phone"
                                               class="db-field-control @error('phone') invalid @enderror"
                                               value="{{ old('phone', $restaurant->user->phone) }}">

                                        @error('phone')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-12 sm:form-col-12 md:form-col-12">
                                        <label class="db-field-title required"
                                               for="address">{{ __('levels.address') }}</label>
                                        <input type="text" name="address"
                                               class="db-field-control @error('address') invalid @enderror"
                                               id="address"
                                               value="{{ old('address', $restaurant->user->address) }}"></input>
                                        @error('address')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-6 sm:form-col-6 md:form-col-6">
                                        <label class="db-field-title"
                                               for="deposit_amount">{{ __('levels.deposit_amount') }}</label>

                                        <input type="number" step="0.1" name="deposit_amount" id="deposit_amount"
                                               class="db-field-control @error('deposit_amount') invalid @enderror"
                                               value="{{ old('deposit_amount', $restaurant->user->deposit->deposit_amount) }}">

                                        @error('deposite_amount')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-12 sm:form-col-6 md:form-col-6">
                                        <label class="db-field-title required">{{ __('levels.status') }}</label>
                                        <div class="db-field-down-arrow">
                                            <select name="userstatus"
                                                    class="db-field-control appearance-none @error('userstatus') invalid @enderror">
                                                <option value="">---</option>
                                                @foreach(trans('user_statuses') as $key => $userstatus)
                                                    <option
                                                        value="{{ $key }}" {{ (old('userstatus', $restaurant->user->status) == $key) ? 'selected' : '' }}>
                                                        {{ $userstatus }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        @error('userstatus')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="db-card p-4 w-full md:w-1/2">
                <div class="db-card-header mb-3">
                    <h3 class="db-card-title">Restaurant Durumu</h3>
                </div>

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">

                    <div class="flex items-center gap-3">
                        <span>Restoran Durumu</span>

                        <form id="statusForm" method="GET" action="">
                            @csrf
                            <label class="switch">
                                <input type="checkbox" id="statusSwitch"
                                    {{ $restaurant->current_status == 5 ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </form>

                        <button id="openModalBtn"
                                class="bg-primary text-white px-3 py-1 rounded">
                            Resturantı Süreli Kapat
                        </button>
                    </div>

                    <span class="text-gray-600 text-sm">
                        ({{ date('H:i', strtotime($restaurant->opening_time)) }} - {{ date('H:i', strtotime($restaurant->closing_time)) }})
                    </span>

                    @php
                        $closedUntil = $restaurant->temporary_closed_until
                            ? \Carbon\Carbon::parse($restaurant->temporary_closed_until)
                            : null;
                    @endphp

                    <div>
                        @if ($restaurant->permanently_closed)
                            <p  style="color: red" class="text-red-700 font-semibold">Süresiz kapalı</p>

                        @elseif ($closedUntil && $closedUntil->isFuture())
                            <p style="color: red" class="text-red-700">
                                {{ __('frontend.close_now') }} ({{ $closedUntil->diffForHumans() }} sonra açılacak)
                            </p>

                        @elseif (
                            $restaurant->opening_time < now()->format('H:i:s') &&
                            $restaurant->closing_time > now()->format('H:i:s')
                        )
                            <p style="color: #116504" class="text-success font-semibold">{{ __('frontend.open_now') }}</p>

                        @else
                            <p style="color: red" class="text-red-700">{{ __('frontend.close_now') }}</p>
                        @endif
                    </div>
                </div>
            </div>


            <!-- Modal -->
            <div id="closeModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
                <div class="bg-white p-6 rounded-xl w-80 shadow-lg">
                    <h2 class="text-lg font-semibold mb-4">Restoranı Kapat</h2>
                    <p class="mb-3 text-sm text-gray-700">Ne kadar süreyle kapatmak istiyorsunuz?</p>

                    <div class="flex flex-col gap-2">
                        @foreach ([15 => '15 Dk', 30 => '30 Dk', 45 => '45 Dk', 60 => '1 Saat', 0 => 'Süresiz'] as $time => $label)
                            <button
                                class="close-btn bg-gray-200 hover:bg-primary hover:text-white py-2 rounded text-sm"
                                data-duration="{{ $time }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    <button id="cancelModal"
                            class="mt-4 text-red-600 text-sm font-medium block mx-auto">
                        İptal
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('backend/lib/select2/dist/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('backend/lib/bootstrap-timepicker/js/bootstrap-timepicker.min.js') }}"></script>
    <script async
            src="https://maps.googleapis.com/maps/api/js?key={{ setting('google_map_api_key') }}&libraries=places&callback=initMap"></script>

    <script>
        const openModalBtn = document.getElementById('openModalBtn');
        const modal = document.getElementById('closeModal');
        const cancelModal = document.getElementById('cancelModal');
        const restaurantId = "{{ $restaurant->id }}";

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
                    body: JSON.stringify({duration: duration})
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
    <script>
        // --- IBAN otomatik formatlama (her 4 karakterde bir boşluk) ---
        document.getElementById("iban_no").addEventListener("input", function (e) {
            let value = e.target.value.replace(/\s+/g, '').toUpperCase();

            // Sadece TR ve rakamlar kalsın
            value = value.replace(/[^A-Z0-9]/g, '');

            // TR ekli değilse ekle
            if (!value.startsWith("TR")) {
                value = "TR" + value.replace(/^TR/i, '');
            }

            // Maksimum 26 karakter (TR + 24 rakam)
            if (value.length > 26) {
                value = value.slice(0, 26);
            }

            // 4 karakterde bir boşluk ekle
            e.target.value = value.replace(/(.{4})/g, '$1 ').trim();
        });

        // --- MERSİS sadece sayı kabul etsin ---
        document.getElementById("mersis_no").addEventListener("input", function (e) {
            e.target.value = e.target.value.replace(/\D/g, ''); // sadece rakam
        });

        // --- Form gönderimi ---
        document.getElementById("companyForm").addEventListener("submit", function (e) {
            e.preventDefault();

            const cap = document.getElementById("cap_address").value.trim();
            const iban = document.getElementById("iban_no").value.replace(/\s/g, '');
            const name = document.getElementById("iban_name").value.trim();
            const mersis = document.getElementById("mersis_no").value.trim();

            // Basit doğrulamalar
            if (!cap || !iban || !name || !mersis) {
                alert("Lütfen tüm alanları doldurun!");
                return;
            }

            if (iban.length !== 26) {
                alert("Lütfen geçerli bir IBAN giriniz. (26 karakter olmalı)");
                return;
            }

            if (mersis.length !== 16) {
                alert("MERSİS numarası 16 haneli olmalıdır.");
                return;
            }

            alert("Form başarıyla kaydedildi!");
        });
    </script>
    <script>
        /**
         *
         * You can write your JS code here, DO NOT touch the default style file
         * because it will make it harder for you to update.
         *
         */

        "use strict";

        function readURL(input, previewImage) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#' + previewImage).attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        // Add the following code if you want the name of the file appear on select
        $(".custom-file-input").on("change", function () {
            let fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });

        if (jQuery().summernote) {
            $(".summernote").summernote({
                dialogsInBody: true,
                minHeight: 250,
            });
            $(".summernote-simple").summernote({
                dialogsInBody: true,
                minHeight: 150,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough']],
                    ['para', ['paragraph']]
                ]
            });
        }

        // Timepicker
        if (jQuery().timepicker && $(".timepicker").length) {
            $(".timepicker").timepicker({
                icons: {
                    up: 'fas fa-chevron-up',
                    down: 'fas fa-chevron-down'
                }
            });
        }

        async function initMap() {

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                        getLatLongPosition(position);
                    },
                    function (error) {
                        console.log('Location access denied. Using default location.')
                        getLatLongPosition({
                            coords: {
                                latitude: {{$restaurant->lat}},
                                longitude: {{$restaurant->long}}
                            }
                        }); // Default: Dhaka
                    }
                );
            } else {
                alert("Sorry, your browser does not support HTML5 geolocation.");
            }

            function getLatLongPosition(position) {

                let latitude = position.coords.latitude;
                let longitude = position.coords.longitude;

                const myLatlng = {lat: latitude, lng: longitude};

                const map = new google.maps.Map(document.getElementById("googleMap"), {
                    zoom: 15,
                    center: myLatlng,
                });

                // Create the initial InfoWindow.
                let infoWindow = new google.maps.InfoWindow({
                    content: "Click the map to get latitude & longitude!",
                    position: myLatlng,
                });

                infoWindow.open(map);
                // Configure the click listener.
                var marker;

                map.addListener("click", (mapsMouseEvent) => {
                    // Close the current InfoWindow.
                    infoWindow.close();
                    // Create a new InfoWindow.
                    infoWindow = new google.maps.InfoWindow({
                        position: mapsMouseEvent.latLng,
                    });

                    var latLng = mapsMouseEvent.latLng.toJSON();
                    $('#lat').val(latLng.lat);
                    $('#long').val(latLng.lng);
                    if (marker)
                        marker.setMap(null);
                    marker = new google.maps.Marker({
                        position: myLatlng,
                        map,
                        draggable: true,
                        title: "Your current location.",
                    });

                    changeMarkerPosition(latLng, marker)

                });

                marker = new google.maps.Marker({
                    position: myLatlng,
                    map,
                    draggable: true,
                    title: "Your current location.",
                });
            }
        }

        function changeMarkerPosition(latLng, marker) {
            var latlng = new google.maps.LatLng(latLng.lat, latLng.lng);
            marker.setPosition(latlng);
        }

        $(document).ready(function () {
            $('.select2').select2();
        });
    </script>

    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key={{ setting('google_map_api_key') }}&libraries=places&callback=initMap"></script>
    <script src="{{ asset('js/restaurant/create.js') }}"></script>
@endpush
