@extends('admin.app')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="custome-breadcrumb">
                {{ Breadcrumbs::render('live-orders') }}
            </div>
        </div>
        <div class="col-12">
            <div class="section-body"></div>
        </div>
    </div>

@endsection

@push('css')
    <link  rel="stylesheet" href="{{asset('css/live-orders.css')}}">
@endpush
@push('js')
    <script>
        const liveOrderRoute = "{{ route('admin.orders.get-live-Order') }}";
    </script>

    <script src="{{ asset('js/live-orders/index.js') }}"></script>


@endpush
