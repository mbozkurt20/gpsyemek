@extends('frontend.layouts.app')
@section('content')
    <div class="container">
        <header>
            <div>
                <h1>Gizlilik Politikası</h1>
                <div class="muted">Gpsyemek — gpsyemek.com ve mobil uygulama</div>
            </div>
        </header>


        <div class="card" role="article" aria-label="Gpsyemek Gizlilik Politikası">


            <nav aria-label="İçindekiler">
                <div class="toc">
                    <strong>İçindekiler</strong>
                    <ul>
                        <li><a href="#toplanan-bilgiler">1. Toplanan Bilgiler</a></li>
                        <li><a href="#kullanim-amaclari">2. Verilerin Kullanım Amaçları</a></li>
                        <li><a href="#veri-paylasimi">3. Verilerin Paylaşımı</a></li>
                        <li><a href="#veri-guvenligi">4. Veri Güvenliği</a></li>
                        <li><a href="#cookie">5. Çerezler (Cookies)</a></li>
                        <li><a href="#kullanici-haklari">6. Kullanıcı Hakları</a></li>
                        <li><a href="#guncellemeler">7. Güncellemeler</a></li>
                        <li><a href="#iletisim">İletişim Bilgileri</a></li>
                    </ul>
                </div>
            </nav>


            <section id="toplanan-bilgiler">
                <h2>1. Toplanan Bilgiler</h2>
                <p>Platformumuzu kullandığınızda aşağıdaki bilgiler toplanabilir:</p>
                <ul>
                    <li>Ad, soyad, iletişim bilgileri (telefon, e-posta, adres)</li>
                    <li>Ödeme bilgileri (kredi/banka kartı vb. — üçüncü taraf güvenli ödeme sağlayıcıları aracılığıyla işlenir)</li>
                    <li>Lokasyon bilgisi (teslimat adresi ve yakın restoran önerileri için)</li>
                    <li>Kullanım verileri (sipariş geçmişi, tercih edilen restoranlar, uygulama kullanım alışkanlıkları)</li>
                </ul>
            </section>


            <section id="kullanim-amaclari">
                <h2>2. Verilerin Kullanım Amaçları</h2>
                <p>Toplanan kişisel verileriniz şu amaçlarla kullanılmaktadır:</p>
                <ul>
                    <li>Siparişlerin alınması, hazırlanması ve teslim edilmesi</li>
                    <li>Kullanıcı hesabının yönetilmesi ve müşteri desteği sağlanması</li>
                    <li>Promosyon, kampanya ve bilgilendirme gönderimleri</li>
                    <li>Platformun geliştirilmesi ve kullanıcı deneyiminin iyileştirilmesi</li>
                    <li>Yasal yükümlülüklerin yerine getirilmesi</li>
                </ul>
            </section>


            <section id="veri-paylasimi">
                <h2>3. Verilerin Paylaşımı</h2>
                <p>Kişisel verileriniz aşağıdaki durumlarda paylaşılabilir:</p>
                <ul>
                    <li>Siparişlerin teslimi için yalnızca iş birliği yapılan restoranlar ve kuryelerle paylaşım.</li>
                    <li>Ödeme işlemleri için üçüncü taraf güvenilir ödeme kuruluşlarıyla sınırlı paylaşım.</li>
                    <li>Yasal zorunluluklar kapsamında resmi merciler ile paylaşım.</li>
                    <li>Kullanıcı onayı olmadan üçüncü kişilerle ticari amaçlarla paylaşılmaz.</li>
                </ul>
            </section>


            <section id="veri-guvenligi">
                <h2>4. Veri Güvenliği</h2>
                <p>Kullanıcı verileri, güvenli sunucularda saklanmakta ve yetkisiz erişime karşı korunmaktadır.</p>
                <p>Ödeme bilgileriniz SSL şifreleme teknolojisi ile korunur ve Gpsyemek tarafından kayıt altında tutulmaz.</p>
            </section>


            <section id="cookie">
                <h2>5. Çerezler (Cookies)</h2>
                <p>Platformumuzda kullanıcı deneyimini geliştirmek için çerezler kullanılmaktadır. Çerez ayarlarınızı dilediğiniz zaman tarayıcı üzerinden değiştirebilirsiniz.</p>
            </section>


            <section id="kullanici-haklari">
                <h2>6. Kullanıcı Hakları</h2>
                <p>KVKK kapsamında kullanıcılar aşağıdaki haklara sahiptir:</p>
                <ul>
                    <li>Kişisel verilerine erişim, düzeltme ve silme</li>
                    <li>İşleme faaliyetlerinin durdurulmasını talep etme</li>
                    <li>Açık rızasını geri çekme</li>
                </ul>
                <p>Bu hakların kullanımı için <a href="mailto:destek@gpsyemek.com">destek@gpsyemek.com</a> adresinden bizimle iletişime geçebilirsiniz.</p>
            </section>


            <section id="guncellemeler">
                <h2>7. Güncellemeler</h2>
                <p>Gizlilik Politikamız zaman zaman güncellenebilir. Güncellemeler web sitemiz üzerinden yayınlandığı andan itibaren geçerlidir.</p>
            </section>


            <section id="iletisim">
                <h2>📌 İletişim Bilgileri</h2>
                <p class="muted">Web: <a href="https://gpsyemek.com" target="_blank" rel="noopener">gpsyemek.com</a></p>
                <p class="muted">Mail: <a href="mailto:destek@gpsyemek.com">destek@gpsyemek.com</a></p>
                <p class="muted">Telefon: <a href="tel:+908503030477">0850 303 04 77</a></p>
            </section>


            <footer class="muted" aria-hidden="false">
                <p>Hazırlayan: Gpsyemek • Son güncelleme: <time datetime="2025-12-08">8 Aralık 2025</time></p>
            </footer>


        </div>
    </div>
@endsection
