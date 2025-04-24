<x-app-layout>
  <livewire:toast />
  <div class="px-4  mx-auto max-w-7xl">
    <div class=" ">
      <div class="col-start-3 row-start-3 ">
        <div class="relative mt-2">
          <x-area.banner :area='$area'/>

          <livewire:areas.view :area='$area'/>
         
        </div>
      </div>
    </div>
  </div>
</x-app-layout>