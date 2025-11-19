<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin kullanıcı (zaten Shield tarafından oluşturuldu)
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@washqr.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole('super_admin');

        // Firma Admin kullanıcı
        $firmaAdmin = User::firstOrCreate(
            ['email' => 'firma@washqr.com'],
            [
                'name' => 'Firma Yöneticisi',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $firmaAdmin->assignRole('firma_admin');

        // Test Firma Admin kullanıcı
        $testFirmaAdmin = User::firstOrCreate(
            ['email' => 'test@washqr.com'],
            [
                'name' => 'Test Firma Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $testFirmaAdmin->assignRole('firma_admin');

        $this->command->info('Demo kullanıcılar oluşturuldu!');
        $this->command->info('');
        $this->command->info('=== Giriş Bilgileri ===');
        $this->command->info('');
        $this->command->info('Super Admin:');
        $this->command->info('E-posta: admin@washqr.com');
        $this->command->info('Şifre: password');
        $this->command->info('');
        $this->command->info('Firma Admin:');
        $this->command->info('E-posta: firma@washqr.com');
        $this->command->info('Şifre: password');
        $this->command->info('');
        $this->command->info('Test Firma Admin:');
        $this->command->info('E-posta: test@washqr.com');
        $this->command->info('Şifre: password');
    }
}
