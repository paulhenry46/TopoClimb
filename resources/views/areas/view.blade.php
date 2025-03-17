<x-app-layout>
  <livewire:toast />
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Administration Area') }}
    </h2>
  </x-slot>
  <div class="py-12  mx-auto ">
    <div class="relative grid  grid-cols-[1fr_2.5rem_80rem_2.5rem_1fr] grid-rows-[1fr_1px_auto_1px_1fr]  ">
      <div class="col-start-3 row-start-3 ">
        <div class="relative">
          <x-site.banner :site='$site' />
          <div class="grid grid-cols-3 mt-8 gap-4">
            <div class="absolute">
              <p class="font-semibold text-right origin-bottom-right -ml-96 pb-10 -mt-8 -rotate-90 text-gray-600 font-mono text-[0.8125rem]/6 font-medium tracking-widest text-pretty uppercase ">{{ __('Infos') }}</p>
              
              </div>
            <div class="col-span-2 flex flex-col">
              <x-site.desc :site='$site'/>
            </div>
            <div class="flex flex-col">
              <x-site.infobox :site='$site'/>
            </div>
          </div>
          <div class="absolute">
            <p class="font-semibold text-right origin-bottom-right -ml-96 pb-10 -mt-8 -rotate-90 text-gray-600 font-mono text-[0.8125rem]/6 font-medium tracking-widest text-pretty uppercase ">{{ __('Topo') }}</p>
            
            </div>
          <x-site.areas :site='$site' />
        </div>
      </div>
      <div class="relative -right-px col-start-2 row-span-full row-start-1 border-x border-x-(--pattern-fg) bg-[image:repeating-linear-gradient(315deg,_var(--pattern-fg)_0,_var(--pattern-fg)_1px,_transparent_0,_transparent_50%)] bg-[size:10px_10px] bg-fixed"></div>
      <div class="relative -left-px col-start-4 row-span-full row-start-1 border-x border-x-(--pattern-fg) bg-[image:repeating-linear-gradient(315deg,_var(--pattern-fg)_0,_var(--pattern-fg)_1px,_transparent_0,_transparent_50%)] bg-[size:10px_10px] bg-fixed"></div>
      <div class="relative -bottom-px col-span-full col-start-1 row-start-2 h-px bg-gray-200"></div>
      <div class="relative -top-px col-span-full col-start-1 row-start-4 h-px bg-gray-200"></div>
    </div>
  </div>
</x-app-layout>