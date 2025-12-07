<x-app-layout>
    <livewire:toast/>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Authorized Users') }} - {{ $contest->name }}
        </h2>
    </x-slot>
    <x-grid-pattern-layout>
        <livewire:contests.authorized-users :$contest/>
    </x-grid-pattern-layout>
</x-app-layout>
