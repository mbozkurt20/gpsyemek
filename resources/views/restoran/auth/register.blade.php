@extends('frontend.layouts.app')
@push('style')
    <link rel="stylesheet" href="{{ asset('frontend/lib/inttelinput/css/intlTelInput.css') }}">
@endpush

@section('main-content')
    <!--========= REGISTER PART START =======-->
    <section class="auth">
        <div class="container">
            <div class="row">
                <div class="col-12 col-md-8 col-lg-7">
                    <div class="auth-content">
                        <nav class="auth-navs">
                            <a class="nav-link" href="{{ route('login') }}"> {{ __('register.login') }} </a>
                            <a class="nav-link active" href="{{ route('register') }}"> {{ __('register.register') }}</a>
                        </nav>
                        <div class="auth-tabs">
                            <form method="POST" class="register" action="{{ route('register') }}">
                                @csrf
                                <ul class="auth-types">
                                    <li>
                                        <input type="radio" id="CustomerRegister" name="roles" value="2"
                                        {{ old('roles', 2)== 2 ? 'checked' : 'checked'}}>
                                        <label for="CustomerRegister">{{ __('register.customer') }}</label>
                                    </li>
                                   {{--

                                     <li>
                                        <input type="radio" id="RestaurantOwnerRegister" name="roles" value="3"
                                        {{ old('roles')== 3 ? 'checked' : ''}}>
                                        <label for="RestaurantOwnerRegister">{{ __('register.restaurant_owner') }}</label>
                                    </li>
                                    <li>
                                        <input type="radio" id="DeliveryRegister" name="roles" value="4"
                                        {{ old('roles')== 4 ? 'checked' : ''}}>
                                        <label for="DeliveryRegister">{{ __('register.delivery_man') }}</label>
                                    </li>
                                    --}}
                                </ul>

                                <div class="row">
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label for="first_name"
                                                class="form-label required">{{ __('register.first_name') }}</label>
                                            <input name="first_name" value="{{ old('first_name') }}" type="text"
                                                class="form-control @if ($errors->has('first_name')) is-invalid @endif"
                                                placeholder="Ad">
                                            @if ($errors->has('first_name'))
                                                <div class="invalid-feadback text-danger" role="alert">
                                                    {{ $errors->first('first_name') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label for="last_name"
                                                class="form-label required">{{ __('register.last_name') }}</label>
                                            <input name="last_name" value="{{ old('last_name') }}" type="text"
                                                class="form-control @if ($errors->has('last_name')) is-invalid @endif"
                                                placeholder="Soyad">
                                            @if ($errors->has('last_name'))
                                                <div class="invalid-feadback text-danger" role="alert">
                                                    {{ $errors->first('last_name') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label for="username" class="form-label required">{{ __('register.username') }}</label>
                                            <input id="username" name="username" value="{{ old('username') }}"
                                                type="text"
                                                class="form-control @if ($errors->has('username')) is-invalid @endif"
                                                placeholder="Kullanıcı Adı">
                                            @if ($errors->has('username'))
                                                <div class="invalid-feadback text-danger" role="alert">
                                                    {{ $errors->first('username') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label for="register_email"
                                                class="form-label required">{{ __('register.email_address') }} </label>
                                            <input name="register_email" value="{{ old('register_email') }}" type="email"
                                                class="form-control @if ($errors->has('register_email')) is-invalid @endif"
                                                placeholder="info@example.com">
                                            @if ($errors->has('register_email'))
                                                <span class="is-invalid" role="alert">
                                                    <strong
                                                        class="text-danger">{{ $errors->first('register_email') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-label required">{{ __('register.phone') }} </label>
                                            <input
                                                class="form-control mobilenumber @error('mobile') is-invalid @enderror phone"
                                                type="tel" id="number" name="phone" onkeypress='validate(event)'>

                                            <input type="hidden" id="code" name="countrycode" value="90">
                                            <input type="hidden" id="code_name" name="countrycodename" value="tr">

                                            @error('phone')
                                                <div class="invalid-feedback d-block">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="address" class="form-label required">{{ __('register.address') }}</label>
                                            <input id="address" name="address" value="{{ old('address') }}"
                                                type="text"
                                                class="form-control @if ($errors->has('address')) is-invalid @endif"
                                                placeholder="House#10, Section#1, Dhaka 1216, Bangladesh">
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label for="password"
                                                class="form-label required">{{ __('register.password') }}</label>
                                            <input name="password" id="password"
                                                class="form-control @if ($errors->has('password')) is-invalid @endif"
                                                type="password" placeholder="Şifre oluştur">
                                            @error('password')
                                                <div class="invalid-feedback d-block">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label for="password2"
                                                class="form-label required">{{ __('register.repeat_password') }}</label>
                                            <input name="password_confirmation"
                                                class="form-control @if ($errors->has('password_confirmation')) is-invalid @endif"
                                                type="password" placeholder="Şİfre Tekrarı">
                                            @if ($errors->has('password_confirmation'))
                                                <span class="is-invalid" role="alert">
                                                    <strong
                                                        class="text-danger">{{ $errors->first('password_confirmation') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <input type="submit" class="form-btn mt-2" name="register" value="{{ __('register.register') }}" />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <img class="auth-banner" src="{{ asset('frontend/images/auth.jpg') }}" alt="auth">
    </section>
    <!--======== REGISTER PART END ======-->
@endsection

@push('js')
    <script defer src="{{ asset('frontend/lib/inttelinput/js/intlTelInput-jquery.js') }}"></script>
    <script defer src="{{ asset('frontend/lib/inttelinput/js/intlTelInput.js') }}"></script>
    <script defer src="{{ asset('frontend/lib/inttelinput/js/utils.js') }}"></script>
    <script defer src="{{ asset('frontend/lib/inttelinput/js/data.js') }}"></script>
    <script defer src="{{ asset('frontend/lib/inttelinput/js/init.js') }}"></script>
    <script src="{{ asset('js/phone_validation/index.js') }}"></script>
@endpush
