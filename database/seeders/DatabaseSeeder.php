<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Str;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // TAGS
        $default_tags = [
            __('Technic'), 
            __('Devers'),
            __('Dalle'),
            __('Archées'),
            __('Jetés'),
            __('Astuce'),
            __('Bacs')

        ];

        foreach ($default_tags as $tag) {
            $tag1 = new Tag;
            $tag1->name = $tag;
            $tag1->slug = Str::slug($tag);
            $tag1->save();
        }

        // Roles and permissions

       
        Role::create(['name' => 'super-admin'])->givePermissionTo([
            Permission::create(['name' => 'sites']), 
            Permission::create(['name' => 'settings']), 
            Permission::create(['name' => 'users'])]);

        // First admin user

        $user = new User();
        $user->name = 'Admin';
        $user->email = 'admin@system.localhost';
        $user->password = Hash::make('d4d5ehdp785pd81');
        $user->email_verified_at = now();
        $user->save();
        $user->syncRoles(['super-admin']);
        
    }
}
