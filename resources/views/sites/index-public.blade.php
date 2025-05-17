<x-app-layout> 
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('All sites') }}
        </h2>
    </x-slot>
    <x-grid-pattern-layout class='min-h-screen'>
        @auth
        <p class='p-2 font-mono'>{{ __('Favorited sites') }} </p>
            <div class='grid sm:grid-cols-3 grid-cols-1 gap-2 p-2' >
    @forelse (auth()->user()->favoriteSites()->get() as $site)
    <div class="relative">
        <div>
            <div class=" flex items-center p-0 overflow-hidden bg-center bg-cover h-96  rounded-2xl" style="background-image: url('{{ $site->banner() }}'); background-position-y: 50%; filter: opacity(37.9%) grayscale(100%);">
            </div>
        </div>
        <div class="rounded-2xl bg-center bg-cover *bg-gradient-to-tl *from-gray-600 *to-gray-400  z-10  h-96  -mt-96" style="background-image: linear-gradient(to left top, rgba(75, 85, 99, 0.6), rgba(156, 163, 175, 0.6));">
        </div>
        <div class=" rounded-b-2xl min z-40 min-h-24 -mt-30 w-full bg-gray-900/50 " style="opacity: 0.999999;">
            <div class=" py-3 px-3 flex mb-2">
                <img class=" size-24 rounded-md object-contain" src="{{ $site->profile_picture() }}" alt="Site picture">
                <div class="ml-3 grid grid-rows-2">
                    <div class="truncate font-semibold text-white content-center text-3xl">{{ $site->name }}</div>
                    <div class=" text-white content-top">{{ $site->state }}</div>
                </div>
            </div>
        </div>          
    </div>
    @empty
        nothing to show
    @endforelse
</div>
        @endauth
       <p class='p-2 font-mono'>{{ __('All sites') }} </p>
    <div class='grid sm:grid-cols-3 grid-cols-1 gap-2 p-2' >
    @forelse ($sites as $site)
    <div class="relative">
        <div>
            <div class=" flex items-center p-0 overflow-hidden bg-center bg-cover h-96  rounded-2xl" style="background-image: url('{{ $site->banner() }}'); background-position-y: 50%; filter: opacity(37.9%) grayscale(100%);">
            </div>
        </div>
        <div class="rounded-2xl bg-center bg-cover *bg-gradient-to-tl *from-gray-600 *to-gray-400  z-10  h-96  -mt-96" style="background-image: linear-gradient(to left top, rgba(75, 85, 99, 0.6), rgba(156, 163, 175, 0.6));">
        </div>
        <div class=" rounded-b-2xl min z-40 min-h-24 -mt-30 w-full bg-gray-900/50 " style="opacity: 0.999999;">
            <div class=" py-3 px-3 flex mb-2">
                <img class=" size-24 rounded-md object-contain" src="{{ $site->profile_picture() }}" alt="Site picture">
                <div class="ml-3 grid grid-rows-2">
                    <div class="truncate font-semibold text-white content-center text-3xl">{{ $site->name }}</div>
                    <div class=" text-white content-top">{{ $site->state }}</div>
                </div>
            </div>
        </div>          
    </div>
    @empty
        nothing to show
    @endforelse
</div>
</x-grid-pattern-layout >

  </x-app-layout>