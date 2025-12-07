@extends('frontend.layouts.app')

@section('main-content')
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <div class="container py-5">

        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="card shadow-sm">
                    <div class="card-body">

                        <h4 class="mb-3">Hesabınızı Kalıcı Olarak Silin</h4>

                        <p class="text-muted">
                            Lütfen Gpsyemek hesabınıza kayıtlı e-posta adresi ve şifrenizi girin.
                            Bu işlem geri alınamaz; hesabınız kalıcı olarak silinir.
                        </p>

                        <form action="{{ route('email.submit') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">E-posta Adresi</label>
                                <input type="email" name="email" id="email" class="form-control"
                                       placeholder="ornek@mail.com" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Şifre</label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="******" required>
                            </div>

                            <button type="submit" class="btn btn-danger w-100">
                                Hesabımı Kalıcı Olarak Sil
                            </button>
                        </form>

                        @if(session('success'))
                            <div class="alert alert-success mt-3">{{ session('success') }}</div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger mt-3">{{ session('error') }}</div>
                        @endif

                    </div>
                </div>

            </div>
        </div>

    </div>

    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
@endsection
