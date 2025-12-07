<x-app-layout>
    <livewire:toast/>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Grid Registration') }} - {{ $contest->name }}
        </h2>
    </x-slot>
    <x-grid-pattern-layout>
        <livewire:contests.grid-registration :$contest/>
    </x-grid-pattern-layout>
</x-app-layout>
