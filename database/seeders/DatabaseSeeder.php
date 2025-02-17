<?php

namespace Database\Seeders;
use Spatie\Permission\Models\Permission;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
{
    // التحقق من وجود الأدوار قبل إنشائها
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $userRole = Role::firstOrCreate(['name' => 'user']);

    // التحقق من وجود الصلاحيات قبل إضافتها
    $permissions = [
        'manage orders',
        'manage users',
        'manage products'
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }

    // ربط الصلاحيات بالأدوار
    $adminRole->syncPermissions($permissions);
    $userRole->syncPermissions(['manage orders']);

    // تعيين دور "admin" لأول مستخدم في قاعدة البيانات
    $user = User::first();
    if ($user && !$user->hasRole('admin')) {
        $user->assignRole('admin');
        echo "تم تعيين الدور Admin للمستخدم: " . $user->first_name . " " . $user->last_name . "\n";
    }
}
}
