@props(['logs', 'key_button' => 'default-button'])

<div class="bg-center bg-cover h-96 rounded-t-2xl " style="background-image: url('{{ $this->route->picture() }}'); background-position-y: 50%; filter: opacity(99.9%) grayscale(100%);"></div>
  <div class="rounded-2xl bg-center bg-cover  z-10 h-96 -mt-96" style="
              background-image: url('{{$this->route->circle()}}'); filter: opacity(99.9%);"></div>
  <div class="bg-white overflow-hidden /*shadow-xl*/ sm:rounded-b-lg">
    <div class="px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex items-center">
        <div class="flex-auto">
          <h1 class="text-2xl font-semibold leading-6 text-gray-900">{{$this->route->name}}</h1>
          <p class="mt-1 text-sm text-gray-700">
            @if($this->area->type == 'bouldering')
            {{$this->route->line->sector->name}}
            @else
           {{ __('Line') }}  {{$this->route->line->local_id}}
            @endif
          </p>
        </div>
        <div x-data class="mt-4 sm:ml-16 sm:mt-0 flex gap-x-1">
          @auth
          @if($key_button == 'button-md')
          <livewire:routes.registered :route='$this->route' key='button3'/>
           <button @click="$dispatch('show_modal')" type="button" class=" cursor-pointer rounded-md bg-gray-800 p-2 text-white shadow-xs hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" fill="currentColor">
      <path d="m382-354 339-339q12-12 28-12t28 12q12 12 12 28.5T777-636L410-268q-12 12-28 12t-28-12L182-440q-12-12-11.5-28.5T183-497q12-12 28.5-12t28.5 12l142 143Z" />
    </svg>
  </button>
            @else
            <livewire:routes.registered :route='$this->route' key='button4'/>
           <button @click="$dispatch('show_modal')" type="button" class=" cursor-pointer rounded-md bg-gray-800 p-2 text-white shadow-xs hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" fill="currentColor">
      <path d="m382-354 339-339q12-12 28-12t28 12q12 12 12 28.5T777-636L410-268q-12 12-28 12t-28-12L182-440q-12-12-11.5-28.5T183-497q12-12 28.5-12t28.5 12l142 143Z" />
    </svg>
  </button>
            @endif
            @endauth
            @guest
              <a href='{{ route('login') }}' wire:navigate  class="rounded-md bg-gray-800 p-2 text-white shadow-xs hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
      <path d="m480-240-168 72q-40 17-76-6.5T200-241v-519q0-33 23.5-56.5T280-840h400q33 0 56.5 23.5T760-760v519q0 43-36 66.5t-76 6.5l-168-72Zm0-88 200 86v-518H280v518l200-86Zm0-432H280h400-200Z"></path>
    </svg>
  </a>
   <a href='{{ route('login') }}' wire:navigate class="cursor-pointer rounded-md bg-gray-800 p-2 text-white shadow-xs hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" fill="currentColor">
      <path d="m382-354 339-339q12-12 28-12t28 12q12 12 12 28.5T777-636L410-268q-12 12-28 12t-28-12L182-440q-12-12-11.5-28.5T183-497q12-12 28.5-12t28.5 12l142 143Z" />
    </svg>
  </a>
            @endguest
        </div>
      </div>
      <div class="grid grid-cols-3 mt-4 gap-x-2">
        <div class="text-gray-500 mt-4 flex w-full flex-none gap-x-2">
          <dt class="flex-none">
            <span class="sr-only">Mail</span>
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
              <path d="M480-440q58 0 99-41t41-99q0-58-41-99t-99-41q-58 0-99 41t-41 99q0 58 41 99t99 41ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-46q-54-53-125.5-83.5T480-360q-83 0-154.5 30.5T200-246v46Z" />
            </svg>
          </dt>
          <dd class="text-sm leading-6 "> @foreach ($this->route->users as $user) {{ $user->name }} @endforeach </dd>
        </div>
        <div class="text-gray-500 mt-4 flex w-full flex-none gap-x-2">
          <dt class="flex-none">
            <span class="sr-only">Mail</span>
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
              <path d="M360-300q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29ZM200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-40q0-17 11.5-28.5T280-880q17 0 28.5 11.5T320-840v40h320v-40q0-17 11.5-28.5T680-880q17 0 28.5 11.5T720-840v40h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Z" />
            </svg>
          </dt>
          <dd class="text-sm leading-6 ">{{ $this->route->created_at->format('d/m/y') }}</dd>
        </div>
        <div class="text-gray-500 mt-4 flex w-full flex-none gap-x-2">
          <dt class="flex-none">
            <span class="sr-only">Mail</span>
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
              <path d="M480-269 314-169q-11 7-23 6t-21-8q-9-7-14-17.5t-2-23.5l44-189-147-127q-10-9-12.5-20.5T140-571q4-11 12-18t22-9l194-17 75-178q5-12 15.5-18t21.5-6q11 0 21.5 6t15.5 18l75 178 194 17q14 2 22 9t12 18q4 11 1.5 22.5T809-528L662-401l44 189q3 13-2 23.5T690-171q-9 7-21 8t-23-6L480-269Z" />
            </svg>
          </dt>
          <dd class="text-sm leading-6 ">{{ $this->route->gradeFormated() }}</dd>
        </div>
        <div class="text-gray-500 mt-5 flex w-full flex-none gap-x-2">
          <dt class="flex-none">
            <span class="sr-only">Mail</span>
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
              <path d="M856-390 570-104q-12 12-27 18t-30 6q-15 0-30-6t-27-18L103-457q-11-11-17-25.5T80-513v-287q0-33 23.5-56.5T160-880h287q16 0 31 6.5t26 17.5l352 353q12 12 17.5 27t5.5 30q0 15-5.5 29.5T856-390ZM260-640q25 0 42.5-17.5T320-700q0-25-17.5-42.5T260-760q-25 0-42.5 17.5T200-700q0 25 17.5 42.5T260-640Z" />
            </svg>
          </dt>
          <dd class="text-sm leading-6 flex gap-x-1"> @foreach ($this->route->tags as $tag) <span class=" mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
              <svg class="h-1.5 w-1.5 fill-gray-500" viewBox="0 0 6 6" aria-hidden="true">
                <circle cx="3" cy="3" r="3"></circle>
              </svg>
              {{ $tag->name }}
            </span> @endforeach </dd>
        </div>
      </div>
      <div class="mt-12" x-data="{ activeTab:  0 }">
        <h1 class="text-2xl font-semibold leading-6 text-gray-900">{{__('Activity')}}</h1>
        <div class="">
          <div class="border-b border-gray-200">
            <nav class="-mb-px flex justify-between" aria-label="Tabs">
              <a @click="activeTab = 0" :class="activeTab == 0 ? 'border-gray-800 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-200 hover:text-gray-700 cursor-pointer'" class="flex whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                {{ __('Comments') }}
                <span :class="activeTab == 0 ? 'bg-gray-100 text-gray-600' : 'bg-gray-100 text-gray-900'" class="ml-3 hidden rounded-full py-0.5 px-2.5 text-xs font-medium md:inline-block">{{$logs->where('comment', '!=', null)->count()}}</span>
              </a>
              <a @click="activeTab = 1" :class="activeTab == 1 ? 'border-gray-800 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-200 hover:text-gray-700 cursor-pointer'" class=" flex whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                {{ __('Ascents') }}
                <span :class="activeTab == 1 ? 'bg-gray-100 text-gray-600' : 'bg-gray-100 text-gray-900'" class=" ml-3 hidden rounded-full py-0.5 px-2.5 text-xs font-medium md:inline-block">{{$logs->count()}}</span>
              </a>
              <a @click="activeTab = 2" :class="activeTab == 2 ? 'border-gray-800 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-200 hover:text-gray-700 cursor-pointer'" class="flex whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium" aria-current="page">
                {{ __('Video') }}
                <span :class="activeTab == 2 ? 'bg-gray-100 text-gray-600' : 'bg-gray-100 text-gray-900'" class="ml-3 hidden rounded-full py-0.5 px-2.5 text-xs font-medium md:inline-block">{{$logs->where('video_url', '!=', null)->count()}}</span>
              </a>
            </nav>
          </div>
        </div>
        <div x-show="activeTab == 0" class='min-h-56'> 
          @forelse ($logs->where('comment','!=', null) as $log) <div class=" mt-2 flex  items-start space-x-3">
            <div>
              <div class=" px-1">
                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-gray-100 ring-8 ring-white">
                  <img class="rounded-md" src="{{ $log->user->profile_photo_url }}" />
                </div>
              </div>
            </div>
            <div class="min-w-0 flex-1 py-0">
              <div class="text-sm leading-6 text-gray-500">
                <span class="">
                  <a href="#" class="font-medium text-gray-900">{{ $log->user->name }}</a>
                  <span class="whitespace-nowrap">{{ $log->created_at->format('d/m/Y') }}</span>
                  </br>
                </span>
                <span class="">
                  {{ $log->comment }}
                </span>
              </div>
            </div>
          </div> 
          @empty
          <div class="text-center rounded-lg border-2 border-dashed border-gray-300 mt-2">
  <x-icons.icon-comments/>
  <h3 class="mt-2 text-sm font-semibold text-gray-900">{{ __('No comments') }}</h3>
  <p class="mt-1 text-sm text-gray-500"> {{ __('No comments for this route. If you manage to climb it, you can be the first to comment !') }}</p>
  <div class="mt-6">
   <x-button @click="$dispatch('show_modal')" class='mb-2'>
      <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" fill="currentColor">
      <path d="m382-354 339-339q12-12 28-12t28 12q12 12 12 28.5T777-636L410-268q-12 12-28 12t-28-12L182-440q-12-12-11.5-28.5T183-497q12-12 28.5-12t28.5 12l142 143Z" />
    </svg>
      {{ __('Register your ascent !') }}
    </x-button>
  </div>
