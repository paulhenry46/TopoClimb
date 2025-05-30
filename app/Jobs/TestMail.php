<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class TestMail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public User $user)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
            $user = $this->user;
            Mail::raw('This is a test email to verify your mail configuration.', function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Test Email - Mail Configuration');
            });
            
    }
}
