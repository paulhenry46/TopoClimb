<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Log;
new class extends Component {

    public User $user;
    public $logs;

    public function mount(){
      
      $this->user  = auth()->user();
      $this->logs = Log::where('user_id', $this->user->id)->with('route')->orderByDesc('created_at')->take(3)->get();

   


    }
}; ?>

<div>
    <table class="border-separate border-spacing-y-3 min-w-full divide-y divide-gray-300 table-fixed">
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
</div>