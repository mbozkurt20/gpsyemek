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
                                            <input name="first_name" value="{{ old('first_name') }}" type="text" required
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
                                            <input name="last_name" value="{{ old('last_name') }}" type="text" required
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
                                            <input id="username" name="username" value="{{ old('username') }}" required
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
                                            <input name="register_email" value="{{ old('register_email') }}" type="email" required
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
                                            <input required
                                                   value="{{ old('phone') }}"
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
                                                type="text" required
                                                class="form-control @if ($errors->has('address')) is-invalid @endif"
                                                placeholder="Ali Yılmaz mah. 2401.sokak  Muğla Türkiye">
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label for="password"
                                                class="form-label required">{{ __('register.password') }}</label>
                                            <input name="password" id="password" required
                                                   value="{{ old('password') }}"
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
                                            <input name="password_confirmation" required
                                                   value="{{ old('password_confirmation') }}"
                                                class="form-control @if ($errors->has('password_confirmation')) is-invalid @endif"
                                                type="password" placeholder="Şifre Tekrarı">
                                            @if ($errors->has('password_confirmation'))
                                                <span class="is-invalid" role="alert">
                                                    <strong
                                                        class="text-danger">{{ $errors->first('password_confirmation') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group mt-3">
                                        {{-- Aydınlatma Metni Onayı --}}
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="aydinlatma" name="is_membership_conditions" required>
                                            <label class="form-check-label " style="cursor: pointer;font-size: 14px" for="aydinlatma">
                                                Tarafıma avantajlı tekliflerin sunulabilmesi amacıyla kişisel verilerimin işlenmesine ve paylaşılmasına
                                                <span style="text-decoration: underline" data-bs-toggle="modal" data-bs-target="#aydinlatmaModal">
                                                    açık rıza
                                                </span>
                                                veriyorum.
                                            </label>
                                        </div>

                                        {{-- Elektronik İleti Onayı --}}
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="iletikabul" name="is_electronic_message">
                                            <label class="form-check-label" style="cursor: pointer;font-size: 14px" for="iletikabul">
                                                {{ __('auth.elektronik_ileti_kabul') }}
                                            </label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" required type="checkbox" id="aydinlatma2" name="is_illumination_text">
                                            <label class="form-check-label" style="cursor: pointer;font-size: 14px" for="aydinlatma2">
                                                Kişisel verilerimin işlenmesine yönelik <span style="text-decoration: underline"  data-bs-toggle="modal" data-bs-target="#aydinlatmaModal">aydınlatma metnini </span> okudum ve anladım.
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Aydınlatma Metni Modal -->
                                    <div class="modal fade" id="aydinlatmaModal" tabindex="-1" aria-labelledby="aydinlatmaModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <div class="modal-body">

                                                    {{-- Tab Başlıkları --}}
                                                    <ul class="nav nav-tabs mt-4 mb-5" id="aydinlatmaTab" role="tablist">
                                                        <li class="nav-item" role="presentation">
                                                            <button class="nav-link active" style="color: #259A38" id="tab1-tab" data-bs-toggle="tab" data-bs-target="#tab1"
                                                                    type="button" role="tab">{{ __('auth.tab1_baslik') }}</button>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <button class="nav-link" id="tab2-tab" style="color: #259A38" data-bs-toggle="tab" data-bs-target="#tab2"
                                                                    type="button" role="tab">{{ __('auth.tab2_baslik') }}</button>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <button class="nav-link" id="tab3-tab" style="color: #259A38" data-bs-toggle="tab" data-bs-target="#tab3"
                                                                    type="button" role="tab">{{ __('auth.tab3_baslik') }}</button>
                                                        </li>
                                                    </ul>

                                                    {{-- Tab İçerikleri --}}
                                                    <div class="tab-content mt-5">
                                                        <div class="tab-pane fade show active" id="tab1" role="tabpanel">
                                                            @include('frontend.agreements.uyelik')
                                                        </div>
                                                        <div class="tab-pane fade" id="tab2" role="tabpanel">
                                                            @include('frontend.agreements.aydinlatma')
                                                        </div>
                                                        <div class="tab-pane fade" id="tab3" role="tabpanel">
                                                            @include('frontend.agreements.acik-riza')
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="g-recaptcha mb-2 ot-2 mx-auto text-center" data-sitekey="{{ setting('recaptcha_site_key') }}"></div>

                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

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
    <script src="https://www.google.com/recaptcha/api.js?hl=tr" async defer></script>
    <script defer src="{{ asset('frontend/lib/inttelinput/js/intlTelInput-jquery.js') }}"></script>
    <script defer src="{{ asset('frontend/lib/inttelinput/js/intlTelInput.js') }}"></script>
    <script defer src="{{ asset('frontend/lib/inttelinput/js/utils.js') }}"></script>
    <script defer src="{{ asset('frontend/lib/inttelinput/js/data.js') }}"></script>
    <script defer src="{{ asset('frontend/lib/inttelinput/js/init.js') }}"></script>
    <script src="{{ asset('js/phone_validation/index.js') }}"></script>
@endpush
