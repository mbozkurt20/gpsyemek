# XYemek Restaurant API

**Base URL:**

```
https://xyemek.com/api/v1
```

## Endpoints

### 1. Get Orders

**Endpoint:**

```
POST /webhook/orders
```

**Headers:**

```
Authorization: Bearer <restaurant_api_token>
Content-Type: application/json
```

**Body:**

```json
{
  "event": "get_orders"
}
```

**Response:**

```json
{
  "success": true,
  "orders": [
    {
      "id": 123,
      "status": 5,
      "total": 240,
      "user": {
        "name": "Ahmet",
        "mobile": "05551234567"
      },
      "items": [
        {
          "menu_item_id": 1,
          "name": "Pizza",
          "quantity": 2,
          "price": 120
        }
      ]
    }
  ]
}
```

**Notes:**

* Status codes:

```
5 = PENDING
10 = CANCEL
12 = REJECT
14 = ACCEPT
15 = PROCESS
16 = ASSIGNED
17 = ON_THE_WAY
20 = COMPLETED
```

* Dönen siparişler sadece token ile eşleşen restoranın siparişleridir.

---

### 2. Update Order Status

**Endpoint:**

```
POST /webhook/orders
```

**Headers:**

```
Authorization: Bearer <restaurant_api_token>
Content-Type: application/json
```

**Body:**

```json
{
  "event": "order_updated",
  "order": {
    "id": 123,
    "status": 14
  }
}
```

**Response:**

```json
{
  "success": true
}
```

**Notes:**

* `id` → Güncellenecek siparişin ID’si.
* `status` → Yeni durum, yukarıdaki status kodlarını kullanın.
* Token geçerli değilse `401 Unauthorized` döner.

---

### Summary

* Tek webhook endpoint üzerinden hem sipariş çekme hem de sipariş durumu güncelleme yapılır.
* `Authorization` header’ı Bearer token olmalı ve restoranın kendi `api_token`’ı ile eşleşmeli.
