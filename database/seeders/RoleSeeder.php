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
        // Super Admin rolünü oluştur
        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web'
        ]);

        // Super Admin için tüm izinleri tanımla
        $superAdminPermissions = [
            // Kullanıcı yönetimi
            'view_any_user',
            'view_user',
            'create_user',
            'update_user',
            'delete_user',
            'restore_user',
            'force_delete_user',

            // Rol yönetimi
            'view_any_role',
            'view_role',
            'create_role',
            'update_role',
            'delete_role',

            // Shield yönetimleri
            'view_shield::role',
            'create_shield::role',
            'update_shield::role',
            'delete_shield::role',

            // Dashboard ve sistem erişimi
            'page_Dashboard',
            'widget_StatsOverviewWidget',

            // Tüm sistem yönetimi
            'manage_system',
            'view_admin_panel',
        ];

        // Super Admin izinlerini oluştur ve ata
        foreach ($superAdminPermissions as $permission) {
            $perm = Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);

            $superAdmin->givePermissionTo($perm);
        }

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
