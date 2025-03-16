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
          <x-site.banner :name='$site->name' :adress='$site->adress'/>
            <div>
              <div class="grid grid-cols-3 mt-8">
                <div class="col-span-2">
                </div>
                <div>
                  <x-site.infobox />
                  <x-site.map class="mt-3"/>
                </div>
              </div>
            
          </div>
        </div>
    </div>
  </div>
</x-app-layout>