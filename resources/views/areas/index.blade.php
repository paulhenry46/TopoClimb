<x-app-layout>
    <livewire:toast/>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Administration Area') }}
        </h2>
    </x-slot>

    <div class="py-12  mx-auto " >
      <div class="relative grid  grid-cols-[1fr_2.5rem_80rem_2.5rem_1fr] grid-rows-[1fr_1px_auto_1px_1fr]  ">
        <div class="col-start-3 row-start-3 " >
            <nav class="flex ml-4 my-4" aria-label="Breadcrumb">
                <ol role="list" class="flex items-center space-x-4">
                  <li>
                    <div>
                      <a href="#" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                          <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z" clip-rule="evenodd" />
                        </svg>
                        <span class="sr-only">{{__('Dashboard')}}</span>
                      </a>
                    </div>
                  </li>
                  <li>
                    <div class="flex items-center">
                      <svg class="h-5 w-5 flex-shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                      </svg>
                      <a wire:navigate href="{{route('admin.sites.manage')}}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">{{__('Sites')}}</a>
                    </div>
                  </li>
                  <li>
                    <div class="flex items-center">
                      <svg class="h-5 w-5 flex-shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                      </svg>
                      <a class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700" aria-current="page">{{__('Areas')}}</a>
                    </div>
                  </li>
                </ol>
            </nav>
            <div class="relative">
              <div class="absolute">
                <p class="font-semibold text-right origin-bottom-right -ml-96 pb-10 -mt-12 -rotate-90 text-gray-600 font-mono text-[0.8125rem]/6 font-medium tracking-widest text-pretty uppercase ">{{ __('Stats') }}</p>
                
                </div>
                <div class=" bg-white overflow-hidden  sm:rounded-lg mb-4">
              
                  <livewire:areas.manager :$site/>
               </div>
               <div class="absolute">
                <p class="font-semibold text-right origin-bottom-right -ml-96 pb-10 -mt-12 -rotate-90 text-gray-600 font-mono text-[0.8125rem]/6 font-medium tracking-widest text-pretty uppercase ">{{ __('Areas') }}</p>
                
                </div>
            <div class=" bg-white overflow-hidden  sm:rounded-lg">
              
               <livewire:areas.manager :$site/>
            </div>
          </div>
        </div>
        <div class="relative -right-px col-start-2 row-span-full row-start-1 border-x border-x-(--pattern-fg) bg-[image:repeating-linear-gradient(315deg,_var(--pattern-fg)_0,_var(--pattern-fg)_1px,_transparent_0,_transparent_50%)] bg-[size:10px_10px] bg-fixed"></div>
  <div class="relative -left-px col-start-4 row-span-full row-start-1 border-x border-x-(--pattern-fg) bg-[image:repeating-linear-gradient(315deg,_var(--pattern-fg)_0,_var(--pattern-fg)_1px,_transparent_0,_transparent_50%)] bg-[size:10px_10px] bg-fixed"></div>
  <div class="relative -bottom-px col-span-full col-start-1 row-start-2 h-px bg-gray-200"></div>
  <div class="relative -top-px col-span-full col-start-1 row-start-4 h-px bg-gray-200"></div>
    </div>
  </div>
</x-app-layout>