</div>         
          @endforelse
        </div>
        <div x-show="activeTab == 1" class='min-h-56'> 
          @forelse ($logs as $log) 
          <div class=" mt-2 flex items-center items-start space-x-3">
            <div>
              <div class=" px-1">
                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-gray-100 ring-8 ring-white">
                  <img class="rounded-md" src="{{ $log->user->profile_photo_url }}" />
                </div>
              </div>
            </div>
            <div class="min-w-0 flex-1 py-0">
              <div class="text-sm leading-6 text-gray-500">
                <span class="">
                  <a href="#" class="font-medium text-gray-900">{{ $log->user->name }}</a>
                  <span class="whitespace-nowrap">{{ $log->created_at->format('d/m/Y') }}</span>
                  </br>
                </span>
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
              </div>
            </div>
          </div> 
          @empty
          <div class="text-center rounded-lg border-2 border-dashed border-gray-300 mt-2">
            
  <x-icons.icon-carabiner/>
  <h3 class="mt-2 text-sm font-semibold text-gray-900">{{ __('No acsents') }}</h3>
  <p class="mt-1 text-sm text-gray-500"> {{ __('No ascents for this route. Maybe you are the fisrt to succes !') }}</p>
  <div class="mt-6">
    <x-button @click="$dispatch('show_modal')" class='mb-2'>
      <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" fill="currentColor">
      <path d="m382-354 339-339q12-12 28-12t28 12q12 12 12 28.5T777-636L410-268q-12 12-28 12t-28-12L182-440q-12-12-11.5-28.5T183-497q12-12 28.5-12t28.5 12l142 143Z" />
    </svg>
      {{ __('Register your ascent !') }}
    </x-button>
  </div>
</div>   
          @endforelse
        </div>
        {{--  
        <div x-show="activeTab == 2" class='min-h-56'> {{ __('Videos') }} 

        </div>
        --}}
      </div>
    </div>
    <livewire:routes.logger :route='$this->route'/>
  </div>