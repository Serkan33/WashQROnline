# WashQR Online - Admin Panel Kurulum Rehberi

## 📋 Proje Özellikleri

Bu proje **Laravel 10** ve **Filament 3** kullanılarak geliştirilmiş modern bir admin panel uygulamasıdır.

### Kullanılan Teknolojiler
- **Laravel 10** - PHP Framework
- **Filament 3** - Admin Panel Framework
- **Filament Shield** - Rol ve Yetki Yönetimi
- **Spatie Laravel Permission** - Permission Management
- **Tailwind CSS** - Mobil uyumlu ve modern UI
- **MySQL** - Veritabanı

### Kullanıcı Rolleri

#### 1. Super Admin (super_admin)
- Sistemdeki tüm yetkilere sahiptir
- Kullanıcı, rol ve yetki yönetimi yapabilir
- Tüm modüllere erişim yetkisi vardır

#### 2. Firma Admin (firma_admin)
- Kendi firması kapsamında yetkilere sahiptir
- Kullanıcı yönetimi yapabilir (görüntüleme, oluşturma, güncelleme, silme)
- Dashboard'a erişim yetkisi vardır

## 🚀 Kurulum Adımları

### 1. Veritabanı Oluşturma
```sql
CREATE DATABASE washqr_online;
```

### 2. Environment Yapılandırması
`.env` dosyası zaten yapılandırılmıştır:
```env
APP_NAME="WashQR Online"
APP_URL=http://localhost:8000
DB_DATABASE=washqr_online
```

### 3. Migration ve Seed İşlemleri
```bash
# Migration'ları çalıştır
php artisan migrate

# Rolleri oluştur
php artisan db:seed --class=RoleSeeder
```

### 4. Varsayılan Super Admin Kullanıcı
Shield kurulumu sırasında otomatik oluşturuldu:
- **E-posta:** admin@robotartech.com
- **Şifre:** Kurulum sırasında belirlenen şifre (konsol çıktısında görülebilir)

### 5. Sunucuyu Başlatma
```bash
php artisan serve
```

Admin paneline erişim: http://localhost:8000/admin

## 📁 Önemli Dosyalar ve Konumları

### Modeller
- `app/Models/User.php` - Kullanıcı modeli (HasRoles trait ile)

### Resources
- `app/Filament/Resources/UserResource.php` - Kullanıcı yönetimi
- `app/Filament/Resources/UserResource/Pages/` - Kullanıcı sayfaları

### Policies
- `app/Policies/UserPolicy.php` - Kullanıcı yetki kontrolleri
- `app/Policies/RolePolicy.php` - Rol yetki kontrolleri

### Seeders
- `database/seeders/RoleSeeder.php` - Rol ve yetki tanımlamaları

### Konfigürasyon
- `config/filament-shield.php` - Shield yapılandırması
- `config/permission.php` - Spatie Permission yapılandırması
- `app/Providers/Filament/AdminPanelProvider.php` - Filament panel yapılandırması

## 🎨 Panel Özellikleri

### Tasarım
- **Renk Paleti:** Mavi primary renk teması
- **Font:** Inter font family
- **Responsive:** Tam mobil uyumlu
- **Sidebar:** Masaüstünde daraltılabilir

### Kullanıcı Yönetimi
- ✅ Kullanıcı listesi (arama, filtreleme, sıralama)
- ✅ Kullanıcı oluşturma (rol atama ile)
- ✅ Kullanıcı düzenleme
- ✅ Kullanıcı silme
- ✅ Rol bazlı erişim kontrolü
- ✅ Şifre hashleme (bcrypt)
- ✅ E-posta benzersizlik kontrolü

### Rol ve Yetki Yönetimi
- ✅ Shield entegrasyonu
- ✅ Role Resource (otomatik)
- ✅ Permission management
- ✅ Policy-based authorization

## 🔧 Komutlar

### Shield Komutları
```bash
# Tüm resource'lar için policy ve permission oluştur
php artisan shield:generate --all

# Belirli bir resource için oluştur
php artisan shield:generate --resource=User

# Super admin oluştur
php artisan shield:super-admin

# Doctor - Shield durumunu kontrol et
php artisan shield:doctor
```

### Filament Komutları
```bash
# Yeni resource oluştur
php artisan make:filament-resource ModelName --generate

# Yeni user oluştur
php artisan make:filament-user

# Cache temizle
php artisan filament:clear-cached-components
```

### Cache Temizleme
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

## 📝 Örnek Kullanım Senaryoları

### Yeni Firma Admin Kullanıcısı Oluşturma
1. Super admin olarak giriş yap
2. Kullanıcılar menüsüne git
3. "Yeni Kullanıcı" butonuna tıkla
4. Bilgileri doldur
5. Rol olarak "firma_admin" seç
6. Kaydet

### Rol Yetkilerini Düzenleme
1. Super admin olarak giriş yap
2. Shield menüsünden "Roller" seçeneğine git
3. Düzenlemek istediğin rolü seç
4. İstediğin yetkileri seç/kaldır
5. Kaydet

## 🔐 Güvenlik Notları

1. **Şifre Güvenliği:** Tüm şifreler bcrypt ile hashlenmiştir
2. **CSRF Koruması:** Laravel'in built-in CSRF koruması aktif
3. **Policy-based Authorization:** Her işlem policy üzerinden kontrol edilir
4. **Super Admin Bypass:** Super admin tüm policy kontrollerini bypass eder

## 🎯 Sonraki Adımlar

- [ ] Firma modeli ve yönetimi ekle
- [ ] Firma-kullanıcı ilişkilendirmesi yap
- [ ] Dashboard widgets ekle
- [ ] Raporlama modülü ekle
- [ ] QR kod yönetimi ekle
- [ ] Bildirim sistemi ekle
- [ ] İki faktörlü kimlik doğrulama

## 📚 Dokümantasyon Linkleri

- [Laravel 10 Documentation](https://laravel.com/docs/10.x)
- [Filament 3 Documentation](https://filamentphp.com/docs/3.x)
- [Filament Shield Documentation](https://github.com/bezhanSalleh/filament-shield)
- [Spatie Permission Documentation](https://spatie.be/docs/laravel-permission)

## 🤝 Destek

Herhangi bir sorun yaşarsanız:
1. `php artisan shield:doctor` komutunu çalıştırın
2. `storage/logs/laravel.log` dosyasını kontrol edin
3. Cache'leri temizleyin: `php artisan config:clear && php artisan cache:clear`

---

**Geliştirici Notu:** Bu proje Tailwind CSS ile mobil uyumlu olarak tasarlanmış, modern ve kullanıcı dostu bir admin panel sağlar.
