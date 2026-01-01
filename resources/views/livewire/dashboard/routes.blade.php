<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Log;
new class extends Component {

    public User $user;
    public $logs;
    public $favorited;
    public $friendsLogs;

    public function mount(){
      
      $this->user  = auth()->user();
      $this->logs = Log::where('user_id', $this->user->id)->with('route')->orderByDesc('created_at')->take(5)->get();
      $this->favorites =$this->user->registeredRoutes()->paginate(5);
      
      // Get friends logs
      $friends = $this->user->friends->merge($this->user->friendOf)->unique('id')->pluck('id');
      $this->friendsLogs = Log::whereIn('user_id', $friends)
                              ->with(['route', 'user'])
                              ->orderByDesc('created_at')
                              ->take(5)
                              ->get();
    }
}; ?>
<div class="bg-white overflow-hidden  sm:rounded-lg md:col-span-3 " x-data="{type_show: 'history'}">
          <div class='flex px-4 items-center my-3'>
            <h2 class="px-4  text-xl font-semibold text-gray-900 mr-2">
              {{ __('Routes') }}
            </h2>
            <div class="w-full flex space-x-2">
              <button
                class="flex-1 py-2 rounded-l-md text-center text-sm font-medium focus:outline-none transition-all duration-150"
                :class="type_show === 'history' ? 'bg-gray-500 text-white shadow' : 'bg-gray-100 text-gray-700'"
                @click="type_show = 'history'"
                type="button"
              >
                {{ __('Recents') }}
              </button>
              <button
                class="flex-1 py-2  text-center text-sm font-medium focus:outline-none transition-all duration-150"
                :class="type_show === 'registered' ? 'bg-gray-500 text-white shadow' : 'bg-gray-100 text-gray-700'"
                @click="type_show = 'registered'"
                type="button"
              >
                {{ __('Registered') }}
              </button>
              <button
                class="flex-1 py-2 rounded-r-md text-center text-sm font-medium focus:outline-none transition-all duration-150"
                :class="type_show === 'friends' ? 'bg-gray-500 text-white shadow' : 'bg-gray-100 text-gray-700'"
                @click="type_show = 'friends'"
                type="button"
              >
                {{ __('Friends') }}
              </button>
            </div>
          </div>
<div class='mx-5 md:min-h-96 '>
<div x-show='type_show == "history"'>
  @if(!$this->logs->isEmpty())
    <table  class="border-separate border-spacing-y-3 min-w-full divide-y divide-gray-300 table-fixed">
        <tbody class="bg-white"> @foreach ($this->logs as $log) <tr 
          class="hover:bg-gray-50 cursor-pointer">
          <td class="bg-{{$log->route->color}}-300 border-2 border-{{$log->route->color}}-300 rounded-l-md text-center h-16 w-16 relative whitespace-nowrap font-medium text-gray-900">
           <div class='grayscale rounded-l h-full w-full bg-cover' style="background-image: url({{ $log->route->thumbnail() }})"></div>
          </td>
            <td class=" text-2xl text-center w-16 bg-{{$log->route->color}}-300 relative whitespace-nowrap py-4 pl-4 pr-3 font-medium text-gray-900 sm:pl-3">
              {{$log->route->defaultGradeFormated()}}
            </td>
            <td class="  whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
              <div class="flex items-center">
                <div>
                  <div class="font-bold pb-1">{{$log->route->name}}</div>   <div class="text-sm opacity-50">{{$log->created_at->format('d/m/y')}}</div>
                </div>
              </div>
            </td>
            <td class=" relative whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3"> 
                <span class=""> @if($log->way == 'top-rope') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                    <svg class="h-1.5 w-1.5 fill-red-500" viewBox="0 0 6 6" aria-hidden="true">
                      <circle cx="3" cy="3" r="3" />
                    </svg>
                    {{ __('Top-rope') }}
                  </a> @elseif($log->way == 'lead') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                    <svg class="h-1.5 w-1.5 fill-green-500" viewBox="0 0 6 6" aria-hidden="true">
                      <circle cx="3" cy="3" r="3" />
                    </svg>
                    {{ __('Leading') }}
                  </a> @endif @if($log->type == 'view') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                    <svg class="h-1.5 w-1.5 fill-indigo-500" viewBox="0 0 6 6" aria-hidden="true">
                      <circle cx="3" cy="3" r="3" />
                    </svg>
                    {{ __('View') }}
                  </a> @elseif($log->type == 'work') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                    <svg class="h-1.5 w-1.5 fill-emerald-500" viewBox="0 0 6 6" aria-hidden="true">
                      <circle cx="3" cy="3" r="3" />
                    </svg>
                    {{ __('After work') }}
                  </a> @elseif($log->type == 'flash') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                    <svg class="h-1.5 w-1.5 fill-amber-500" viewBox="0 0 6 6" aria-hidden="true">
                      <circle cx="3" cy="3" r="3" />
                    </svg>
                    {{ __('Flash') }}
                  </a> @endif </span>
            </td>
          </tr> @endforeach </tbody>
      </table>
      @else
      <div class="col-span-3 flex flex-col items-center justify-center text-gray-300">
        <svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 -960 960 960" width="48px" fill="currentColor"><path d="M680-680h-40q-17 0-28.5-11.5T600-720q0-17 11.5-28.5T640-760h40v-40q0-17 11.5-28.5T720-840q17 0 28.5 11.5T760-800v40h40q17 0 28.5 11.5T840-720q0 17-11.5 28.5T800-680h-40v40q0 17-11.5 28.5T720-600q-17 0-28.5-11.5T680-640v-40ZM480-240l-168 72q-40 17-76-6.5T200-241v-519q0-33 23.5-56.5T280-840h225q18 0 27 16t1 33q-7 17-10 34t-3 37q0 72 45.5 127T680-524q12 2 21.5 2.5t18.5.5q17 0 28.5 10.5T760-484v243q0 43-36 66.5t-76 6.5l-168-72Z"/></svg>
        <h3 class="text-2xl font-semibold text-gray-700 mb-2">{{ __('No climbed routes yet') }}</h3>
        <p class="text-gray-500 mb-6 text-center max-w-md">{{ __("Explore routes proposed by sites and come back !. When you will have climbed your first route, it will appear here !") }}</p>
            <a wire:navigate href="{{ route('sites.public-index') }}" class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-md shadow hover:bg-gray-700 transition">
                
                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg"  viewBox="0 -960 960 960" fill="currentColor"><path d="M380-320q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l224 224q11 11 11 28t-11 28q-11 11-28 11t-28-11L532-372q-30 24-69 38t-83 14Zm0-80q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/></svg>

                {{ __('Explore sites') }}
            </a>
    </div>
    @endif
    </div>
