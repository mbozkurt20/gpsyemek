@extends('frontend.layouts.app')

@section('main-content')
    <div class="min-vh-100 d-flexalign-items-center">
        <iframe src="https://www.paytr.com/odeme/guvenli/{{ $token }}"
                frameborder="0"
                scrolling="no"
                style="width: 100%; height: 600px;">
        </iframe>
    </div>
@endsection
