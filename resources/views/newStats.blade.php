<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Training Statistics') }}
        </h2>
    </x-slot>
<x-grid-pattern-layout >
     <x-grid-pattern-title >
                {{ __('Your Climbing Analytics') }}
              </x-grid-pattern-title >
    <x-grid-pattern-item >
    <div class="">
        <livewire:stats.immersive-stats/>
    </div>
</x-grid-pattern-item >
</x-grid-pattern-layout>
</x-app-layout>
