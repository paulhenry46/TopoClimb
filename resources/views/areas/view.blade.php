<x-app-layout>
    <livewire:toast/>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Administration Area') }}
        </h2>
    </x-slot>

    <div class="py-12  mx-auto max-w-7xl" >
      <div class=" ">
        <div>
          <x-site.banner />
            <div>
                
            
          </div>
        </div>
    </div>
  </div>
</x-app-layout>