@extends('admin.setting.index')

@section('admin.setting.breadcrumbs')
<div class="row">
	<div class="col-12">
		<div class="custome-breadcrumb">
		{{ Breadcrumbs::render('settings/sms') }}
		</div>
	</div>
</div>
@endsection

@section('admin.setting.layout')
    <!-- SITE SETTING -->
    <div id="site" class="db-card">
        <div class="db-card-header">
            <h3 class="db-card-title">{{ __('setting.sms_setting') }}</h3>
        </div>
        <div class="db-card-body">
            <form class="form-horizontal" role="form" method="POST"
                  action="{{ route('admin.setting.sms-update') }}">
                @csrf
                <div class="form-row">
                    <div class="form-col-12 sm:form-col-6">
                        <label class="db-field-title required"
                               for="twilio_auth_token">NetGsm Usercode</label>
                        <input name="netgsm_usercode" id="netgsm_usercode" type="text"
                               class="db-field-control {{ $errors->has('netgsm_usercode') ? ' invalid ' : '' }}"
                               value="{{ old('netgsm_usercode', setting('netgsm_usercode')) }}">
                        @if ($errors->has('netgsm_usercode'))
                            <small class="db-field-alert">{{ $errors->first('netgsm_usercode') }}</small>
                        @endif
                    </div>
                    <div class="form-col-12 sm:form-col-6">
                        <label class="db-field-title required"
                               for="twilio_account_sid">Netgsm Password</label>
                        <input name="netgsm_password" id="netgsm_password" type="text"
                               class="db-field-control {{ $errors->has('netgsm_password') ? ' invalid ' : '' }}"
                               value="{{ old('netgsm_password', setting('netgsm_password')) }}">
                        @if ($errors->has('netgsm_password'))
                            <small class="db-field-alert">{{ $errors->first('netgsm_password') }}</small>
                        @endif
                    </div>
                    <div class="form-col-12 sm:form-col-6">
                        <label class="db-field-title required" for="netgsm_header">Netgsm Header</label>
                        <input name="netgsm_header" id="netgsm_header" type="text"
                               class="db-field-control {{ $errors->has('netgsm_header') ? ' invalid ' : '' }}"
                               value="{{ old('netgsm_header', setting('netgsm_header')) }}">
                        @if ($errors->has('netgsm_header'))
                            <small class="db-field-alert">{{ $errors->first('netgsm_header') }}</small>
                        @endif
                    </div>
                    <div class="form-col-12 sm:form-col-6">
                        <label class="db-field-title required">{{ __('levels.status') }}</label>
                        <div class="db-field-down-arrow">
                            <select name="netgsm_disabled" id="netgsm_disabled"
                                    class="db-field-control appearance-none @error('netgsm_disabled') invalid @enderror">
                                <option value="1" {{ (old('netgsm_disabled', setting('netgsm_disabled')) == 1) ? 'selected' : '' }}> {{ __('setting.enable') }}</option>
                                <option value="0" {{ (old('netgsm_disabled', setting('netgsm_disabled')) == 0) ? 'selected' : '' }}> {{ __('setting.disable') }}</option>
                            </select>
                        </div>
                        @error('netgsm_disabled')
                        <small class="db-field-alert">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-col-12">
                        <button class="db-btn text-white bg-primary">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>{{ __('setting.update_sms_setting') }}</span>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection
