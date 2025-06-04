<x-app-layout>
  <livewire:toast />
  <div class="md:px-4  mx-auto max-w-7xl">
    <div class=" ">
      <div class="col-start-3 row-start-3 ">
        <div class="relative mt-2">
          <x-area.banner :area='$area'/>
          <div class='-mt-2'>
          <x-grid-pattern-hr/>
          </div>

          <livewire:areas.view :area='$area'/>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>