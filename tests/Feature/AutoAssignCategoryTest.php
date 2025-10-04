<?php

use Tests\TestCase;
use App\Models\User;
use App\Models\Contest;
use App\Models\ContestCategory;
use App\Models\Site;
use App\Models\Route;
use App\Models\Area;
use App\Models\Sector;
use App\Models\Line;
use App\Models\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AutoAssignCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_is_auto_assigned_to_matching_category_on_log_creation()
    {
        // Create a user with age and gender
        $user = User::factory()->create([
            'birth_date' => now()->subYears(25),
            'gender' => 'male',
        ]);

        // Create a site
        $site = Site::create([
            'name' => 'Test Site',
            'slug' => 'test-site',
            'address' => 'Test Address',
        ]);
        
        $area = $site->areas()->create([
            'name' => 'Test Area',
            'slug' => 'test-area',
            'type' => 'bouldering',
        ]);
        
        $sector = $area->sectors()->create([
            'name' => 'Test Sector',
            'slug' => 'test-sector',
            'local_id' => 1,
        ]);
        
        $line = $sector->lines()->create([
            'local_id' => 1,
        ]);
        
        $route = $line->routes()->create([
            'name' => 'Test Route',
            'slug' => 'test-route',
            'local_id' => 1,
            'grade' => 500,
            'color' => 'blue',
        ]);

        // Create a contest
        $contest = Contest::create([
            'name' => 'Test Contest',
            'description' => 'Test Description',
            'site_id' => $site->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'mode' => 'free',
        ]);
        
        // Associate route with contest
        $contest->routes()->attach($route->id, ['points' => 100]);

        // Create an auto-assign category
        $category = ContestCategory::create([
            'contest_id' => $contest->id,
            'name' => 'Men 18-30',
            'type' => 'gender',
            'criteria' => 'male',
            'auto_assign' => true,
            'min_age' => 18,
            'max_age' => 30,
        ]);

        // User should not be in category yet
        $this->assertFalse($category->users->contains($user));

        // Create a log for the user on the contest route
        $log = Log::create([
            'user_id' => $user->id,
            'route_id' => $route->id,
            'type' => 'flash',
            'way' => 'lead',
            'grade' => $route->grade,
        ]);

        // Refresh the category
        $category->refresh();

        // User should now be in the category
        $this->assertTrue($category->users->contains($user));
    }

    public function test_user_is_not_assigned_to_non_matching_category()
    {
        // Create a user with age and gender
        $user = User::factory()->create([
            'birth_date' => now()->subYears(25),
            'gender' => 'male',
        ]);

        // Create a site
        $site = Site::create([
            'name' => 'Test Site',
            'slug' => 'test-site',
            'address' => 'Test Address',
        ]);
        
        $area = $site->areas()->create([
            'name' => 'Test Area',
            'slug' => 'test-area',
            'type' => 'bouldering',
        ]);
        
        $sector = $area->sectors()->create([
            'name' => 'Test Sector',
            'slug' => 'test-sector',
            'local_id' => 1,
        ]);
        
        $line = $sector->lines()->create([
            'local_id' => 1,
        ]);
        
        $route = $line->routes()->create([
            'name' => 'Test Route',
            'slug' => 'test-route',
            'local_id' => 1,
            'grade' => 500,
            'color' => 'blue',
        ]);

        // Create a contest
        $contest = Contest::create([
            'name' => 'Test Contest',
            'description' => 'Test Description',
            'site_id' => $site->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'mode' => 'free',
        ]);
        
        // Associate route with contest
        $contest->routes()->attach($route->id, ['points' => 100]);

        // Create a non-matching auto-assign category (for women)
        $category = ContestCategory::create([
            'contest_id' => $contest->id,
            'name' => 'Women 18-30',
            'type' => 'gender',
            'criteria' => 'female',
            'auto_assign' => true,
            'min_age' => 18,
            'max_age' => 30,
        ]);

        // Create a log for the user on the contest route
        $log = Log::create([
            'user_id' => $user->id,
            'route_id' => $route->id,
            'type' => 'flash',
            'way' => 'lead',
            'grade' => $route->grade,
        ]);

        // Refresh the category
        $category->refresh();

        // User should not be in the category
        $this->assertFalse($category->users->contains($user));
    }
}
