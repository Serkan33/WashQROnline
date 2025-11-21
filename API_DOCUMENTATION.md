# WashQR Online - Kullanıcı ve Hizmet Talebi API'si

Bu API, kullanıcı kaydı oluşturmak ve otomatik olarak bir online hizmet talebi başlatmak için geliştirilmiştir.

## API Endpoint'leri

### 1. Kullanıcı Oluştur ve Hizmet Talebi Başlat

**POST** `/api/v1/user-service`

Yeni bir kullanıcı oluşturur ve otomatik olarak bir online hizmet talebi başlatır.

#### Request Parametreleri

| Parametre | Tip | Zorunlu | Açıklama |
|-----------|-----|---------|----------|
| email | string | Evet | Kullanıcının e-posta adresi (benzersiz olmalı) |
| password | string | Evet | Kullanıcının şifresi (min. 6 karakter) |
| phone | string | Evet | Kullanıcının telefon numarası |
| name | string | Hayır | Kullanıcının adı (varsayılan: "Müşteri") |
| service_description | string | Hayır | Hizmet talebi açıklaması |

#### Örnek Request

```json
{
    "email": "musteri@example.com",
    "password": "123456",
    "phone": "05551234567",
    "name": "Ahmet Yılmaz",
    "service_description": "Acil yıkama hizmeti gerekiyor"
}
```

#### Başarılı Yanıt (201)

```json
{
    "success": true,
    "message": "Kullanıcı ve hizmet talebi başarıyla oluşturuldu",
    "data": {
        "user": {
            "id": 1,
            "name": "Ahmet Yılmaz",
            "email": "musteri@example.com",
            "phone": "05551234567",
            "created_at": "19.11.2025 20:45"
        },
        "service_request": {
            "id": 1,
            "service_type": "online_service",
            "status": "pending",
            "description": "Acil yıkama hizmeti gerekiyor",
            "created_at": "19.11.2025 20:45"
        }
    }
}
```

#### Hata Yanıtları

**422 - Doğrulama Hatası**
```json
{
    "success": false,
    "message": "Doğrulama hatası",
    "errors": {
        "email": ["Bu e-posta adresi zaten kullanımda."],
        "password": ["Şifre en az 6 karakter olmalıdır."]
    }
}
```

**500 - Sunucu Hatası**
```json
{
    "success": false,
    "message": "Kullanıcı oluşturulurken bir hata oluştu",
    "error": "Hata detayı"
}
```

### 2. Kullanıcının Hizmet Taleplerini Listele

**GET** `/api/v1/user/{userId}/service-requests`

Belirtilen kullanıcının tüm hizmet taleplerini listeler.

#### URL Parametreleri

| Parametre | Tip | Açıklama |
|-----------|-----|----------|
| userId | integer | Kullanıcının ID'si |

#### Başarılı Yanıt (200)

```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "Ahmet Yılmaz",
            "email": "musteri@example.com",
            "phone": "05551234567"
        },
        "service_requests": [
            {
                "id": 1,
                "service_type": "online_service",
                "status": "pending",
                "description": "Acil yıkama hizmeti gerekiyor",
                "created_at": "19.11.2025 20:45"
            }
        ]
    }
}
```

#### Hata Yanıtı

**404 - Kullanıcı Bulunamadı**
```json
{
    "success": false,
    "message": "Kullanıcı bulunamadı",
    "error": "No query results for model [App\\Models\\User] 999"
}
```

## cURL Örnekleri

### Kullanıcı oluşturma:
```bash
curl -X POST http://localhost:8000/api/v1/user-service \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "123456",
    "phone": "05551234567",
    "name": "Test Kullanıcı",
    "service_description": "Online yıkama hizmeti talebi"
  }'
```

### Hizmet taleplerini listeleme:
```bash
curl -X GET http://localhost:8000/api/v1/user/1/service-requests \
  -H "Accept: application/json"
```

## Veritabanı Yapısı

### users tablosu
- id (Primary Key)
- name (string)
- email (string, unique)
- phone (string)
- password (hashed)
- email_verified_at (timestamp, nullable)
- remember_token (string, nullable)
- created_at, updated_at (timestamps)

### service_requests tablosu
- id (Primary Key)
- user_id (Foreign Key -> users.id)
- service_type (string, default: 'online_service')
- status (string, default: 'pending')
- description (text, nullable)
- additional_data (JSON, nullable)
- created_at, updated_at (timestamps)

## Durum Kodları

- `pending`: Beklemede
- `processing`: İşleniyor
- `completed`: Tamamlandı
- `cancelled`: İptal edildi

## Notlar

1. Tüm API yanıtları JSON formatındadır
2. Başarılı işlemler için HTTP 201 (Created) veya 200 (OK) kodu döner
3. Hatalar için uygun HTTP hata kodları (422, 404, 500) döner
4. Veritabanı işlemleri transaction içinde yapılır
5. Şifreler bcrypt ile hashlenir
6. Kullanıcı oluşturulduğunda otomatik olarak bir hizmet talebi de oluşturulur
