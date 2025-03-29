<?php

use Livewire\Volt\Component;
use App\Models\Log;
use App\Models\Area;
use App\Models\Sector;
use App\Models\Line;
use App\Models\User;
use App\Models\Tag;
use App\Models\Route;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use Carbon\Carbon;
new class extends Component {
  use WithFileUploads;

    public Route $route;
    public User $user;
    public Area $area;

    #[Validate('required')]
    public Int $cotation;
    #[Validate('required')]
    public $type;
    #[Validate('string')]
    public string $way;
    #[Validate('string')]
    public  $comment;
    #[Validate('url')]
    public string $videoUrl;

    public $date;
  public $cotations;


    public function mount(Route $route){
      $this->route = $route;
      $this->area = $route->line->sector->area;
      $this->user = Auth::user();
      $this->comment = null;
      $this->cotations = [
        '3a' => 300, '3a+' => 310, '3b' => 320, '3b+' => 330, '3c' => 340, '3c+' => 350, 
        '4a' => 400, '4a+' => 410, '4b' => 420, '4b+' => 430, '4c' => 440, '4c+' => 450, 
        '5a' => 500, '5a+' => 510, '5b' => 520, '5b+' => 530, '5c' => 540, '5c+' => 550, 
        '6a' => 600, '6a+' => 610, '6b' => 620, '6b+' => 630, '6c' => 640, '6c+' => 650, 
        '7a' => 700, '7a+' => 710, '7b' => 720, '7b+' => 730, '7c' => 740, '7c+' => 750, 
        '8a' => 800, '8a+' => 810, '8b' => 820, '8b+' => 830, '8c' => 840, '8c+' => 850, 
        '9a' => 900, '9a+' => 910, '9b' => 920, '9b+' => 930, '9c' => 940, '9c+' => 950,];
        $this->cotation = $this->route->grade;
        $this->type = 'view';
        if($this->area->type == 'trad'){
          $this->way = 'top-rope';
        }else{
          $this->way = 'bouldering';
        }
        $this->comment = '';
        $this->videoUrl = '';
    }

    public function save(){
        $this->validate();
        $log = new Log;
        $log->user_id = $this->user->id;
        
        $log->route_id = $this->route->id;
        $log->type = $this->type;
        $log->comment = $this->comment;
        $log->grade = $this->cotation;
        $log->way = $this->way;
        $log->save();
    }
    protected function gradeToInt($grade){
        $array = [
        '3a' => 300, '3a+' => 310, '3b' => 320, '3b+' => 330, '3c' => 340, '3c+' => 350, 
        '4a' => 400, '4a+' => 410, '4b' => 420, '4b+' => 430, '4c' => 440, '4c+' => 450, 
        '5a' => 500, '5a+' => 510, '5b' => 520, '5b+' => 530, '5c' => 540, '5c+' => 550, 
        '6a' => 600, '6a+' => 610, '6b' => 620, '6b+' => 630, '6c' => 640, '6c+' => 650, 
        '7a' => 700, '7a+' => 710, '7b' => 720, '7b+' => 730, '7c' => 740, '7c+' => 750, 
        '8a' => 800, '8a+' => 810, '8b' => 820, '8b+' => 830, '8c' => 840, '8c+' => 850, 
        '9a' => 900, '9a+' => 910, '9b' => 920, '9b+' => 930, '9c' => 940, '9c+' => 950,];
        return $array[$grade];
       /* if (preg_match('/^([3-9][abc])(\+?)$/', $grade, $matches)) {
            $base = (int)$matches[1][0] * 100 + (ord($matches[1][1]) - ord('a')) * 20;
            $modifier = $matches[2] === '+' ? 10 : 0;
            return $base + $modifier;
        }*/
    }
}; ?>

