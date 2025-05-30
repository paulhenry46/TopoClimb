<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Jobs\TestMail;

new class extends Component {
    public $MAIL_MAILER;
    public $MAIL_HOST;
    public $MAIL_PORT;
    public $MAIL_USERNAME;
    public $MAIL_PASSWORD;
    public $MAIL_ENCRYPTION;
    public $MAIL_FROM_ADDRESS;
    public $MAIL_FROM_NAME;

    public function mount(){
        $this->MAIL_MAILER = env('MAIL_MAILER');
        $this->MAIL_HOST = env('MAIL_HOST');
        $this->MAIL_PORT = env('MAIL_PORT');
        $this->MAIL_USERNAME = env('MAIL_USERNAME');
        $this->MAIL_PASSWORD = env('MAIL_PASSWORD');
        $this->MAIL_ENCRYPTION = env('MAIL_ENCRYPTION');
        $this->MAIL_FROM_ADDRESS = env('MAIL_FROM_ADDRESS');
    }
        

     public function updateEnv() {
        $envPath = base_path('.env');
        $env = file_get_contents($envPath);

        $env = preg_replace('/^MAIL_MAILER=.*/m', 'MAIL_MAILER=' . $this->MAIL_MAILER, $env);
        $env = preg_replace('/^MAIL_HOST=.*/m', 'MAIL_HOST=' . $this->MAIL_HOST, $env);
        $env = preg_replace('/^MAIL_PORT=.*/m', 'MAIL_PORT=' . $this->MAIL_PORT, $env);
        $env = preg_replace('/^MAIL_USERNAME=.*/m', 'MAIL_USERNAME=' . $this->MAIL_USERNAME, $env);
        $env = preg_replace('/^MAIL_PASSWORD=.*/m', 'MAIL_PASSWORD=' . $this->MAIL_PASSWORD, $env);
        $env = preg_replace('/^MAIL_ENCRYPTION=.*/m', 'MAIL_ENCRYPTION=' . $this->MAIL_ENCRYPTION, $env);
        $env = preg_replace('/^MAIL_FROM_ADDRESS=.*/m', 'MAIL_FROM_ADDRESS=' . $this->MAIL_FROM_ADDRESS, $env);

        file_put_contents($envPath, $env);

        Artisan::call('config:clear');
        }

     public function sendTestMail() {
        TestMail::dispatchSync(Auth::user());
        $this->dispatch('action_ok', title: 'Test Mail sent', message: 'A test email has been sent to your address!');
    }

}; ?>

<div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Mails Settings')}}</h1>
            <p class="mt-2 text-sm text-gray-700">{{__('Mails settings to send mails')}}</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <x-button type='button' wire:click='updateEnv()' >{{ __('Save') }}</x-button> <x-button type='button' wire:click='updateEnv()' >{{ __('Test') }}</x-button>
        </div>
    </div>
    <div class="px-4 sm:px-6 lg:px-8 py-8 grid grid-cols-3 gap-2">
        <div>
            <label class="block ">Mailer</label>
            <input type="text" wire:model.live="MAIL_MAILER" class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6" />
        </div>
        <div>
            <label class="block ">Host</label>
            <input type="text" wire:model.live="MAIL_HOST" class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6" />
        </div>
        <div>
            <label class="block ">Port</label>
            <input type="text" wire:model.live="MAIL_PORT" class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6" />
        </div>
        <div>
            <label class="block ">Username</label>
            <input type="text" wire:model.live="MAIL_USERNAME" class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6" />
        </div>
        <div>
            <label class="block ">Password</label>
            <input type="password" wire:model.live="MAIL_PASSWORD" class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6" />
        </div>
        <div>
            <label class="block ">Encryption</label>
            <input type="text" wire:model.live="MAIL_ENCRYPTION" class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6" />
        </div>
        <div>
            <label class="block ">From Address</label>
            <input type="email" wire:model.live="MAIL_FROM_ADDRESS" class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6" />
        </div>
</div>
</div>