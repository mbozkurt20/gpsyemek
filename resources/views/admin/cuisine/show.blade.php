@extends('admin.app')
@section('content')

<div class="row">
    <div class="col-12">
        <div class="custome-breadcrumb">
        {{ Breadcrumbs::render('cuisines/edit') }}
        </div>
    </div>
</div>

@endsection

@push('js')
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
    <script src="{{ asset('js/cuisine/edit.js') }}"></script>
@endpush
