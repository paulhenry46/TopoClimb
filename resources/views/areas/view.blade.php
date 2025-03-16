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
              <div class="grid grid-cols-3 mt-8 gap-4">
                <div class="col-span-2 flex flex-col">
                  <x-site.desc />
                </div>
                <div class="flex flex-col">
                  <x-site.infobox />
                </div>
              </div>
              <x-site.areas />
            
          </div>
        </div>
    </div>
  </div>
</x-app-layout>