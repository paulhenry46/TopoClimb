<x-app-layout>
  <livewire:toast />
  <div class="  mx-auto ">
    <x-grid-pattern-layout >
          <x-site.banner :site='$site' />
          <div class="grid md:grid-cols-3 mt-8 gap-4 grid-cols-1">
            <x-grid-pattern-title >{{ __('Infos') }}</x-grid-pattern-title >
            <div class="md:col-span-2 flex flex-col">
              <x-site.desc :site='$site'/>
            </div>
            <div class="flex flex-col">
              <x-site.infobox :site='$site'/>
            </div>
          </div>
          <x-grid-pattern-title >{{ __('Topo') }}</x-grid-pattern-title >
          <x-site.areas :site='$site' />
        </x-grid-pattern-layout >
  </div>
</x-app-layout>