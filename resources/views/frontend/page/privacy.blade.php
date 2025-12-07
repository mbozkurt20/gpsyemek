@extends('frontend.layouts.app')

@section('main-content')
    <div class="container py-5">

        <header class="mb-4">
            <h1 class="h3 mb-1">Gizlilik Politikası</h1>
            <p class="text-muted mb-0">Gpsyemek — gpsyemek.com ve mobil uygulama</p>
        </header>

        <div class="row">
            <div class="col-md-4 mb-4">

                <!-- İçindekiler -->
                <div class="card">
                    <div class="card-header fw-semibold">
                        İçindekiler
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><a href="#toplanan-bilgiler">1. Toplanan Bilgiler</a></li>
                        <li class="list-group-item"><a href="#kullanim-amaclari">2. Verilerin Kullanım Amaçları</a></li>
                        <li class="list-group-item"><a href="#veri-paylasimi">3. Verilerin Paylaşımı</a></li>
                        <li class="list-group-item"><a href="#veri-guvenligi">4. Veri Güvenliği</a></li>
                        <li class="list-group-item"><a href="#cookie">5. Çerezler (Cookies)</a></li>
                        <li class="list-group-item"><a href="#kullanici-haklari">6. Kullanıcı Hakları</a></li>
                        <li class="list-group-item"><a href="#guncellemeler">7. Güncellemeler</a></li>
                        <li class="list-group-item"><a href="#iletisim">İletişim Bilgileri</a></li>
                    </ul>
                </div>

            </div>

            <div class="col-md-8">

                <div class="card p-4">

                    <!-- 1. Toplanan Bilgiler -->
                    <section id="toplanan-bilgiler" class="mb-4">
                        <h2 class="h5">1. Toplanan Bilgiler</h2>
                        <p>Platformumuzu kullandığınızda aşağıdaki bilgiler toplanabilir:</p>
                        <ul>
                            <li>Ad, soyad, iletişim bilgileri (telefon, e-posta, adres)</li>
                            <li>Ödeme bilgileri (kredi/banka kartı — güvenli ödeme sağlayıcıları aracılığıyla)</li>
                            <li>Lokasyon bilgisi (teslimat adresi ve yakın restoran önerileri için)</li>
                            <li>Kullanım verileri (sipariş geçmişi, tercih edilen restoranlar, kullanım alışkanlıkları)</li>
                        </ul>
                    </section>

                    <!-- 2. Verilerin Kullanım Amaçları -->
                    <section id="kullanim-amaclari" class="mb-4">
                        <h2 class="h5">2. Verilerin Kullanım Amaçları</h2>
                        <p>Toplanan kişisel veriler şu amaçlarla kullanılmaktadır:</p>
                        <ul>
                            <li>Siparişlerin alınması, hazırlanması ve teslim edilmesi</li>
                            <li>Kullanıcı hesabı yönetimi ve müşteri desteği</li>
                            <li>Promosyon ve kampanya bilgilendirmeleri</li>
                            <li>Platform geliştirme ve kullanıcı deneyimi iyileştirme</li>
                            <li>Yasal yükümlülüklerin yerine getirilmesi</li>
                        </ul>
                    </section>

                    <!-- 3. Verilerin Paylaşımı -->
                    <section id="veri-paylasimi" class="mb-4">
                        <h2 class="h5">3. Verilerin Paylaşımı</h2>
                        <p>Kişisel veriler aşağıdaki durumlarda paylaşılabilir:</p>
                        <ul>
                            <li>İş birliği yapılan restoranlar ve kuryeler</li>
                            <li>Güvenilir üçüncü taraf ödeme sağlayıcıları</li>
                            <li>Yasal zorunluluklar kapsamında resmi merciler</li>
                            <li>Kullanıcı onayı olmadan ticari amaçlı paylaşım yapılmaz</li>
                        </ul>
                    </section>

                    <!-- 4. Veri Güvenliği -->
                    <section id="veri-guvenligi" class="mb-4">
                        <h2 class="h5">4. Veri Güvenliği</h2>
                        <p>Kullanıcı verileri güvenli sunucularda saklanır ve yetkisiz erişime karşı korunur.</p>
                        <p>Ödeme bilgileriniz SSL ile şifrelenir ve tarafımızca kayıt altına alınmaz.</p>
                    </section>

                    <!-- 5. Çerezler -->
                    <section id="cookie" class="mb-4">
                        <h2 class="h5">5. Çerezler (Cookies)</h2>
                        <p>Kullanıcı deneyimini geliştirmek amacıyla çerezler kullanılmaktadır. Tarayıcınızdan çerez ayarlarını değiştirebilirsiniz.</p>
                    </section>

                    <!-- 6. Kullanıcı Hakları -->
                    <section id="kullanici-haklari" class="mb-4">
                        <h2 class="h5">6. Kullanıcı Hakları</h2>
                        <p>KVKK kapsamında kullanıcıların şu hakları bulunmaktadır:</p>
                        <ul>
                            <li>Kişisel verilere erişim, düzeltme ve silme</li>
                            <li>İşleme faaliyetlerinin durdurulmasını talep etme</li>
                            <li>Açık rızayı geri çekme</li>
                        </ul>
                        <p>Dilekçe ve talepler için: <a href="mailto:destek@gpsyemek.com">destek@gpsyemek.com</a></p>
                    </section>

                    <!-- 7. Güncellemeler -->
                    <section id="guncellemeler" class="mb-4">
                        <h2 class="h5">7. Güncellemeler</h2>
                        <p>Gizlilik Politikası zaman zaman güncellenebilir. Web sitemizde yayınlandığında yürürlüğe girer.</p>
                    </section>

                    <!-- İletişim -->
                    <section id="iletisim" class="mb-4">
                        <h2 class="h5">📌 İletişim Bilgileri</h2>
                        <p class="text-muted mb-1">Web: <a href="https://gpsyemek.com" target="_blank">gpsyemek.com</a></p>
                        <p class="text-muted mb-1">Mail: <a href="mailto:destek@gpsyemek.com">destek@gpsyemek.com</a></p>
                        <p class="text-muted">Telefon: <a href="tel:+908503030477">0850 303 04 77</a></p>
                    </section>

                    <!-- Footer -->
                    <footer class="text-muted mt-4">
                        <small>Hazırlayan: Gpsyemek • Son güncelleme: <time datetime="2025-12-08">8 Aralık 2025</time></small>
                    </footer>

                </div>
            </div>
        </div>

    </div>
@endsection
