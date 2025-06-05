<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Log;
new class extends Component {

    public User $user;
    public $logs;
    public $favorited;

    public function mount(){
      
      $this->user  = auth()->user();
      $this->logs = Log::where('user_id', $this->user->id)->with('route')->orderByDesc('created_at')->take(5)->get();
      $this->favorites =$this->user->registeredRoutes()->paginate(5);
    }
}; ?>
<div class="bg-white overflow-hidden  sm:rounded-lg md:col-span-3" x-data="{type_show: 'history'}">
                <div class='px-4 py-4 flex justify-between items-center'>
                    <h2 class="px-4 py-4 text-xl font-semibold text-gray-900">
                       {{ __('Routes') }}
                    </h2>
                    <select x-model='type_show' class='h-10 block  rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6'>
                <option value="history">{{ __('Recents') }}</option>
                <option value="registered">{{ __('Registered') }}</option>
                
            </select>
          </div>
<div>
    <table x-show='type_show == "history"' class="border-separate border-spacing-y-3 min-w-full divide-y divide-gray-300 table-fixed">
        <tbody class="bg-white"> @foreach ($this->logs as $log) <tr 
          class="hover:bg-gray-50 cursor-pointer">
          <td class="bg-{{$log->route->color}}-300 border-2 border-{{$log->route->color}}-300 rounded-l-md text-center h-16 w-16 relative whitespace-nowrap font-medium text-gray-900">
           <div class='grayscale rounded-l h-full w-full bg-cover' style="background-image: url({{ $log->route->thumbnail() }})"></div>
          </td>
            <td class=" text-2xl text-center w-16 bg-{{$log->route->color}}-300 relative whitespace-nowrap py-4 pl-4 pr-3 font-medium text-gray-900 sm:pl-3">
              {{$log->route->gradeFormated()}}
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

      <table x-show='type_show == "registered"' class="border-separate border-spacing-y-3 min-w-full divide-y divide-gray-300 table-fixed">
        <tbody class="bg-white"> @foreach ($this->favorites as $route) <tr 
          class="hover:bg-gray-50 cursor-pointer">
          <td class="bg-{{$route->color}}-300 border-2 border-{{$route->color}}-300 rounded-l-md text-center h-16 w-16 relative whitespace-nowrap font-medium text-gray-900">
           <div class='grayscale rounded-l h-full w-full bg-cover' style="background-image: url({{ $route->thumbnail() }})"></div>
          </td>
            <td class=" text-2xl text-center w-16 bg-{{$route->color}}-300 relative whitespace-nowrap py-4 pl-4 pr-3 font-medium text-gray-900 sm:pl-3">
              {{$route->gradeFormated()}}
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
</div>
</div>
</div>