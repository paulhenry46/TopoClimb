<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Line;
use App\Models\Route;
use App\Models\Sector;
use App\Models\Site;
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


        // System site, sectors, areas, lines

        $site = new Site;
        $site->name = 'System';
        $site->address = 'Localhost';
        $site->slug = 'system';
        $site->save();

        $area_trad = new Area;
        $area_trad->name = 'Trad';
        $area_trad->slug = 'trad';
        $area_trad->type = 'trad';
        $area_trad->site_id = 1;
        $area_trad->save();

        $area_bouldering = new Area;
        $area_bouldering->name = 'System Bouldering';
        $area_bouldering->slug = 'bouldering';
        $area_bouldering->type = 'bouldering';
        $area_bouldering->site_id = 1;
        $area_bouldering->save();

        $sector_trad = new Sector;
        $sector_trad->name = 'System sector';
        $sector_trad->slug = 'sector';
        $sector_trad->area_id = 1;
        $sector_trad->local_id = 1;
        $sector_trad->save();

        $sector_bould = new Sector;
        $sector_bould->name = 'System sector';
        $sector_bould->slug = 'sector';
        $sector_bould->area_id = 2;
        $sector_bould->local_id = 1;
        $sector_bould->save();

        $line_trad = new Line();
        $line_trad->sector_id = 1;
        $line_trad->local_id = 1;
        $line_trad->save();

        $line_bould = new Line;
        $line_bould->sector_id = 2;
        $line_bould->local_id = 1;
        $line_bould->save();

        $route_t = new Route;
        $route_t->name = 'System 1';
        $route_t->slug = 'system-1';
        $route_t->local_id = 1;
        $route_t->grade  = 0;
        $route_t->color = 'red';
        $route_t->line_id = 1;
        $route_t->save();

        $route_t = new Route;
        $route_t->name = 'System 2';
        $route_t->slug = 'system-2';
        $route_t->local_id = 1;
        $route_t->grade  = 0;
        $route_t->color = 'red';
        $route_t->line_id = 2;
        $route_t->save();


        
    }
}
