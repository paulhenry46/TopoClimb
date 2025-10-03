<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Jobs\GenerateQrCodeOfUser;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    public User $user;
    public $qrCodeExists = false;

    public function mount()
    {
        $this->user = auth()->user();
        $this->checkQrCode();
    }

    public function checkQrCode()
    {
        $qrPath = 'qrcode/user-' . $this->user->id . '/qrcode.svg';
        $this->qrCodeExists = Storage::exists($qrPath);
    }

    public function generateQrCode()
    {
        GenerateQrCodeOfUser::dispatchSync($this->user);
        $this->checkQrCode();
        $this->dispatch('action_ok', title: 'QR Code generated', message: 'Your QR code has been generated successfully!');
    }

    public function getQrCodeUrlProperty()
    {
        if ($this->qrCodeExists) {
            return Storage::url('qrcode/user-' . $this->user->id . '/qrcode.svg');
        }
        return null;
    }
}; ?>

<x-action-section>
    <x-slot name="title">
        {{ __('My QR Code') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Generate and display your QR code for quick identification by staff members during contests.') }}
    </x-slot>

    <x-slot name="content">
        <div class="max-w-xl text-sm text-gray-600">
            <p>
                {{ __('Staff members can scan your QR code to quickly identify you when registering your climbs in official contests, eliminating the need to search for your name.') }}
            </p>
        </div>

        @if($qrCodeExists)
            <div class="mt-5">
                <div class="flex items-center justify-center p-6 bg-white border border-gray-200 rounded-lg">
                    <div class="text-center">
                        <div class="mb-4">
                            <img src="{{ $this->qrCodeUrl }}" alt="User QR Code" class="w-64 h-64 mx-auto">
                        </div>
                        <p class="text-sm text-gray-500">
                            {{ __('Show this QR code to staff members for quick identification') }}
                        </p>
                    </div>
                </div>
                
                <div class="mt-4">
                    <x-secondary-button wire:click="generateQrCode">
                        {{ __('Regenerate QR Code') }}
                    </x-secondary-button>
                </div>
            </div>
        @else
            <div class="mt-5">
                <x-button wire:click="generateQrCode">
                    {{ __('Generate My QR Code') }}
                </x-button>
            </div>
        @endif
    </x-slot>
</x-action-section>
