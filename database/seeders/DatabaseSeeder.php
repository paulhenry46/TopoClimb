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

        $owner = Role::create(['name' => 'owner']);
        $admin = Role::create(['name' => 'admin']);
        $opener = Role::create(['name' => 'opener']);
        $user = Role::create(['name' => 'user']);

        $p_1 = Permission::create(['name' => 'edit routes']);
        $p_2 = Permission::create(['name' => 'edit lines and sectors']);
        $p_3 = Permission::create(['name' => 'edit areas']);
        $p_4 = Permission::create(['name' => 'edit sites']);
        $p_5 = Permission::create(['name' => 'edit settings']);
        $p_6 = Permission::create(['name' => 'edit users']);

        $owner->givePermissionTo([$p_1, $p_2, $p_3, $p_4, $p_5, $p_6]);
        $admin->givePermissionTo([$p_1, $p_2, $p_3, $p_4, $p_5]);
        $opener->givePermissionTo($p_1);

        // First admin user

        $user = new User();
        $user->name = 'Admin';
        $user->email = 'admin@system.localhost';
        $user->password = Hash::make('d4d5ehdp785pd81');
        $user->email_verified_at = now();
        $user->save();
        $user->syncRoles(['owner', 'admin', 'opener', 'user']);
        
    }
}
