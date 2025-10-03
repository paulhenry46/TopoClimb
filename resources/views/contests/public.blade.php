<x-app-layout>
    <livewire:toast/>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $contest->name }}
        </h2>
    </x-slot>

    <div class="">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-grid-pattern-layout>
                <livewire:contests.public-view :$contest/>
            </x-grid-pattern-layout>
        </div>
    </div>
</x-app-layout>