<div x-data="{ open: false }">
  <button @click="open=true" type="button" class="rounded-md bg-gray-800 p-2 text-white shadow-sm hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" fill="currentColor">
      <path d="m382-354 339-339q12-12 28-12t28 12q12 12 12 28.5T777-636L410-268q-12 12-28 12t-28-12L182-440q-12-12-11.5-28.5T183-497q12-12 28.5-12t28.5 12l142 143Z" />
    </svg>
  </button>
  <div class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-show="open">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
      <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
          <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-gray-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">{{ __('Your ascent') }}</h3>
                <div class="mt-2">
                  <div class="grid grid-cols-2">
                    <div>
                      <x-label class="mb-2" for="adress" value="{{__('Ascent way')}}" />
                      <fieldset wire:model="type" x-data="{type: $wire.entangle('type')}">
                        <legend class="sr-only">{{__('Ascent way')}}</legend>
                        <div class="-space-y-px bg-white">
                          <label :class="type == 'view' ? 'z-10 border-gray-200 bg-gray-50' : 'border-gray-200'" class=" rounded-t-md relative flex cursor-pointer border p-4 focus:outline-none">
                            <input x-model="type" type="radio" name="area-type" value="view" class="mt-0.5 h-4 w-4 shrink-0 cursor-pointer text-gray-600 border-gray-300 focus:ring-0 focus:ring-offset-0 active:ring-0  active:ring-gray-600" aria-labelledby="privacy-setting-0-label" aria-describedby="privacy-setting-0-description">
                            <span class="ml-3 flex flex-col">
                              <span :class="type == 'view' ? 'text-gray-900' : 'text-gray-900'" id="privacy-setting-0-label" class="block text-sm font-medium">{{__('View')}}</span>
                              <span :class="type == 'view' ? 'text-gray-700' : 'text-gray-500'" id="privacy-setting-0-description" class="block text-sm"> {{__('Area for climbing with distinct lines')}}</span>
                            </span>
                          </label>
                          <label :class="type == 'flash' ? 'z-10 border-gray-200 bg-gray-50' : 'border-gray-200'" class="relative flex cursor-pointer border p-4 focus:outline-none">
                            <input x-model="type" type="radio" name="area-type" value="flash" class="mt-0.5 h-4 w-4 shrink-0 cursor-pointer text-gray-600 border-gray-300 focus:ring-0 focus:ring-offset-0 active:ring-0  active:ring-gray-600" aria-labelledby="privacy-setting-1-label" aria-describedby="privacy-setting-1-description">
                            <span class="ml-3 flex flex-col">
                              <span :class="type == 'flash' ? 'text-gray-900' : 'text-gray-900'" id="privacy-setting-1-label" class="block text-sm font-medium">{{__('Flash')}}</span>
                              <span :class="type == 'flash' ? 'text-gray-700' : 'text-gray-500'" id="privacy-setting-1-description" class="block text-sm">{{__('Area for bouldering without line')}}</span>
                            </span>
                          </label>
                          <label :class="type == 'work' ? 'z-10 border-gray-200 bg-gray-50' : 'border-gray-200'" class="rounded-b-md relative flex cursor-pointer border p-4 focus:outline-none">
                            <input x-model="type" type="radio" name="area-type" value="work" class="mt-0.5 h-4 w-4 shrink-0 cursor-pointer text-gray-600 border-gray-300 focus:ring-0 focus:ring-offset-0 active:ring-0  active:ring-gray-600" aria-labelledby="privacy-setting-1-label" aria-describedby="privacy-setting-1-description">
                            <span class="ml-3 flex flex-col">
                              <span :class="type == 'work' ? 'text-gray-900' : 'text-gray-900'" id="privacy-setting-1-label" class="block text-sm font-medium">{{__('After work')}}</span>
                              <span :class="type == 'work' ? 'text-gray-700' : 'text-gray-500'" id="privacy-setting-1-description" class="block text-sm">{{__('Area for bouldering without line')}}</span>
                            </span>
                          </label>
                        </div>
                      </fieldset>
                    </div>
                    <div class="space-y-2 px-4">
                      @if ($this->area->type == 'trad')
                      <div class="w-full">
                        <x-label for="name" value="{{ __('Type') }}" />
                        <div class="mt-2">
                          <select wire:model="way" id="location" name="location" class=" block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6">
                            <option value="moul">{{__('Moulinette')}}</option>
                            <option value="tete">{{ __('En tete') }}</option>
                          </select>
                          <x-input-error for="name" class="mt-2" />
                        </div>
                      </div>
                      @endif
                      <div class="w-full mt-3">
                        <x-label for="adress" value="{{ __('Comments') }}" />
                        <div class="mt-2">
                          <textarea wire:model="comment" id="adress" name="adress" rows="2" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6"></textarea>
                          <x-input-error for="adress" class="mt-2" />
                        </div>
                      </div>
                      <div class="w-full">
                        <x-label for="name" value="{{ __('Your cotation') }}" />
                        <div class="mt-2 flex items-center gap-2">
                       <select wire:model='cotation' id="location" name="location" class=" block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6">
                        <option value="0">+ ∞</option>
                        @foreach ($this->cotations as $key => $value)
                        <option @if ($value == $this->cotation)
                          selected
                        @endif value="{{ $value }}">{{ $key }}</option>
                        @endforeach
                      </select>
                        <x-input-error for="name" class="mt-2" />
                        </div>
                      </div>
                    </div>
                    <div class="w-full col-span-2 mt-3">
                      <x-label for="adress" value="{{ __('Video URL') }}" />
                      <div class="mt-2">
                        <x-input class="w-full" wire:model="adress" id="adress" name="adress"/>
                        <x-input-error for="adress" class="mt-2" />
                      </div>
                    </div>
                    <div></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 sm:gap-x-1">
            <x-secondary-button x-on:click="open = false" type="button">{{__('Cancel')}}</x-secondary-button>
            <x-button @click="$wire.save()">{{__('Save')}}</x-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>