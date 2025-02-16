<x-app-layout>
    <livewire:toast/>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Sectors') }}
        </h2>
    </x-slot>

   
                <livewire:sectors.manager :$area/>
            
</x-app-layout>