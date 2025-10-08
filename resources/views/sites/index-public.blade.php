<x-app-layout> 
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('All sites') }}
        </h2>
    </x-slot>
    <x-grid-pattern-layout class='min-h-screen'>
        @auth
        @if(!auth()->user()->favoriteSites()->get()->isEmpty())
        <p class='sm:hidden p-2 font-mono'>{{ __('All sites') }} </p>
        <div class='sm:mt-8'>
        <x-grid-pattern-title>{{ __('Favorites') }}</x-grid-pattern-title>
        </div>
            <div class='grid sm:grid-cols-3 grid-cols-1 gap-2 p-2' >
    @foreach (auth()->user()->favoriteSites()->get() as $site)
    <a wire:navigate href='{{route('site.view', $site->slug) }}' class="relative">
        <div>
            <div class=" flex items-center p-0 overflow-hidden bg-center bg-cover h-96  rounded-2xl" style="background-image: url('{{ $site->banner() }}'); background-position-y: 50%; filter: opacity(37.9%) grayscale(100%);">
            </div>
        </div>
        <div class="rounded-2xl bg-center bg-cover *bg-gradient-to-tl *from-gray-600 *to-gray-400  z-10  h-96  -mt-96" style="background-image: linear-gradient(to left top, rgba(75, 85, 99, 0.6), rgba(156, 163, 175, 0.6));">
        </div>
         <div class=" rounded-b-2xl min z-40 min-h-24 h-30 -mt-30 w-full bg-gray-900/50 " style="opacity: 0.999999;">
            <div  class=" py-3 px-3 flex mb-2">
                <img class=" size-24 rounded-md object-contain" src="{{ $site->profile_picture() }}" alt="Site picture">
                <div class="ml-3 grid grid-rows-2">
                    <div class="font-semibold text-white content-center text-3xl whitespace-normal break-words">{{ $site->name }}</div>
                    <div class=" text-white content-top">{{ $site->state }}</div>
                </div>
            </div>
        </div>          
    </a>
    @endforeach
</div>
<div class="h-10 -top-px  border-b border-t border-t-(--pattern-fg) bg-[image:repeating-linear-gradient(-45deg,_var(--pattern-fg)_0,_var(--pattern-fg)_1px,_transparent_0,_transparent_50%)] bg-[size:10px_10px] bg-fixed"></div>
@endif
        @endauth
        <x-grid-pattern-title>{{ __('All sites') }}</x-grid-pattern-title>
       <p class='sm:hidden p-2 font-mono'>{{ __('All sites') }} </p>
    <div class='grid sm:grid-cols-3 grid-cols-1 gap-2 p-2' >
    @forelse ($sites as $site)
    <a wire:navigate href='{{route('site.view', $site->slug) }}' class="relative">
        <div>
            <div class=" flex items-center p-0 overflow-hidden bg-center bg-cover h-96  rounded-2xl" style="background-image: url('{{ $site->banner() }}'); background-position-y: 50%; filter: opacity(37.9%) grayscale(100%);">
            </div>
        </div>
        <div class="rounded-2xl bg-center bg-cover *bg-gradient-to-tl *from-gray-600 *to-gray-400  z-10  h-96  -mt-96" style="background-image: linear-gradient(to left top, rgba(75, 85, 99, 0.6), rgba(156, 163, 175, 0.6));">
        </div>
        <div class=" rounded-b-2xl min z-40 min-h-24 h-30 -mt-30 w-full bg-gray-900/50 " style="opacity: 0.999999;">
            <div  class=" py-3 px-3 flex mb-2">
                <img class=" size-24 rounded-md object-contain" src="{{ $site->profile_picture() }}" alt="Site picture">
                <div class="ml-3 grid grid-rows-2">
                     <div class="font-semibold text-white content-center text-3xl whitespace-normal break-words">{{ $site->name }}</div>
                    <div class=" text-white content-top">{{ $site->state }}</div>
                </div>
            </div>
        </div>          
    </a>
    @empty
        <div class="col-span-3 flex flex-col items-center justify-center py-24 text-gray-300">
        <svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 -960 960 960" width="48px" fill="currentColor"><path d="M440-520v80q0 17 11.5 28.5T480-400q17 0 28.5-11.5T520-440v-80h80q17 0 28.5-11.5T640-560q0-17-11.5-28.5T600-600h-80v-80q0-17-11.5-28.5T480-720q-17 0-28.5 11.5T440-680v80h-80q-17 0-28.5 11.5T320-560q0 17 11.5 28.5T360-520h80Zm40 413q-14 0-28-5t-25-15q-65-60-115-117t-83.5-110.5q-33.5-53.5-51-103T160-552q0-150 96.5-239T480-880q127 0 223.5 89T800-552q0 45-17.5 94.5t-51 103Q698-301 648-244T533-127q-11 10-25 15t-28 5Z"/></svg>
        <h3 class="text-2xl font-semibold text-gray-700 mb-2">{{ __('No sites yet') }}</h3>
        <p class="text-gray-500 mb-6 text-center max-w-md">{{ __("There are no climbing sites registered yet. When sites are added, they'll show up here!") }}</p>
        @auth
            <a wire:navigate href="{{ route('admin.sites.manage') }}" class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-md shadow hover:bg-gray-700 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('Add your first site') }}
            </a>
        @endauth
    </div>
    @endforelse
</div>
</x-grid-pattern-layout >

  </x-app-layout>