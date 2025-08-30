@props([
    'open',
    'title' => '',
    'subtitle' => '',
    'save_method_name',
])
<div x-data="{ open: $wire.entangle('{{$open}}') }">
  <div class="relative z-10 overflow-y-auto" aria-labelledby="drawer-title" role="dialog" aria-modal="true" x-show="open" x-cloak x-trap.noscroll="open">
    <div class="fixed inset-0 overflow-hidden">
      <div class="absolute inset-0 overflow-hidden">
        <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
          <div class="pointer-events-auto w-screen max-w-2xl"
               x-show="open"
               x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
               x-transition:enter-start="translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="translate-x-full">
            <form x-on:keydown.prevent.enter="" wire:submit="{{$save_method_name}}" class="flex h-full flex-col bg-white shadow-xl">
              <div class="flex-1 overflow-y-auto">
                {{-- Header --}}
                <div class="bg-gray-50 px-4 py-6 sm:px-6">
                  <div class="flex items-start justify-between space-x-3">
                    <div class="space-y-1">
                      <h2 class="text-base font-semibold leading-6 text-gray-900" id="drawer-title">
                        {{ $title ?? '' }}
                      </h2>
                      <p class="text-sm text-gray-500">{{ $subtitle ?? '' }}</p>
                    </div>
                    <div class="flex h-7 items-center">
                      <button x-on:click="open = false" type="button" class="cursor-pointer relative text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
                {{-- Content --}}
                <div class="overflow-y-auto py-6 px-4 sm:px-6">
                  {{ $slot }}
                </div>
                @isset($footer)
                <div class="shrink-0 border-t border-gray-200 px-4 py-5 sm:px-6">
                  {{ $footer }}
                </div>
                @endisset
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>