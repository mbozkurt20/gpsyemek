@extends('frontend.layouts.app')

@section('main-content')
    <div class="container text-center" style="margin-top:50px;">
        <h2>3D Güvenli Ödeme</h2>
        <div id="iyzipay-checkout-form">
            {!! $checkoutFormContent !!}
        </div>
    </div>
@endsection
