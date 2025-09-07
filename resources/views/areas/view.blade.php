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
          <div class="grid grid-cols-3 md:mt-8 gap-4 md:pt-2">
          <livewire:areas.view :area='$area'/>

           @auth
          @can('can:routes.'.$area->site->id)
           <livewire:routes.view-opener :site='$site' :area='$area' />
         @else
            <livewire:routes.view :area='$area' />
         @endcan
    @else
    <livewire:routes.view :area='$area' />
    @endauth


        </div>
      </div>
    </div>
  </div>
</x-app-layout>