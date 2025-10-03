<?php

use App\Jobs\GenerateQrCodeOfUser;
use App\Models\Contest;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

test('user can generate qr code', function () {
    Storage::fake('local');
    
    $user = User::factory()->create();
    
    // Generate QR code
    GenerateQrCodeOfUser::dispatchSync($user);
    
    // Check that QR code file exists
    $qrPath = 'qrcode/user-' . $user->id . '/qrcode.svg';
    expect(Storage::exists($qrPath))->toBeTrue();
});

test('user qr code route returns correct data', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
    
    $response = $this->get(route('user.qr', ['user' => $user->id]));
    
    $response->assertOk();
    $response->assertJson([
        'id' => $user->id,
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
});

test('contest registration can keep route when checkbox is checked', function () {
    $site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);

    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test Description',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'official',
        'site_id' => $site->id,
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
    
    $contest->routes()->attach($route->id);
    
    $user = User::factory()->create();
    $staff = User::factory()->create();
    $contest->staffMembers()->attach($staff->id);
    
    $this->actingAs($staff);
    
    // Test with keep_route enabled
    $component = Livewire\Livewire::test('contests.registrations', ['contest' => $contest])
        ->set('user_id', $user->id)
        ->set('route_id', $route->id)
        ->set('keep_route', true)
        ->call('registerClimb');
    
    // Route should still be set after registration
    $component->assertSet('route_id', $route->id);
    
    // Test with keep_route disabled
    $component = Livewire\Livewire::test('contests.registrations', ['contest' => $contest])
        ->set('user_id', $user->id)
        ->set('route_id', $route->id)
        ->set('keep_route', false)
        ->call('registerClimb');
    
    // Route should be reset after registration
    $component->assertSet('route_id', '');
});

test('qr code section shows generate button when no qr code exists', function () {
    Storage::fake('local');
    
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $component = Livewire\Livewire::test('profile.qr-code-section');
    
    $component->assertSet('qrCodeExists', false);
});

test('qr code section can generate qr code', function () {
    Storage::fake('local');
    
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $component = Livewire\Livewire::test('profile.qr-code-section')
        ->call('generateQrCode');
    
    $component->assertSet('qrCodeExists', true);
    
    $qrPath = 'qrcode/user-' . $user->id . '/qrcode.svg';
    expect(Storage::exists($qrPath))->toBeTrue();
});
