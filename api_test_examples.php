<?php
/**
 * API Test Örnekleri
 *
 * Bu dosya, oluşturulan API endpoint'lerini test etmek için örnek istekleri içerir.
 */

// Kullanıcı oluşturma ve hizmet talebi başlatma API'si için örnek cURL komutları:

/*
POST /api/v1/user-service
Content-Type: application/json

Örnek Request Body:
{
    "email": "test@example.com",
    "password": "123456",
    "phone": "05551234567",
    "name": "Test Kullanıcı",
    "service_description": "Online yıkama hizmeti talebi"
}

cURL Komutu:
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

Başarılı Yanıt (201):
{
    "success": true,
    "message": "Kullanıcı ve hizmet talebi başarıyla oluşturuldu",
    "data": {
        "user": {
            "id": 1,
            "name": "Test Kullanıcı",
            "email": "test@example.com",
            "phone": "05551234567",
            "created_at": "19.11.2025 20:45"
        },
        "service_request": {
            "id": 1,
            "service_type": "online_service",
            "status": "pending",
            "description": "Online yıkama hizmeti talebi",
            "created_at": "19.11.2025 20:45"
        }
    }
}

Hata Yanıtı (422 - Doğrulama Hatası):
{
    "success": false,
    "message": "Doğrulama hatası",
    "errors": {
        "email": ["Bu e-posta adresi zaten kullanımda."],
        "password": ["Şifre en az 6 karakter olmalıdır."]
    }
}
*/

/*
GET /api/v1/user/{userId}/service-requests

Kullanıcının hizmet taleplerini listeleme:

cURL Komutu:
curl -X GET http://localhost:8000/api/v1/user/1/service-requests \
  -H "Accept: application/json"

Başarılı Yanıt (200):
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "Test Kullanıcı",
            "email": "test@example.com",
            "phone": "05551234567"
        },
        "service_requests": [
            {
                "id": 1,
                "service_type": "online_service",
                "status": "pending",
                "description": "Online yıkama hizmeti talebi",
                "created_at": "19.11.2025 20:45"
            }
        ]
    }
}
*/

// Postman Collection için JSON:
/*
{
    "info": {
        "name": "WashQR Online API",
        "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    },
    "item": [
        {
            "name": "Create User with Service Request",
            "request": {
                "method": "POST",
                "header": [
                    {
                        "key": "Content-Type",
                        "value": "application/json"
                    },
                    {
                        "key": "Accept",
                        "value": "application/json"
                    }
                ],
                "body": {
                    "mode": "raw",
                    "raw": "{\n    \"email\": \"test@example.com\",\n    \"password\": \"123456\",\n    \"phone\": \"05551234567\",\n    \"name\": \"Test Kullanıcı\",\n    \"service_description\": \"Online yıkama hizmeti talebi\"\n}"
                },
                "url": {
                    "raw": "http://localhost:8000/api/v1/user-service",
                    "protocol": "http",
                    "host": ["localhost"],
                    "port": "8000",
                    "path": ["api", "v1", "user-service"]
                }
            }
        },
        {
            "name": "Get User Service Requests",
            "request": {
                "method": "GET",
                "header": [
                    {
                        "key": "Accept",
                        "value": "application/json"
                    }
                ],
                "url": {
                    "raw": "http://localhost:8000/api/v1/user/1/service-requests",
                    "protocol": "http",
                    "host": ["localhost"],
                    "port": "8000",
                    "path": ["api", "v1", "user", "1", "service-requests"]
                }
            }
        }
    ]
}
*/
