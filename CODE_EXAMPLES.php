<?php

/**
 * WashQR Online - Kod Örnekleri ve Açıklamalar
 * 
 * Bu dosya, projede kullanılan önemli kodların açıklamalarını içerir.
 */

// ============================================================================
// 1. USER MODELİ - HasRoles Trait Kullanımı
// ============================================================================

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    // HasRoles trait'i, kullanıcılara rol ve yetki yönetimi sağlar
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * Password otomatik hash'lenir
     * Laravel 10+ 'hashed' cast'i ile
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',  // Otomatik bcrypt
    ];
}

// Kullanım örnekleri:
// $user->assignRole('super_admin');           // Rol ata
// $user->hasRole('firma_admin');              // Rol kontrolü
// $user->givePermissionTo('view_user');       // Direkt yetki ver
// $user->can('view_user');                    // Yetki kontrolü

// ============================================================================
// 2. FILAMENT RESOURCE - Kullanıcı Yönetimi
// ============================================================================

class UserResource extends Resource
{
    /**
     * Form Şeması
     * - Section ile gruplandırma
     * - TextInput, Select gibi form bileşenleri
     * - Validasyon kuralları
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Kullanıcı Bilgileri')
                ->schema([
                    // Ad Soyad alanı
                    Forms\Components\TextInput::make('name')
                        ->label('Ad Soyad')
                        ->required()                    // Zorunlu
                        ->maxLength(255),              // Max karakter
                    
                    // E-posta alanı
                    Forms\Components\TextInput::make('email')
                        ->label('E-posta')
                        ->email()                      // E-posta formatı
                        ->required()
                        ->unique(ignoreRecord: true)   // Benzersiz (güncellemede mevcut kaydı göz ardı et)
                        ->maxLength(255),
                    
                    // Şifre alanı
                    Forms\Components\TextInput::make('password')
                        ->label('Şifre')
                        ->password()                   // Password input
                        ->required(fn (string $context): bool => $context === 'create') // Sadece oluştururken zorunlu
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))       // Hash'le
                        ->dehydrated(fn ($state) => filled($state))                     // Sadece dolu ise kaydet
                        ->revealable()                 // Göster/gizle butonu
                        ->maxLength(255),
                ])->columns(2),  // 2 sütunlu layout
            
            // Rol seçimi bölümü
            Forms\Components\Section::make('Rol ve Yetkiler')
                ->schema([
                    Forms\Components\Select::make('roles')
                        ->label('Rol')
                        ->relationship('roles', 'name')  // Relationship kullan
                        ->multiple()                      // Çoklu seçim
                        ->preload()                       // Seçenekleri önceden yükle
                        ->searchable()                    // Arama özelliği
                        ->required(),
                ]),
        ]);
    }

    /**
     * Tablo Şeması
     * - Kolonlar ve özellikleri
     * - Filtreler
     * - Aksiyonlar
     */
    public static function table(Table $table): Table
    {
        return $table->columns([
            // Ad Soyad kolonu
            Tables\Columns\TextColumn::make('name')
                ->label('Ad Soyad')
                ->searchable()  // Aranabilir
                ->sortable(),   // Sıralanabilir
            
            // E-posta kolonu
            Tables\Columns\TextColumn::make('email')
                ->label('E-posta')
                ->searchable()
                ->sortable()
                ->copyable()                          // Kopyalanabilir
                ->icon('heroicon-o-envelope'),       // İkon
            
            // Rol kolonu (relationship)
            Tables\Columns\TextColumn::make('roles.name')
                ->label('Rol')
                ->badge()                            // Badge görünümü
                ->colors([                           // Renge göre renklendirme
                    'danger' => 'super_admin',
                    'warning' => 'firma_admin',
                ]),
        ])
        ->filters([
            // Rol filtreleme
            Tables\Filters\SelectFilter::make('roles')
                ->relationship('roles', 'name')
                ->multiple()
                ->preload(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }
}

// ============================================================================
// 3. PANEL PROVIDER - Filament Panel Yapılandırması
// ============================================================================

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()                              // Varsayılan panel
            ->id('admin')                            // Panel ID
            ->path('admin')                          // URL path
            ->login()                                // Login sayfası aktif
            ->brandName('WashQR Online')            // Marka adı
            
            // Renk paleti
            ->colors([
                'primary' => Color::Blue,
                'danger' => Color::Red,
                'info' => Color::Sky,
                'success' => Color::Green,
                'warning' => Color::Amber,
            ])
            
            ->font('Inter')                          // Font family
            ->favicon(asset('favicon.ico'))          // Favicon
            
            // Resources, Pages, Widgets otomatik keşif
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            
            // Middleware'ler
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            
            // Auth middleware
            ->authMiddleware([
                Authenticate::class,
            ])
            
            // Plugin'ler - Shield eklendi
            ->plugins([
                FilamentShieldPlugin::make()
            ])
            
            // UI ayarları
            ->maxContentWidth('full')                // Full genişlik
            ->sidebarCollapsibleOnDesktop();         // Sidebar daraltılabilir
    }
}

// ============================================================================
// 4. ROLE SEEDER - Rol ve Yetki Oluşturma
// ============================================================================

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Firma Admin rolü oluştur
        $firmaAdmin = Role::firstOrCreate([
            'name' => 'firma_admin',
            'guard_name' => 'web'
        ]);

        // İzinleri tanımla
        $firmaAdminPermissions = [
            'view_any_user',    // Kullanıcı listesi
            'view_user',        // Kullanıcı detayı
            'create_user',      // Kullanıcı oluştur
            'update_user',      // Kullanıcı güncelle
            'delete_user',      // Kullanıcı sil
            'page_Dashboard',   // Dashboard erişimi
        ];

