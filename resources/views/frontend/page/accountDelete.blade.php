@extends('frontend.layouts.app')

@section('main-content')
    <div class="container py-5">

        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="card shadow-sm">
                    <div class="card-body">

                        <h4 class="mb-3">Hesabınızı Silin</h4>

                        <p class="text-muted">
                            Aşağıya Gpsyemek hesabınıza kayıtlı e-posta adresinizi girin.
                            Bu işlem geri alınamaz ve **hesabınız sistemden kalıcı olarak silinir**.
                        </p>

                        <form action="{{ route('email.submit') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Kayıtlı E-posta Adresiniz</label>
                                <input type="email" name="email" id="email" class="form-control"
                                       placeholder="ornek@mail.com" required>
                            </div>

                            <button type="submit" class="btn btn-danger w-100">
                                Hesabımı Kalıcı Olarak Sil
                            </button>
                        </form>

                        @if(session('success'))
                            <div class="alert alert-success mt-3">
                                {{ session('success') }}
                            </div>
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
    <br>
    <br>
    <br>
@endsection
