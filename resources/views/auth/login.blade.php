@extends('frontend.layouts.app')
@section('main-content')
    <section class="auth">
        <div class="container">
            <div class="row">
                <div class="col-12 col-md-8 col-lg-7">
                    <div class="auth-content">
                        <nav class="auth-navs">
                            <a class="nav-link active" href="{{ route('login') }}"> {{ __('auth.login') }} </a>
                            <a class="nav-link" href="{{ route('register') }}"> {{ __('auth.register') }}</a>
                        </nav>
                        <div class="auth-tabs">
                            <div class="auth-header">
                                <h3>{{ __('auth.welcome_back') }}</h3>
                                <p>{{ __('auth.enter_login_details') }}</p>
                            </div>
                            <form method="POST" class="login" action="{{ route('login') }}">
                                @csrf
                                <input type="hidden" name="type"
                                       value="{{request()->has('type') ? request()->type : 'frontend'}}">

                                <div class="form-group">
                                    <label for="email" class="form-label"> {{ __('auth.email') }} </label>
                                    <p class="border border-green-700 py-2 rounded px-2 font-bold">{{ session()->has('verified_value') ? session('verified_value') : old('email') }}</p>
                                    <input style="display: none" id="demoemail" type="email"
                                           class="form-control  @if ($errors->has('email') || session('block')) is-invalid @endif"
                                           name="email" value="{{ session()->has('verified_value') ? session('verified_value') : old('email') }}"
                                           autocomplete="email" autofocus
                                           placeholder="Email">
                                    <small class="form-alert green">{{ __('auth.email_privacy') }}</small>

                                    @if ($errors->has('email'))
                                        <span class="is-invalid" role="alert">
                                            <strong class="text-danger">{{ $errors->first('email') }}</strong>
                                        </span>
                                    @elseif(session('block'))
                                        <span class="is-invalid" role="alert">
                                            <strong>{{ session('block') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="password">{{ __('auth.password') }}</label>
                                    <input placeholder="Şifreniz" id="demopassword" type="password"
                                           class="form-control @if ($errors->has('password')) is-invalid @endif"
                                           name="password" autocomplete="current-password">
                                    @if ($errors->has('password'))
                                        <span class="is-invalid" role="alert">
                                            <strong class="text-danger">{{ $errors->first('password') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="d-flex justify-content-between">
                                    <div class="form-group form-check-group">
                                        <input type="checkbox" id="remember-me" name="check">
                                        <label for="remember-me"> {{ __('auth.remember_me') }}</label>
                                    </div>

                                    <div class="col-md-6 d-flex justify-content-end">
                                        <label for="forgot password">
                                            <a class="linkTxt" href="{{ route('password.request') }}"
                                               class="text-primary">
                                                {{ __('auth.forgot_password') }}
                                            </a>
                                        </label>
                                    </div>
                                </div>

                                <input type="submit" class="form-btn" value="{{ __('auth.login') }}">

                                @if (setting('facebook_key') || setting('google_key'))
                                    <div class="auth-divide"><span>{{ __('auth.or_login_with') }}</span></div>
                                @endif

                                <nav class="auth-sync">
                                    @if (setting('google_key'))
                                        <a href="{{ route('social-login', 'google') }}">
                                            <img src="{{ asset('frontend/images/social/google.png') }}" alt="social">
                                            <span>{{ __('auth.google') }}</span>
                                        </a>
                                    @endif

                                    @if (setting('facebook_key'))
                                        <a href="{{ route('social-login', 'facebook') }}">
                                            <img src="{{ asset('frontend/images/social/facebook.png') }}" alt="social">
                                            <span>{{ __('auth.facebook') }}</span>
                                        </a>
                                    @endif
                                </nav>
                            </form>
                        </div>

                        @if (env('DEMO_MODE'))
                            <div class="card demo-login mx-auto text-center mt-2 border-0">
                                <div class="card-body border-0">
                                    <h5 class="mb-2">{{ __('auth.quick_demo_login') }}</h5>
                                    <div class="buttons">
                                        <button id="demoadmin"
                                                class="btn btn-sm btn-primary">{{ __('auth.admin') }}</button>
                                        <button id="democustomer"
                                                class="btn btn-sm btn-info">{{ __('auth.customer') }}</button>
                                        <button id="demorestaurantowner"
                                                class="btn btn-success btn-sm">{{ __('auth.restaurant_owner') }}</button>
                                        <button id="demodeliveryboy"
                                                class="btn btn-warning btn-sm">{{ __('auth.delivery_boy') }}</button>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
        <img class="auth-banner" src="{{ asset('frontend/images/auth.jpg') }}" alt="auth">
    </section>
@endsection

@push('js')
    <script src="{{ asset('frontend/js/demo-login.js') }}"></script>
@endpush
