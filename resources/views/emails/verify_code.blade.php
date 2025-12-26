<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Doğrulama Kodunuz</title>
</head>
<body>
<h2>Merhaba,</h2>
<p>Doğrulama kodunuz:</p>
<h1 style="color:#2c7be5;">{{ $code }}</h1>
<p>Bu kodu 5 dakika içinde kullanabilirsiniz.</p>
<p>Teşekkürler,<br>{{ config('app.name') }} Ekibi</p>
</body>
</html>
