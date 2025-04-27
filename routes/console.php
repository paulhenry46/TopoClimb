<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;

Artisan::command('grant_owner', function () {
   $users = User::all();
   foreach($users as $user){
    $user->assignRole('owner');
   }
})->purpose('Temporary DEV command to grant all access to everybody');