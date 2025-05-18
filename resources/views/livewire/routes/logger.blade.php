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
use Livewire\Attributes\Reactive;
new class extends Component {
  use WithFileUploads;
    #[Reactive]
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

      $existingLog = Log::where('user_id', $this->user->id)
        ->where('route_id', $this->route->id)
        ->where('way', $this->way)
        ->exists();

    if ($existingLog) {
        $this->dispatch('action_error', title: 'Duplicate Log', message: 'You have already logged this route.');
        return;
    }

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
    public function rendering(){
      $this->cotation = $this->route->grade;
    }
}; ?>

<div x-data="{ open: false, save(){$wire.save(); this.open = false;} }">
  <button @click="open=true" type="button" class="cursor-pointer rounded-md bg-gray-800 p-2 text-white shadow-xs hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" fill="currentColor">
      <path d="m382-354 339-339q12-12 28-12t28 12q12 12 12 28.5T777-636L410-268q-12 12-28 12t-28-12L182-440q-12-12-11.5-28.5T183-497q12-12 28.5-12t28.5 12l142 143Z" />
    </svg>
  </button>
  <div class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-show="open" style="display: none;">
    <div class="fixed inset-0 bg-gray-500/75 transition-opacity" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
      <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
          <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gray-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-7 w-7"
                  viewBox="0 0 389 515"
                  id="svg2"
                  version="1.1"
                  xmlns="http://www.w3.org/2000/svg"
                  xmlns:svg="http://www.w3.org/2000/svg">
                  <defs
                    id="defs17" />
                  <g
                    id="g1"
                    transform="translate(-984.5876,180.9994)">
                    <path
                      id="path5040"
                      d="m 1185.0503,160.80697 c -4.8166,1.77753 -10.2066,2.80966 -15.7113,2.80966 -5.906,0 -11.7548,-0.68808 -17.1448,-2.23626 l -60.8955,139.96802 c -5.562,12.90161 -20.8719,17.25946 -34.2322,9.74786 -13.303,-7.5116 -19.6105,-23.96832 -13.8764,-36.8699 l 74.6572,-171.21854 h 95.1852 V 87.00982 l 48.1659,-22.42013 c 12.6149,-6.13541 29.0143,4.35788 32.34,17.7182 l 23.6243,94.72641 c 3.3831,13.41766 -4.8166,27.00735 -18.2343,30.3331 -13.475,3.38309 -27.122,-4.81659 -30.4477,-18.23425 l -15.0233,-60.37949 z"
                      style="fill:#231f20;fill-rule:nonzero" />
                    <path
                      id="path5042"
                      d="M 1228.9158,62.69748 C 1333.218,10.97639 1373.643,-166.20556 1373.643,-180.9994 h -14.3351 c -5.39,36.92725 -50.1729,184.80823 -130.3921,227.69889 z"
                      style="fill:#231f20;fill-rule:nonzero" />
                    <path
                      id="path5044"
                      d="m 1036.4812,23.93533 c 5.4474,8.54373 16.4568,12.32819 26.2046,8.31436 l 55.1615,-21.61734 v 76.49216 h 95.1852 V -1.46648 l 85.3225,-136.52759 c 6.2501,-9.97723 3.3831,-23.16553 -6.7088,-29.58766 -9.9772,-6.25011 -23.1655,-3.32574 -28.7849,6.42213 l -72.4783,115.9997 -43.6361,0.11469 c -2.8097,0 -5.6193,0.45872 -8.429,1.5482 l -75.4027,29.58764 -38.8768,-58.88862 c -6.3648,-9.86255 -19.6105,-12.78691 -29.5877,-6.42214 -9.97723,6.42214 -12.84426,19.72512 -6.47947,29.70237 z"
                      style="fill:#231f20;fill-rule:nonzero" />
                    <path
                      id="path5046"
                      d="m 1158.9031,-52.90085 c 20.9866,0 38.0167,-16.91544 38.0167,-37.8447 0,-20.92925 -17.0301,-37.95936 -38.0167,-37.95936 -20.9293,0 -37.9594,17.03011 -37.9594,37.95936 0,20.92926 17.0301,37.8447 37.9594,37.8447"
                      style="fill:#231f20;fill-rule:nonzero" />
                    <path
                      id="path5048"
                      d="M 1213.0325,334.37646 V 163.61664 l -15.998,7.45425 v 163.30557 z"
                      style="fill:#231f20;fill-rule:nonzero" />
                  </g>
                </svg>
              </div>
              <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">{{ __('Your ascent') }}</h3>
                <div class="mt-2">
                  <div class="grid grid-cols-2">
                    <div>
                      <x-label class="mb-2" for="address" value="{{__('Ascent way')}}" />
                      <fieldset wire:model="type" x-data="{type: $wire.entangle('type')}">
                        <legend class="sr-only">{{__('Ascent way')}}</legend>
                        <div class="-space-y-px bg-white">
                          <label :class="type == 'view' ? 'z-10 border-gray-200 bg-gray-50' : 'border-gray-200'" class=" rounded-t-md relative flex cursor-pointer border p-4 focus:outline-hidden">
                            <input x-model="type" type="radio" name="area-type" value="view" class="mt-0.5 h-4 w-4 shrink-0 cursor-pointer text-gray-600 border-gray-300 active:border-0   focus:ring-0 focus:ring-offset-0 active:ring-0  active:ring-gray-600" aria-labelledby="privacy-setting-0-label" aria-describedby="privacy-setting-0-description">
                            <span class="ml-3 flex flex-col">
                              <span :class="type == 'view' ? 'text-gray-900' : 'text-gray-900'" id="privacy-setting-0-label" class="block text-sm font-medium">{{__('View')}}</span>
                              <span :class="type == 'view' ? 'text-gray-700' : 'text-gray-500'" id="privacy-setting-0-description" class="block text-sm"> {{__('Area for climbing with distinct lines')}}</span>
                            </span>
                          </label>
                          <label :class="type == 'flash' ? 'z-10 border-gray-200 bg-gray-50' : 'border-gray-200'" class="relative flex cursor-pointer border p-4 focus:outline-hidden">
                            <input x-model="type" type="radio" name="area-type" value="flash" class="mt-0.5 h-4 w-4 shrink-0 cursor-pointer text-gray-600 border-gray-300 focus:ring-0 focus:ring-offset-0 active:ring-0  active:ring-gray-600" aria-labelledby="privacy-setting-1-label" aria-describedby="privacy-setting-1-description">
                            <span class="ml-3 flex flex-col">
                              <span :class="type == 'flash' ? 'text-gray-900' : 'text-gray-900'" id="privacy-setting-1-label" class="block text-sm font-medium">{{__('Flash')}}</span>
                              <span :class="type == 'flash' ? 'text-gray-700' : 'text-gray-500'" id="privacy-setting-1-description" class="block text-sm">{{__('Area for bouldering without line')}}</span>
                            </span>
                          </label>
                          <label :class="type == 'work' ? 'z-10 border-gray-200 bg-gray-50' : 'border-gray-200'" class="rounded-b-md relative flex cursor-pointer border p-4 focus:outline-hidden">
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
                            <option value="top-rope">{{__('Moulinette')}}</option>
                            <option value="lead">{{ __('En tete') }}</option>
                          </select>
                          <x-input-error for="name" class="mt-2" />
                        </div>
                      </div>
                      @endif
                      <div class="w-full">
                        <x-label for="address" value="{{ __('Comments') }}" />
                        <div class="mt-2">
                          <textarea wire:model="comment" id="address" name="address" rows="2" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6"></textarea>
                          <x-input-error for="address" class="mt-2" />
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
                      <x-label for="address" value="{{ __('Video URL') }}" />
                      <div class="mt-2">
                        <x-input class="w-full" wire:model="address" id="address" name="address"/>
                        <x-input-error for="address" class="mt-2" />
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
            <x-button @click="save()">{{__('Save')}}</x-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>