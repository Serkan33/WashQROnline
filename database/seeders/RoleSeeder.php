<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin rolü (Shield tarafından otomatik oluşturulur)
        // Tüm yetkilere sahiptir
        
        // Firma Admin rolünü oluştur
        $firmaAdmin = Role::firstOrCreate([
            'name' => 'firma_admin',
            'guard_name' => 'web'
        ]);

        // Firma admin için gerekli izinleri tanımla
        // Bu izinler, firma adminin kendi firması kapsamında yapabileceği işlemleri temsil eder
        $firmaAdminPermissions = [
            // Kullanıcı yönetimi
            'view_any_user',
            'view_user',
            'create_user',
            'update_user',
            'delete_user',
            
            // Dashboard erişimi
            'page_Dashboard',
        ];

        // İzinleri oluştur ve role ata
        foreach ($firmaAdminPermissions as $permission) {
            $perm = Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
            
            $firmaAdmin->givePermissionTo($perm);
        }

        $this->command->info('Roller başarıyla oluşturuldu!');
        $this->command->info('- super_admin: Tüm yetkilere sahip');
        $this->command->info('- firma_admin: Firma kapsamında yetkilere sahip');
    }
}
