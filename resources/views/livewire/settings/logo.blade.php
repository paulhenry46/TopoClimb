<?php

use Livewire\Volt\Component;

use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;
    public $picture;

    public function updated(){
        if($this->picture !== null){
          $this->picture->storeAs(path: 'pictures', name: 'logo');
        }
        $this->dispatch('action_ok', title: 'Logo uploaded', message: 'Your logo has been uploaded !');
}

}; ?>

<div class="px-4 sm:px-6 lg:px-8 py-8 bg-white sm:rounded-lg">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Logo')}}</h1>
            <p class="mt-2 text-sm text-gray-700">{{__('Upload logo of website')}}</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <x-input wire:model.live="picture" type="file" name="picture" id="project-name" class="mt-1 block w-full file:inline-flex file:items-center file:px-4 file:py-2 file:bg-gray-800 file:border file:border-transparent file:rounded-md file:font-semibold file:text-sm file:text-white file:tracking-widest file:hover:bg-gray-700 file:focus:bg-gray-700 file:active:bg-gray-900 file:focus:outline-hidden file:disabled:opacity-50 file:transition file:ease-in-out file:duration-150" />
        </div>
    </div>
</div>