<div x-show='type_show == "registered"' >
  @if(!$this->favorites->isEmpty())
      <table class="border-separate border-spacing-y-3 min-w-full divide-y divide-gray-300 table-fixed">
        <tbody class="bg-white"> @foreach ($this->favorites as $route) <tr 
          class="hover:bg-gray-50 cursor-pointer">
          <td class="bg-{{$route->color}}-300 border-2 border-{{$route->color}}-300 rounded-l-md text-center h-16 w-16 relative whitespace-nowrap font-medium text-gray-900">
           <div class='grayscale rounded-l h-full w-full bg-cover' style="background-image: url({{ $route->thumbnail() }})"></div>
          </td>
            <td class=" text-2xl text-center w-16 bg-{{$route->color}}-300 relative whitespace-nowrap py-4 pl-4 pr-3 font-medium text-gray-900 sm:pl-3">
              {{$route->defaultGradeFormated()}}
            </td>
            <td class="  whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
              <div class="flex items-center">
                <div>
                  <div class="font-bold pb-1">{{$route->name}}</div>   <div class="text-sm opacity-50">{{$route->created_at->format('d/m/y')}}</div>
                </div>
              </div>
            </td>
          </tr> @endforeach </tbody>
      </table>
      @else
       <div class="col-span-3 flex flex-col items-center justify-center text-gray-300">
        <svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 -960 960 960" width="48px" fill="currentColor"><path d="M680-680h-40q-17 0-28.5-11.5T600-720q0-17 11.5-28.5T640-760h40v-40q0-17 11.5-28.5T720-840q17 0 28.5 11.5T760-800v40h40q17 0 28.5 11.5T840-720q0 17-11.5 28.5T800-680h-40v40q0 17-11.5 28.5T720-600q-17 0-28.5-11.5T680-640v-40ZM480-240l-168 72q-40 17-76-6.5T200-241v-519q0-33 23.5-56.5T280-840h225q18 0 27 16t1 33q-7 17-10 34t-3 37q0 72 45.5 127T680-524q12 2 21.5 2.5t18.5.5q17 0 28.5 10.5T760-484v243q0 43-36 66.5t-76 6.5l-168-72Z"/></svg>
        <h3 class="text-2xl font-semibold text-gray-700 mb-2">{{ __('No routes yet') }}</h3>
        <p class="text-gray-500 mb-6 text-center max-w-md">{{ __("You have not yet favorited route. Explore routes proposed by sites and come back !") }}</p>
            <a wire:navigate href="{{ route('sites.public-index') }}" class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-md shadow hover:bg-gray-700 transition">
                
                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg"  viewBox="0 -960 960 960" fill="currentColor"><path d="M380-320q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l224 224q11 11 11 28t-11 28q-11 11-28 11t-28-11L532-372q-30 24-69 38t-83 14Zm0-80q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/></svg>

                {{ __('Explore sites') }}
            </a>
    </div>
      @endif

    </div>
