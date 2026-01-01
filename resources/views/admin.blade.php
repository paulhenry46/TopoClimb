<x-app-layout>
    <livewire:toast/>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Administration Area') }}
        </h2>
    </x-slot>
    <div class="py-12  mx-auto " >
      <x-grid-pattern-layout >
            <nav class="flex ml-4 my-4" aria-label="Breadcrumb">
                <ol role="list" class="flex items-center space-x-4">
                  <li>
                    <div>
                      <a href="#" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                          <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z" clip-rule="evenodd" />
                        </svg>
                        <span class="sr-only">{{__('Dashboard')}}</span>
                      </a>
                    </div>
                  </li>
                  
                    <div class="flex items-center">
                      <svg class="h-5 w-5 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                      </svg>
                      <a class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700" aria-current="page">{{__('Sites')}}</a>
                    </div>
                  </li>
                </ol>
            </nav>
            <div class="relative">
              <x-grid-pattern-title >
                {{ __('Admin manager') }}
              </x-grid-pattern-title >
              <x-grid-pattern-item >
               <livewire:sites.manager/>
              </x-grid-pattern-item >

            @if(auth()->user()->hr() <= 1)
            <x-grid-pattern-title >
              {{ __('Users') }}
            </x-grid-pattern-title >

            <x-grid-pattern-item >
            <div class="px-4 sm:px-6 lg:px-8 py-8">
              <div class="sm:flex sm:items-center">
                  <div class="sm:flex-auto">
                      <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Users')}}</h1>
                      <p class="mt-2 text-sm text-gray-700">{{__('Manage resgistered users in all sites')}}</p>
                  </div>
                  <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                      <x-button href="{{ route('admin.users') }}" wire:navigate>{{ __('Manage users') }}</x-button>
                  </div>
              </div>
            </div>
          </x-grid-pattern-item >
          @endif
          @if(auth()->user()->hr() <= 0)
          <x-grid-pattern-title >
                {{ __('Achievements') }}
            </x-grid-pattern-title >
          <x-grid-pattern-item >
            <livewire:admin.achievements />
          </x-grid-pattern-item >
          
          <x-grid-pattern-title >
                {{ __('Others') }}
            </x-grid-pattern-title >
          <div class='overflow-hidden  mb-4' >
            <div class='grid grid-cols-2 space-x-4'>
            <livewire:settings.backup />
            <livewire:settings.logo />
            </div>
          </div>
          <x-grid-pattern-title >
                {{ __('Mails') }}
            </x-grid-pattern-title >
          <x-grid-pattern-item >
            <livewire:settings.mails />
          </x-grid-pattern-item >
          @endif
          </div>
        </x-grid-pattern-layout >
    </div>
</x-app-layout>