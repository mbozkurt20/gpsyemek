@extends('admin.app')

@push('css')
    <link rel="stylesheet" href="{{ asset('backend/lib/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/lib/bootstrap-social/bootstrap-social.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/lib/summernote/summernote-bs4.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/lib/bootstrap-timepicker/css/bootstrap-timepicker.min.css') }}">
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="custome-breadcrumb">
            {{ Breadcrumbs::render('restaurant/edit') }}
        </div>
        </div>

        <div class="col-12">
            <form action="{{ route('admin.restaurant.restaurant-update', $restaurant) }}" method="POST" enctype="multipart/form-data">
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
                                        <label class="db-field-title required" for="name">{{ __('levels.name') }}</label>
                                        <input type="text" name="name" id="name" class="db-field-control @error('name') invalid @enderror" value="{{ old('name', $restaurant->name) }}">

                                        @error('name')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-6 sm:form-col-6 md:form-col-6">
                                        <label class="db-field-title" for="opening_time">{{ __('levels.opening_time') }}</label>
                                        <input type="time" name="opening_time" id="opening_time" class="db-field-control @error('opening_time') invalid @enderror" value="{{ old('opening_time', $restaurant->opening_time) }}">

                                        @error('opening_time')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-6 sm:form-col-6 md:form-col-6">
                                        <label class="db-field-title" for="closing_time">{{ __('levels.closing_time') }}</label>
                                        <input type="time" name="closing_time" id="closing_time" class="db-field-control @error('closing_time') invalid @enderror" value="{{ old('closing_time', $restaurant->closing_time) }}">

                                        @error('closing_time')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-12 sm:form-col-12 md:form-col-12">
                                        <label class="db-field-title" for="cuisines">{{ __('levels.cuisines') }}</label>
                                        <div class="db-field-down-arrow">
                                            <select name="cuisines[]" id="cuisines" class="db-field-control select2 appearance-none @error('cuisines') invalid @enderror" multiple="multiple">
                                                <option value="">---</option>
                                                @if(!blank($cuisines))
                                                    @foreach($cuisines as $cuisine)
                                                    @if(in_array($cuisine->id, $restaurant_cuisines))
                                                        <option value="{{ $cuisine->id }}" selected>{{ $cuisine->name }}</option>
                                                    @else
                                                        <option value="{{ $cuisine->id }}">{{ $cuisine->name }}</option>
                                                    @endif
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        @error('cuisines')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                   {{--
                                    <div class="form-col-12">
                                        <label class="db-field-title required" for="address">{{ __('levels.address') }}</label>
                                        <input type="text" name="address"
                                            class="db-field-control @error('address') invalid @enderror"
                                            id="address" value="{{ old('address', $restaurant->address) }}"></input>
                                        @error('address')
                                            <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>
                                   --}}

                                    <div class="form-col-12">
                                        <label class="db-field-title" for="description">{{ __('levels.description') }}</label>
                                        <textarea name="description"
                                            class="db-field-control @error('description') invalid @enderror"
                                            style="height: 5rem"
                                            id="editor">{{ old('description', $restaurant->description) }}</textarea>
                                        @error('description')
                                            <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-12 sm:form-col-12 md:form-col-12">
                                        <label class="db-field-title" for="customFile">{{ __('restaurant.logo') }}</label>

                                        <input type="file" name="restaurant_logo" id="customFile" class="db-field-control @error('restaurant_logo') invalid @enderror">

                                        @if ($errors->has('restaurant_logo'))
                                        <small class="db-field-alert">{{ $errors->first('restaurant_logo') }}</small>
                                        @endif
                                    </div>

                                    <div class="form-col-12 sm:form-col-12 md:form-col-12">
                                        <label class="db-field-title" for="customFile">{{ __('restaurant.background_image') }}</label>

                                        <input type="file" name="image" id="customFile" class="db-field-control @error('image') invalid @enderror">

                                        @if ($errors->has('image'))
                                        <small class="db-field-alert">{{ $errors->first('image') }}</small>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="db-card mt-5 p-4">
                            <div class="mb-5">
                                <label for="cap_address" class="block text-sm font-medium text-gray-700">CAP Adresi</label>
                                <input value="{{$restaurant->payInformation->cap_address}}" required type="text" id="cap_address" name="cap_address"
                                       class="mt-1 db-field-control block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Örn: cap@firma.com">
                            </div>

                            <div class="mb-5">
                                <label for="iban_no" class="block text-sm font-medium text-gray-700">IBAN Numarası</label>
                                <input value="{{$restaurant->payInformation->iban_no}}" required type="text" id="iban_no" name="iban_no"
                                       class="mt-1 db-field-control block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="TR00 0000 0000 0000 0000 0000 00">
                            </div>

                            <div class="mb-5">
                                <label for="iban_name" class="block text-sm font-medium text-gray-700">IBAN Adı</label>
                                <input value="{{$restaurant->payInformation->iban_name}}" required type="text" id="iban_name" name="iban_name"
                                       class="mt-1  db-field-control block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Hesap Sahibinin Adı">
                            </div>

                            <div class="mb-5">
                                <label for="mersis_no" class="block text-sm font-medium text-gray-700">MERSİS Numarası</label>
                                <input value="{{$restaurant->payInformation->mersis_no}}" type="text" id="mersis_no" name="mersis_no"
                                       class="mt-1 db-field-control block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Örn: 0123456789012345">
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
                                        <label class="db-field-title required" for="name">{{ __('levels.latitude') }}</label>
                                        <input type="text" name="lat" id="lat" class="db-field-control @error('lat') invalid @enderror" value="{{ old('lat', $restaurant->lat) }}">

                                        @error('lat')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-6 sm:form-col-6 md:form-col-6">
                                        <label class="db-field-title required" for="name">{{ __('levels.longitude') }}</label>
                                        <input type="text" name="long" id="long" class="db-field-control @error('long') invalid @enderror" value="{{ old('long', $restaurant->long) }}">

                                        @error('long')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-col-12 sm:form-col-12 md:form-col-12">
                                        <label class="db-field-title required" for="address">Adres</label>
                                        <textarea name="address" id="address-input" class="db-field-control">{{ old('address', $restaurant->address) }}</textarea>
                                        @error('address')
                                        <small class="db-field-alert">{{ $message }}</small>
                                        @enderror

                                        <button type="button" id="show-on-map" class="db-btn rounded-full text-white mt-2" style="background: #14179e">Haritada Göster</button>
                                    </div>
                                </div>
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
                                            <select name="delivery_status" class="db-field-control appearance-none @error('delivery_status') invalid @enderror">
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
                                            <select name="pickup_status" class="db-field-control appearance-none @error('pickup_status') invalid @enderror">
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
                                            <select name="table_status" class="db-field-control appearance-none @error('table_status') invalid @enderror">
                                                <option value="">---</option>
                                                @foreach(trans('table_statuses') as $table_statusKey => $table_status)
                                                    <option value="{{ $table_statusKey }}"
                                                        {{ (old('table_status', $restaurant->table_status) == $table_statusKey) ? 'selected' : '' }} {{ $table_statusKey }}>
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
                                            <select name="current_status" class="db-field-control appearance-none @error('current_status') invalid @enderror">
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

                                    <div class="form-col-12 sm:form-col-12 md:form-col-12">
                                        <label class="db-field-title required">{{ __('levels.waiter_status') }}</label>
                                        <div class="db-field-down-arrow">
                                            <select name="waiter_status" class="db-field-control appearance-none @error('waiter_status') invalid @enderror">
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
                </div>
            </form>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('backend/lib/select2/dist/js/select2.full.min.js') }}"></script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ setting('google_map_api_key') }}&libraries=places&callback=initMap"></script>
    <script src="{{ asset('js/restaurant/create.js') }}"></script>
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
@endpush
