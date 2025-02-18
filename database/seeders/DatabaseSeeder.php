<?php

namespace Database\Seeders;

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
        \App\Models\User::factory(1)->create();

        \App\Models\User::factory()->create([
            'first_name' => 'Test',
            'last_name'=>'User',
            'email' => 'test@example.com',
            'phone'=>'01234567899',
            'password'=>bcrypt('123456789'),

        ]);
        /*
         // تحقق مما إذا كان الدور موجودًا قبل إنشائه
    //if (!Role::where('name', 'admin')->exists()) {
        //Role::create(['name' => 'admin']);
    // تحقق مما إذا كان الدور موجودًا قبل إنشائه
    //if (!Role::where('name', 'admin')->exists()) {
        //Role::create(['name' => 'admin']);
    //}
    // إنشاء الأدوار
    //$adminRole = Role::create(['name' => 'admin']);
    //$userRole = Role::create(['name' => 'user']);

    // إنشاء مستخدم إداري وإعطاؤه دور Admin
    $admin = User::create([
        'first_name' => 'Admin',
        'last_name' => 'User',
        'email' => 'admin@example.com',
        'phone' => '123456789',
        'password' => bcrypt('123456789'),
    ]);
    $admin->assignRole($adminRole);
    */


    }

}
