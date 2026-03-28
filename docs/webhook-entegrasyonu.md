# GPS Yemek – Webhook Entegrasyon Dokümantasyonu

## İçindekiler

1. [Genel Bakış](#1-genel-bakış)
2. [Webhook URL Ekleme (Admin Panel)](#2-webhook-url-ekleme-admin-panel)
3. [Kimlik Doğrulama](#3-kimlik-doğrulama)
4. [Gelen Webhooklar (GPS Yemek → Dış Sistem)](#4-gelen-webhooklar-gps-yemek--dış-sistem)
5. [Giden Webhooklar (Dış Sistem → GPS Yemek)](#5-giden-webhooklar-dış-sistem--gps-yemek)
6. [Sipariş Durum Akışı](#6-sipariş-durum-akışı)
7. [Hata Senaryoları](#7-hata-senaryoları)
8. [Test (Postman)](#8-test-postman)

---

## 1. Genel Bakış

GPS Yemek, restoran entegrasyonunu çift yönlü webhook sistemiyle yönetir:

| Yön | Açıklama |
|-----|----------|
| **GPS → Dış Sistem** | Yeni sipariş oluştuğunda veya sipariş durumu değiştiğinde GPS Yemek, restoranın kayıtlı webhook URL'lerine bildirim gönderir. |
| **Dış Sistem → GPS** | POS veya kasa sistemi, `POST /api/webhook/orders` endpoint'ine event göndererek siparişleri sorgulayabilir, durumları güncelleyebilir, restoran durumunu yönetebilir. |

---

## 2. Webhook URL Ekleme (Admin Panel)

### Adım 1 – Restoran Detay Sayfasına Git

`https://gpsyemek.com/admin/restaurants` adresine git ve ilgili restoranın detay sayfasını aç.

### Adım 2 – API Token'ı Görüntüle

Sayfada **API & Webhook** bölümünde **"Göster"** butonuna tıklayarak `api_token` değerini görüntüle.
Bu token, webhook isteklerinde `Authorization: Bearer <token>` olarak kullanılır.

### Adım 3 – Webhook URL Ekle

**Push Bildirimi (Webhook)** bölümünde:

- Hazır tanımlı URL'ler (POS Sistemi, Kurye Sistemi) checkbox ile açılıp kapatılabilir.
- Özel bir URL eklemek için **"+ Yeni Webhook URL Ekle"** butonuna tıkla.
- Açılan alana webhook URL'ini gir (ör. `https://senin-pos-sistemin.com/api/gpsyemek/inbound`).
- Bir satırı silmek için satırın sağındaki **×** butonuna tıkla.
- Birden fazla URL eklenebilir; hepsi aynı anda çalışır.

### Adım 4 – Kaydet

**Kaydet** butonuna tıkladığında sistem:

1. İşaretli her URL için `/restaurant-get-domain` endpoint'ini çağırarak **tenant domain** bilgisini otomatik çeker.
2. `{url, domain}` çiftini JSON formatında `restaurants.webhook_url` alanına kaydeder.

> **Not:** Domain bilgisi otomatik çözümlenir, elle girmen gerekmez.

---

## 3. Kimlik Doğrulama

Tüm isteklerde (hem gelen hem giden) **Bearer Token** kullanılır.

```
Authorization: Bearer <restaurant.api_token>
```

Token, admin panelde restoran kaydında `api_token` alanında görüntülenir.

---

## 4. Gelen Webhooklar (GPS Yemek → Dış Sistem)

GPS Yemek, aşağıdaki durumlarda kayıtlı webhook URL'lerine **otomatik POST** isteği atar.

### 4.1 Yeni Sipariş – `new_order`

Sipariş oluşturulduğunda tetiklenir.

**Payload:**

```json
{
  "event": "new_order",
  "order_code": "ORD-12345",
  "status": "PENDING",
  "total": 150.00,
  "sub_total": 135.00,
  "delivery_charge": 15.00,
  "payment_method": "Kapıda Nakit Ödeme",
  "address": "Örnek Mah. Test Sok. No:1",
  "mobile": "05XX XXX XX XX",
  "created_at": "2026-03-28T10:00:00.000000Z",
  "customer": {
    "id": 1,
    "name": "Ali Yılmaz",
    "email": "ali@example.com"
  },
  "restaurant_id": 5,
  "restaurant": {
    "id": 5,
    "name": "Test Restoran",
    "logo": "https://..."
  },
  "items": [
    {
      "id": 1,
      "name": "Adana Kebap",
      "quantity": 2,
      "price": 67.50
    }
  ],
  "domain": "tenant.bmd-pos.com"
}
```

### 4.2 Sipariş Durumu Değişti – `order_status_changed`

Sipariş durumu değiştiğinde **OrderObserver** tarafından tetiklenir.

**Payload:**

```json
{
  "event": "order_status_changed",
  "order_code": "ORD-12345",
  "status": "CONFIRMED",
  "domain": "tenant.bmd-pos.com"
}
```

**Status değerleri:**

| GPS Yemek Durumu | Gönderilen Değer |
|------------------|-----------------|
| Beklemede        | `PENDING`       |
| Onaylandı        | `CONFIRMED`     |
| Hazırlanıyor     | `PREPARED`      |
| Kurye Atandı     | `ASSIGNED`      |
| Yolda            | `HANDOVER`      |
| Teslim Edildi    | `DELIVERED`     |
| Reddedildi       | `UNSUPPLIED`    |
| İptal Edildi     | `CANCELLED`     |

---

## 5. Giden Webhooklar (Dış Sistem → GPS Yemek)

POS/kasa sistemi bu endpoint'i çağırarak GPS Yemek'i yönetir.

```
POST https://gpsyemek.com/api/webhook/orders
Authorization: Bearer <api_token>
Content-Type: application/json
```

Body'de her zaman `event` alanı bulunmalıdır.

---

### Event: `get_orders`

Bugünkü bekleyen (PENDING) siparişleri ve restoran durumunu getirir.

**Request:**
```json
{
  "event": "get_orders"
}
```

**Response (200):**
```json
{
  "success": true,
  "orders": [ /* sipariş listesi */ ],
  "restaurant_status": {
    "current_status": 0,
    "permanently_closed": false,
    "temporary_closed_until": null
  }
}
```

---

### Event: `order_updated`

Sipariş durumunu günceller.

**Request:**
```json
{
  "event": "order_updated",
  "order_code": "ORD-12345",
  "status": "PREPARED"
}
```

**Kabul edilen `status` değerleri:**

| Gönderilen Değer | GPS Yemek Karşılığı |
|-----------------|---------------------|
| `PREPARED`      | Onaylandı (ACCEPT)  |
| `HANDOVER`      | Yolda (ON_THE_WAY)  |
| `DELIVERED`     | Teslim Edildi (COMPLETED) |
| `REJECTED`      | Reddedildi (REJECT) |

**Response (200):**
```json
{
  "success": true
}
```

**Response (404) – Sipariş bulunamadı:**
```json
{
  "success": false,
  "error": "Order not found"
}
```

---

### Event: `restaurant_status_changed`

Restoranın açık/kapalı durumunu **toggle** eder.
`current_status` → 5 ise 0 yapar, 0 ise 5 yapar.

**Request:**
```json
{
  "event": "restaurant_status_changed"
}
```

**Response (200):**
```json
{
  "success": true,
  "restaurant": {
    "id": 5,
    "current_status": 5
  }
}
```

---

### Event: `restaurant_permanently_close`

Restoranı **kalıcı olarak kapatır** (`permanently_closed = true`).

**Request:**
```json
{
  "event": "restaurant_permanently_close"
}
```

**Response (200):**
```json
{
  "success": true,
  "permanently_closed": true
}
```

---

### Event: `restaurant_open`

Restoranı **açar**. `permanently_closed = false`, `temporary_closed_until = null` yapar.

**Request:**
```json
{
  "event": "restaurant_open"
}
```

**Response (200):**
```json
{
  "success": true,
  "permanently_closed": false
}
```

---

## 6. Sipariş Durum Akışı

```
Yeni Sipariş
     │
     ▼
  PENDING ──► REJECT / CANCEL
     │
     ▼ (POS: PREPARED)
  ACCEPT (Onaylandı)
     │
     ▼ (POS: HANDOVER)
  ON_THE_WAY (Yolda)
     │
     ▼ (POS: DELIVERED)
  COMPLETED (Teslim Edildi)
```

---

## 7. Hata Senaryoları

| HTTP Kodu | Durum | Açıklama |
|-----------|-------|----------|
| `401` | Unauthorized | Bearer token geçersiz veya eksik |
| `400` | Bad Request | Bilinmeyen `event` değeri |
| `404` | Not Found | `order_updated` veya restoran işlemlerinde kayıt bulunamadı |

**401 Response:**
```json
{
  "error": "Unauthorized"
}
```

**400 Response:**
```json
{
  "error": "Unknown event"
}
```

---

## 8. Test (Postman)

Proje kökünde `GPS_Yemek_Webhook.postman_collection.json` dosyası mevcuttur.

1. Postman'ı aç → **Import** → dosyayı seç
2. Koleksiyon değişkenlerini güncelle:
   - `base_url` → `https://gpsyemek.com`
   - `api_token` → Restoran `api_token` değeri
3. İstediğin event'i çalıştır
