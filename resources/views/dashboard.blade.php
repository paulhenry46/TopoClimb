<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class='grid grid-cols-6 gap-x-6 gap-y-6'>

            <div class="bg-white overflow-hidden  sm:rounded-lg col-span-2 min-h-32">
                <div class='px-4 py-4'>
                <h2 class="ml-4 mt-3 text-xl font-semibold text-gray-900">
                   {{ __('Profile') }}
                </h2>
                <livewire:dashboard.profile/>
            </div>
            </div>
            <div class="bg-white overflow-hidden  sm:rounded-lg col-span-4">
                <div class='px-4 py-4'>
                    stats
                </div>
               </div>
               <div class="bg-white overflow-hidden  sm:rounded-lg col-span-3 min-h-32">
                <div class='px-4 py-4'>
                    <h2 class="ml-4 mt-3 text-xl font-semibold text-gray-900">
                       {{ __('Graph') }}
                    </h2>
                    <livewire:dashboard.stats/>
                </div>
               </div>
               <div class="bg-white overflow-hidden  sm:rounded-lg col-span-3">
                Routes
               </div>
        </div>

        </div>
    </div>
</x-app-layout>