<div x-show='type_show == "friends"' >
  @if(!$this->friendsLogs->isEmpty())
      <table class="border-separate border-spacing-y-3 min-w-full divide-y divide-gray-300 table-fixed">
        <tbody class="bg-white"> @foreach ($this->friendsLogs as $log) <tr 
          class="hover:bg-gray-50 cursor-pointer">
          <td class="bg-{{$log->route->color}}-300 border-2 border-{{$log->route->color}}-300 rounded-l-md text-center h-16 w-16 relative whitespace-nowrap font-medium text-gray-900">
           <div class='grayscale rounded-l h-full w-full bg-cover' style="background-image: url({{ $log->route->thumbnail() }})"></div>
          </td>
            <td class=" text-2xl text-center w-16 bg-{{$log->route->color}}-300 relative whitespace-nowrap py-4 pl-4 pr-3 font-medium text-gray-900 sm:pl-3">
              {{$log->route->defaultGradeFormated()}}
            </td>
            <td class="  whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
              <div class="flex items-center">
                <div>
                  <div class="font-bold pb-1">{{$log->route->name}}</div>
                  <div class="text-sm opacity-50">
                    {{ $log->user->name }} - {{$log->created_at->format('d/m/y')}}
                  </div>
                </div>
              </div>
            </td>
            <td class=" relative whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3"> 
                <span class=""> @if($log->way == 'top-rope') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                    <svg class="h-1.5 w-1.5 fill-red-500" viewBox="0 0 6 6" aria-hidden="true">
                      <circle cx="3" cy="3" r="3" />
                    </svg>
                    {{ __('Top-rope') }}
                  </a> @elseif($log->way == 'lead') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                    <svg class="h-1.5 w-1.5 fill-green-500" viewBox="0 0 6 6" aria-hidden="true">
                      <circle cx="3" cy="3" r="3" />
                    </svg>
                    {{ __('Leading') }}
                  </a> @endif @if($log->type == 'view') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                    <svg class="h-1.5 w-1.5 fill-indigo-500" viewBox="0 0 6 6" aria-hidden="true">
                      <circle cx="3" cy="3" r="3" />
                    </svg>
                    {{ __('View') }}
                  </a> @elseif($log->type == 'work') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                    <svg class="h-1.5 w-1.5 fill-emerald-500" viewBox="0 0 6 6" aria-hidden="true">
                      <circle cx="3" cy="3" r="3" />
                    </svg>
                    {{ __('After work') }}
                  </a> @elseif($log->type == 'flash') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                    <svg class="h-1.5 w-1.5 fill-amber-500" viewBox="0 0 6 6" aria-hidden="true">
                      <circle cx="3" cy="3" r="3" />
                    </svg>
                    {{ __('Flash') }}
                  </a> @endif </span>
            </td>
          </tr> @endforeach </tbody>
      </table>
      @else
       <div class="col-span-3 flex flex-col items-center justify-center text-gray-300">
        <svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 -960 960 960" width="48px" fill="currentColor"><path d="M40-160v-112q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q66 0 130 15.5T616-378q29 15 46.5 43.5T680-272v112H40Zm720 0v-120q0-44-24.5-84.5T666-434q51 6 96 20.5t84 35.5q36 20 55 44.5t19 53.5v120H760ZM360-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm400-160q0 66-47 113t-113 47q-11 0-28-2.5t-28-5.5q27-32 41.5-71t14.5-81q0-42-14.5-81T544-792q14-5 28-6.5t28-1.5q66 0 113 47t47 113ZM120-240h480v-32q0-11-5.5-20T580-306q-54-27-109-40.5T360-360q-56 0-111 13.5T140-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T440-640q0-33-23.5-56.5T360-720q-33 0-56.5 23.5T280-640q0 33 23.5 56.5T360-560Zm0 320Zm0-400Z"/></svg>
        <h3 class="text-2xl font-semibold text-gray-700 mb-2">{{ __('No friend routes yet') }}</h3>
        <p class="text-gray-500 mb-6 text-center max-w-md">{{ __("Your friends haven't climbed any routes yet, or you haven't added any friends.") }}</p>
    </div>
      @endif

    </div>
</div>
</div>
</div>