<?php

use Livewire\Volt\Component;
use App\Models\Log;
use App\Models\Area;
use App\Models\Sector;
use App\Models\Line;
use App\Models\User;
use App\Models\Tag;
use App\Models\Route;
use Livewire\Attributes\Computed;
use Carbon\Carbon;
new class extends Component {

    public Route $route;
    public User $user;
    public Area $area;
    
    public Int $cotation;
    public $type;
    public string $way;
    public  $comment;
    public string $videoUrl;

    public $date;
    public $cotations;

        public function rules()
    {
        return [
            'cotation' => 'required|integer',
            'type' => 'required',
            'way' => 'string',
            'comment' => 'string|nullable',
            'videoUrl' => 'url|nullable',
        ];
    }

    public function mount(Route $route){
      $this->route = $route;
      $this->area = $route->line->sector->area;
      $this->user = Auth::user();
      $this->comment = null;
      $this->cotations = $this->area->site->cotations();/* config('climb.default_cotation');*/
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
        return $this->cotations[$grade];
    }
    public function rendering(){
      $this->cotation = $this->route->grade;
    }
}; ?>

<div x-data="{ open: false, save(){$wire.way = this.way; $wire.type= this.type; $wire.save(); this.open = false;}, step:1, type: 'work', way: 'top-rope', 
            next_action(){
                if(this.step == @if ($this->area->type == 'trad')3 @else 2 @endif ){this.save()}else{this.step++;}
                },
                cancel_action(){this.open = false; this.step = 1;} 
                }" @show_modal.window="open = true">
  <div class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-show="open" x-cloak>
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
              <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                
                
                <div class='mt-2 text-left' x-show='step == 1'> 
                  <div class="mx-auto max-w-lg">
                    <h2 class="text-base font-semibold leading-6 text-gray-900">{{ __('Ascent type') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Get started by selecting how you climbed the route') }}</p>
                    <ul role="list" class="mt-6 divide-y divide-gray-200 border-b border-t border-gray-200">
                      <li x-on:click='type = "view"'>
                        <div class="group relative flex items-start space-x-3 py-4">
                          <div class="flex-shrink-0">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-500">
                              <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 010 3.46" />
                              </svg>
                            </span>
                          </div>
                          <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-gray-900">
                              <a href="#">
                                <span class="absolute inset-0" aria-hidden="true"></span>
                                {{__('View')}}
                              </a>
                            </div>
                            <p class="text-sm text-gray-500">{{ __('Successfully complete a route on the first attempt, without knowing the difficulties involved') }}</p>
                          </div>
                           <div class="flex-shrink-0 self-center text-indigo-500" x-show='type =="view"'>
                            <x-icons.icon-check/>
                          </div>
                        </div>
                      </li>
                      <li x-on:click='type = "flash"'>
                        <div class="group relative flex items-start space-x-3 py-4">
                          <div class="flex-shrink-0">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-amber-500">
                              <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z" />
                              </svg>
                            </span>
                          </div>
                          <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-gray-900">
                              <a href="#">
                                <span class="absolute inset-0" aria-hidden="true"></span>
                                {{__('Flash')}}
                              </a>
                            </div>
                            <p class="text-sm text-gray-500">{{ __('Flashering a route means linking it together without having worked on it, but knowing the movements you\'ve read from the ground.') }}</p>
                          </div>
                          <div class="flex-shrink-0 self-center text-amber-500" x-show='type =="flash"'>
                            <x-icons.icon-check/>
                          </div>
                        </div>
                      </li>
                      <li x-on:click='type = "work"'>
                        <div class="group relative flex items-start space-x-3 py-4">
                          <div class="flex-shrink-0">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500">
                              <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                              </svg>
                            </span>
                          </div>
                          <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-gray-900">
                              <a href="#">
                                <span class="absolute inset-0" aria-hidden="true"></span>
                                {{__('After work')}}
                              </a>
                            </div>
                            <p class="text-sm text-gray-500">{{ __('Here, we know the movements, we\'ve already worked the way.') }}</p>
                          </div>
                           <div class="flex-shrink-0 self-center text-emerald-500" x-show='type =="work"'>
                            <x-icons.icon-check/>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
                @if ($this->area->type == 'trad')
                <div class='mt-2 text-left' x-show='step == 2'> 
                  <div class="mx-auto max-w-lg">
                    <h2 class="text-base font-semibold leading-6 text-gray-900">{{ __('Ascent way') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Continue by selecting the type of ascent') }}</p>
                    <ul role="list" class="mt-6 divide-y divide-gray-200 border-b border-t border-gray-200">
                      <li x-on:click='way = "top-rope"'>
                        <div class="group relative flex items-start space-x-3 py-4">
                          <div class="flex-shrink-0">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500">
                              <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 010 3.46" />
                              </svg>
                            </span>
                          </div>
                          <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-gray-900">
                              <a href="#">
                                <span class="absolute inset-0" aria-hidden="true"></span>
                                {{__('Top-Rope')}}
                              </a>
                            </div>
                            <p class="text-sm text-gray-500">{{ __('The rope is already in the belay at the top of the route.') }}</p>
                          </div>
                           <div class="flex-shrink-0 self-center text-blue-500" x-show='way =="top-rope"'>
                            <x-icons.icon-check/>
                          </div>
                        </div>
                      </li>
                      <li x-on:click='way = "lead"'>
                        <div class="group relative flex items-start space-x-3 py-4">
                          <div class="flex-shrink-0">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-pink-500">
                              <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z" />
                              </svg>
                            </span>
                          </div>
                          <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-gray-900">
                              <a href="#">
                                <span class="absolute inset-0" aria-hidden="true"></span>
                                {{__('Lead')}}
                              </a>
                            </div>
                            <p class="text-sm text-gray-500">{{ __('The climber places the rope in the quickdraws as he progresses up the route, or even the quickdraws..') }}</p>
                          </div>
                          <div class="flex-shrink-0 self-center text-pink-500" x-show='way =="lead"'>
                            <x-icons.icon-check/>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
                @endif

                @if ($this->area->type == 'trad')
                <div class="mt-2 w-full" x-show='step == 3'>
                  @else
                  <div class="mt-2 w-full" x-show='step == 2'>
                  @endif
                   <h2 class="text-base font-semibold leading-6 text-gray-900">{{ __('Your comments') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ __('you can comment this route and show the felt grade') }}</p>

                  <div class="grid grid-cols-2">
                      <div class="w-full col-span-2">
                        <x-label for="address" value="{{ __('Comments') }}" />
                        <div class="mt-2">
                          <textarea wire:model="comment" id="address" name="address" rows="2" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6"></textarea>
                          <x-input-error for="address" class="mt-2" />
                        </div>
                      </div>
                      <div class="w-full mt-3">
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
                      <div class="w-full mt-3">
                      <x-label for="address" value="{{ __('Video URL') }}" />
                      <div class="mt-2">
                        <x-input class="text-xs w-full" wire:model="address" id="address" name="address"/>
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
            <x-secondary-button x-on:click="cancel_action()" type="button">{{__('Cancel')}}</x-secondary-button>
            <x-button x-on:click='next_action()'>{{__('Continue')}}</x-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>