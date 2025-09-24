@extends('admin.setting.index')

@section('admin.setting.breadcrumbs')
<div class="row">
	<div class="col-12">
		<div class="custome-breadcrumb">
		{{ Breadcrumbs::render('settings/social') }}
		</div>
	</div>
</div>
@endsection

@section('admin.setting.layout')
    <!-- SITE SETTING -->
    <div id="site" class="db-card">
        <div class="db-card-header">
            <h3 class="db-card-title">{{ __('setting.recaptcha_setting') }}</h3>
        </div>
        <div class="db-card-body">
            <form role="form" method="POST" action="{{ route('admin.setting.recaptcha') }}">
                @csrf
                    <div class="form-row">
                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title" for="recaptcha_site_key">{{ __('levels.recaptcha_site_key') }}</label>
                            <input name="recaptcha_site_key" id="recaptcha_site_key" type="text"
                                class="db-field-control {{ $errors->has('recaptcha_site_key') ? ' invalid ' : '' }}"
                                value="{{ old('recaptcha_site_key', setting('recaptcha_site_key')) }}">
                            @if ($errors->has('recaptcha_site_key'))
                            <small class="db-field-alert">{{ $errors->first('recaptcha_site_key') }}</small>
                            @endif
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title" for="recaptcha_secret_key">{{ __('levels.recaptcha_secret_key') }}</label>
                            <input name="recaptcha_secret_key" id="recaptcha_secret_key" type="text"
                                class="db-field-control {{ $errors->has('recaptcha_secret_key') ? ' invalid ' : '' }}"
                                value="{{ old('recaptcha_secret_key', setting('recaptcha_secret_key')) }}">
                            @if ($errors->has('recaptcha_secret_key'))
                            <small class="db-field-alert">{{ $errors->first('recaptcha_secret_key') }}</small>

                            @endif
                        </div>
                        <div class="form-col-12">
                            <button class="db-btn text-white bg-primary">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>{{ __('setting.update_social_setting') }}</span>
                            </button>
                        </div>
                    </div>
            </form>
        </div>
    </div>
@endsection

