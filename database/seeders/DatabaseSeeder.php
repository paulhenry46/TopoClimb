<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Support\Str;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

      

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
    }
}
