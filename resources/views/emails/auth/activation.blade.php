@component('mail::message')
    # Hesap Aktivasyonu

    Merhaba,

    Kayıt olduğunuz için teşekkür ederiz.

    Hesabınızı kullanmaya başlayabilmek için lütfen aşağıdaki butona tıklayarak hesabınızı aktive edin.

    @component('mail::button', ['url' => route('activation.activate', $token), 'color' => 'green'])
        Hesabı Aktifleştir
    @endcomponent

    Teşekkürler,<br>
    {{ config('app.name') }}
@endcomponent
