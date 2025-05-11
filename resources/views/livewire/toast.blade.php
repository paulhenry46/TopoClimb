<?php

use Livewire\Volt\Component;
use Livewire\Attributes\On; 

new class extends Component {
    public $shown;
    public $title;
    public $message;
    public $type;

    #[On('action_ok')]
    public function toastNotification($title, $message){
        $this->title = $title;
        $this->message = $message;
        $this->shown = true;
        $this->type = 'ok';
    }

    #[On('action_error')]
    public function errorNotification($title, $message){
        $this->title = $title;
        $this->message = $message;
        $this->shown = true;
        $this->type = 'error';
    }

    public function close(){
        $this->shown = false;
    }
    public function mount(){
        $this->shown = false;
    }
}; ?>

<div aria-live="assertive" class="z-50 pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6" x-data="{open: $wire.shown}">
    <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
      @if($this->shown)
      <div class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black/5">
        <div class="p-4">
          <div class="flex items-start">
            <div class="shrink-0">
              @if($this->type == 'ok')
              <svg class="h-6 w-6 text-green-400  "  fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              @elseif($this->type == 'error')
              <svg class='text-red-500' xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M480-280q17 0 28.5-11.5T520-320q0-17-11.5-28.5T480-360q-17 0-28.5 11.5T440-320q0 17 11.5 28.5T480-280Zm0-160q17 0 28.5-11.5T520-480v-160q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640v160q0 17 11.5 28.5T480-440Zm0 360q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/>
              </svg>
              @endif
            </div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
              <p class="text-sm font-medium text-gray-900">{{$this->title}}</p>
              <p class="mt-1 text-sm text-gray-500">{{$this->message}}</p>
            </div>
            <div class="ml-4 flex shrink-0">
              <button x-on:click="$wire.close()"  type="button" class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-hidden focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                <span class="sr-only">Close</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>
