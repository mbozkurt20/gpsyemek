@extends('frontend.layouts.app')
@section('main-content')
    <div class="min-h-screen flex items-center justify-center  px-4">
        <div class="bg-white w-full max-w-5xl rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row">

            <!-- Sol Kısım -->
            <div class="bg-[#259a38] text-white md:w-1/2 flex flex-col justify-center items-center p-10 text-center">
                <h2 class="text-3xl  text-white font-bold mb-4">Hoş Geldiniz!</h2>
                <p class="text-lg mb-8">Hesabınıza giriş yapın veya yeni bir hesap oluşturun.</p>

                <div class="flex space-x-4">
                    <a href="{{ route('login') }}"
                       class="bg-white text-[#259a38] font-semibold px-6 py-3 rounded-xl hover:bg-gray-100 transition">
                        Giriş Yap
                    </a>
                    <a href="{{ route('register') }}"
                       class="border-2 border-white px-6 py-3 rounded-xl font-semibold hover:bg-white hover:text-[#259a38] transition">
                        Kayıt Ol
                    </a>
                </div>
            </div>

            <!-- Sağ Kısım -->
            <div class="w-full md:w-1/2 p-10">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4 text-center">Doğrulama</h3>

                @if(setting('facebook_key') || setting('google_key'))
                    <div class="flex justify-center space-x-4 mb-6">
                        @if (setting('google_key'))
                            <a href="{{ route('social-login', 'google') }}"
                               class="flex items-center space-x-2 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition">
                                <img src="{{ asset('frontend/images/social/google.png') }}" class="w-6 h-6" alt="Google">
                                <span>Google</span>
                            </a>
                        @endif

                        @if (setting('facebook_key'))
                            <a href="{{ route('social-login', 'facebook') }}"
                               class="flex items-center space-x-2 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition">
                                <img src="{{ asset('frontend/images/social/facebook.png') }}" class="w-6 h-6" alt="Facebook">
                                <span>Facebook</span>
                            </a>
                        @endif
                    </div>
                @endif

                @if(session('message'))
                    <div class="bg-[#d1fae5] text-[#259a38] p-4 rounded-xl font-medium mb-4">
                        {{ session('message') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-50 text-red-700 p-4 rounded-xl font-medium mb-4">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <!-- Tabs -->
                <div class="flex justify-between mb-6 border-b mt-5">
                    <button id="tabEmailBtn"
                            class="pb-3 border-b-4 border-[#259a38] text-[#259a38] font-bold w-1/2 text-center">
                        Email ile Doğrula
                    </button>
                    <button id="tabPhoneBtn"
                            class="pb-3 text-gray-400 hover:text-[#259a38] w-1/2 text-center">
                        Telefon ile Doğrula     {{ session('type')}}
                    </button>
                </div>


                <!-- Email Form -->
                <div id="tabEmailContent">
                    <form action="{{ route('verify.send') }}" method="POST" class="mb-6">
                        @csrf
                        <input type="hidden" name="type" value="email">
                        <input type="email" value="{{isset($type) && $type == 'email' ? $value : null}}" name="value" placeholder="Email adresiniz" required
                               class="w-full border border-gray-300 rounded-xl p-3 mb-3 focus:ring-2 focus:ring-[#259a38] focus:outline-none">

                        @if(!isset($type))
                            <button type="submit"
                                    class="w-full bg-[#259a38] text-white py-3 rounded-xl hover:bg-[#1e7a2b] font-semibold">
                                Gönder
                            </button>
                        @else
                            <span class="text-green-600">Lütfen mail adresinizi kontrol ediniz...</span>
                        @endif
                    </form>

                    @if(isset($type) && $type == 'email')
                        <form action="{{ route('verify.check') }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="email">
                            <input type="hidden" name="value" value="{{ $value }}">
                            <input type="text" name="otp" placeholder="6 haneli OTP kodunu girin" required
                                   class="w-full border border-gray-300 rounded-xl p-3 mb-3 focus:ring-2 focus:ring-[#259a38] focus:outline-none">
                            <button type="submit"
                                    class="w-full bg-[#259a38] text-white py-3 rounded-xl hover:bg-[#1e7a2b] font-semibold">
                                Doğrula
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Telefon Form -->
                <div id="tabPhoneContent" class="hidden">
                    <form action="{{ route('verify.send') }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="phone">
                        <input id="phoneInput" type="text" value="{{isset($type) && $type == 'phone' ? $value : null}}" name="value" placeholder="5xx xxx xx xx" required
                               class="w-full border border-gray-300 rounded-xl p-3 mb-3 focus:ring-2 focus:ring-[#259a38] focus:outline-none">

                        @if(!isset($type))
                            <button type="submit"
                                    class="w-full bg-[#259a38] text-white py-3 rounded-xl hover:bg-[#1e7a2b] font-semibold">
                                Gönder
                            </button>
                        @endif
                    </form>

                    @if(isset($type) && $type == 'phone')
                        <form action="{{ route('verify.check') }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="phone">
                            <input type="hidden" name="value" value="{{ $value }}">
                            <input type="text" name="otp" placeholder="6 haneli OTP kodunu girin" required
                                   class="w-full border border-gray-300 rounded-xl p-3 mb-3 focus:ring-2 focus:ring-[#259a38] focus:outline-none">
                            <button type="submit"
                                    class="w-full bg-[#259a38] text-white py-3 rounded-xl hover:bg-[#1e7a2b] font-semibold">
                                Doğrula
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script>
        const tabEmailBtn = document.getElementById('tabEmailBtn');
        const tabPhoneBtn = document.getElementById('tabPhoneBtn');
        const tabEmailContent = document.getElementById('tabEmailContent');
        const tabPhoneContent = document.getElementById('tabPhoneContent');

        tabEmailBtn.addEventListener('click', () => {
            tabEmailContent.classList.remove('hidden');
            tabPhoneContent.classList.add('hidden');
            tabEmailBtn.classList.add('border-b-4', 'border-[#259a38]', 'text-[#259a38]', 'font-bold');
            tabPhoneBtn.classList.remove('border-b-4', 'border-[#259a38]', 'text-[#259a38]', 'font-bold');
            tabPhoneBtn.classList.add('text-gray-400');
        });

        tabPhoneBtn.addEventListener('click', () => {
            tabPhoneContent.classList.remove('hidden');
            tabEmailContent.classList.add('hidden');
            tabPhoneBtn.classList.add('border-b-4', 'border-[#259a38]', 'text-[#259a38]', 'font-bold');
            tabEmailBtn.classList.remove('border-b-4', 'border-[#259a38]', 'text-[#259a38]', 'font-bold');
            tabEmailBtn.classList.add('text-gray-400');
        });

        const phoneInput = document.getElementById('phoneInput');
        phoneInput.addEventListener('input', (e) => {
            let x = e.target.value.replace(/\D/g, '').substring(0, 10);
            let formatted = x.replace(/(\d{3})(\d{3})(\d{2})(\d{2})/, '$1 $2 $3 $4');
            e.target.value = formatted.trim();
        });
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
@endsection
