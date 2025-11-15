@extends('admin.app')

@push('css')
    <link rel="stylesheet" href="{{ asset('backend/lib/bootstrap-social/bootstrap-social.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/lib/summernote/summernote-bs4.css') }}">
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="custome-breadcrumb">
            {{ Breadcrumbs::render('menu-items/view') }}
            </div>
        </div>


        <div class="col-12">
            <div class="grid grid-cols-1 sm:grid-cols-5 mb-4 sm:mb-0">
                <button type="button" class="db-tabBtn active" data-tab="#information">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>{{ __('levels.coupon_info') }}</span>
                </button>
            </div>

            <div class="db-tabDiv active" id="information">

                @if(session('success'))
                    <div style="color: green" class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="db-card col-lg-3 mt-2">
                    <form action="{{ route('admin.menu-items.temporary-close', $menuItem->id) }}" method="POST">
                        @csrf
                        <label class="db-field-title"
                               for="temporary_close">Belirli Süre Ürünü Kapat</label>
                        <select name="minutes" class="db-field-control" onchange="this.form.submit()">
                            <option value="">---</option>
                            <option value="15">15 dk</option>
                            <option value="30">30 dk</option>
                            <option value="45">45 dk</option>
                            <option value="60">60 dk</option>
                            <option value="unlimited">Süresiz</option>
                        </select>
                    </form>
                </div>


                    <br>
                    <br>
                <ul class="db-list multiple">
                    <li class="db-list-item">
                        <span class="db-list-item-title">{{ __('levels.name') }}</span>
                        <span class="db-list-item-text">{{ $menuItem->name }}</span>
                    </li>
                    <li class="db-list-item">
                        <span class="db-list-item-title">{{ __('levels.status') }}</span>
                        <span class="db-list-item-text">{!! $menuItem->statusName !!}</span>
                    </li>
                    <li class="db-list-item">
                        <span class="db-list-item-title">{{ __('levels.created_date') }}</span>
                        <span class="db-list-item-text">{{ $menuItem->created_at->diffForHumans() }}</span>
                    </li>
                    <li class="db-list-item">
                        <span class="db-list-item-title">{{ __('levels.description') }}</span>
                        <span class="db-list-item-text">{{ strip_tags($menuItem->description) }}</span>
                    </li>
                </ul>
                <hr class="py-3">
                @if(!blank($menuItem->image))
                    <div class="col-lg-4 sm:col-4 mt-4">
                        <div class="db-card p-3">
                            <img class="d-block w-100 h-232 rounded" src="{{ $menuItem->image }}">
                        </div>
                    </div>
                @endif
            </div>
        </div>
z
    </div>
@endsection
