<x-app-layout>
  <livewire:toast />
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Administration Area') }}
    </h2>
  </x-slot>
  <div class="px-4  mx-auto max-w-7xl">
    <div class=" ">
      <div class="col-start-3 row-start-3 ">
        <div class="relative">
          <x-area.banner :area='$area' />

          <livewire:areas.view :area='$area'/>
         
        </div>
      </div>
    </div>
  </div>
</x-app-layout>