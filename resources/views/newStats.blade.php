<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Immersive Statistics') }}
        </h2>
    </x-slot>

    <div class="bg-[#1E1E1E]">
        <livewire:stats.immersive-stats/>
    </div>
</x-app-layout>