        // İzinleri oluştur ve role ata
        foreach ($firmaAdminPermissions as $permission) {
            $perm = Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
            
            $firmaAdmin->givePermissionTo($perm);
        }
    }
}

// ============================================================================
// 5. POLICY - Yetki Kontrolü
// ============================================================================

/**
 * UserPolicy - Shield tarafından otomatik oluşturuldu
 * 
 * Super admin her zaman bypass edilir
 * Diğer kullanıcılar için permission kontrolü yapılır
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        // Super admin bypass
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // İzin kontrolü
        return $user->can('view_any_user');
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        return $user->can('create_user');
    }

    public function update(User $user, User $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        return $user->can('update_user');
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        return $user->can('delete_user');
    }
}

// ============================================================================
// 6. ARTISAN KOMUTLARI
// ============================================================================

/*

# SHIELD KOMUTLARI
# ---------------

# Tüm resource'lar için policy ve permission oluştur
php artisan shield:generate --all

# Belirli resource için oluştur
php artisan shield:generate --resource=User

# Super admin kullanıcı oluştur
php artisan shield:super-admin

# Shield durumunu kontrol et
php artisan shield:doctor


# FILAMENT KOMUTLARI
# ------------------

# Yeni resource oluştur (CRUD otomatik)
php artisan make:filament-resource ModelName --generate

# Yeni kullanıcı oluştur (interaktif)
php artisan make:filament-user

# Yeni sayfa oluştur
php artisan make:filament-page PageName

# Yeni widget oluştur
php artisan make:filament-widget WidgetName


# VERİTABANI KOMUTLARI
# --------------------

# Migration'ları çalıştır
php artisan migrate

# Tüm tabloları sil ve yeniden oluştur
php artisan migrate:fresh

# Seeder çalıştır
php artisan db:seed
php artisan db:seed --class=RoleSeeder

# Migration + Seed
php artisan migrate:fresh --seed


# CACHE TEMİZLEME
# ---------------

# Tüm cache'leri temizle
php artisan optimize:clear

# Config cache
php artisan config:clear

# Route cache
php artisan route:clear

# View cache
php artisan view:clear

*/

// ============================================================================
// 7. BLADE KOMPONENTLERI (Filament)
// ============================================================================

/*

<!-- Form Components -->
<x-filament::input.wrapper>
    <x-filament::input type="text" wire:model="name" />
</x-filament::input.wrapper>

<x-filament::button>
    Kaydet
</x-filament::button>

<x-filament::badge color="success">
    Aktif
</x-filament::badge>

<!-- Section -->
<x-filament::section>
    <x-slot name="heading">
        Kullanıcı Bilgileri
    </x-slot>
    
    İçerik buraya...
</x-filament::section>

*/

// ============================================================================
// 8. LİVEWIRE KULLANIMI
// ============================================================================

/*

// Livewire Component
class UserList extends Component
{
    // Public property - otomatik wire:model bağlanır
    public $search = '';
    
    // Computed property
    public function getUsersProperty()
    {
        return User::where('name', 'like', '%' . $this->search . '%')->get();
    }
    
    // Action
    public function deleteUser($userId)
    {
        User::find($userId)->delete();
        
        // Bildirim göster (Filament)
        Notification::make()
            ->title('Kullanıcı silindi')
            ->success()
            ->send();
    }
    
    public function render()
    {
        return view('livewire.user-list');
    }
}

*/

// ============================================================================
// 9. HELPER FONKSİYONLAR
// ============================================================================

// Kullanıcı kontrolü
if (auth()->check()) {
    $user = auth()->user();
}

// Rol kontrolü
if (auth()->user()->hasRole('super_admin')) {
    // Super admin işlemleri
}

// Yetki kontrolü
if (auth()->user()->can('create_user')) {
    // Kullanıcı oluşturabilir
}

// Çoklu yetki kontrolü
if (auth()->user()->hasAnyPermission(['view_user', 'create_user'])) {
    // En az bir yetkiye sahip
}

// Tüm yetkileri kontrol
if (auth()->user()->hasAllPermissions(['view_user', 'create_user', 'update_user'])) {
    // Tüm yetkilere sahip
}

// ============================================================================
// 10. .ENV AYARLARI (Önemli)
// ============================================================================

/*

# Uygulama
APP_NAME="WashQR Online"
APP_ENV=local                    # production'da: production
APP_DEBUG=true                   # production'da: false
APP_URL=http://localhost:8000

# Veritabanı
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=washqr_online
DB_USERNAME=root
DB_PASSWORD=

# Mail (production için)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@washqr.com"
MAIL_FROM_NAME="${APP_NAME}"

# Session
SESSION_DRIVER=file             # production'da: database veya redis
SESSION_LIFETIME=120

# Cache
CACHE_DRIVER=file               # production'da: redis

# Queue
QUEUE_CONNECTION=sync           # production'da: redis veya database

*/

// ============================================================================
// SONUÇ
// ============================================================================

/*

Bu kod örnekleri, WashQR Online projesindeki temel yapıları göstermektedir.

Önemli Noktalar:
1. Spatie Permission ile rol/yetki yönetimi
2. Filament 3 ile modern admin panel
3. Shield ile otomatik policy ve permission oluşturma
4. Livewire ile reactive components
5. Tailwind CSS ile responsive tasarım

Daha fazla bilgi için:
- SETUP.md
- QUICKSTART.md
- Laravel Documentation: https://laravel.com/docs/10.x
- Filament Documentation: https://filamentphp.com/docs/3.x

*/
