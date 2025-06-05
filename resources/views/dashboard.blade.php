<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class='grid md:grid-cols-6 gap-x-6 gap-y-6'>

            <div class="bg-white overflow-hidden  sm:rounded-lg md:col-span-2 min-h-32">
                <div class='px-4 py-4'>
                <h2 class="ml-4 text-xl font-semibold text-gray-900 mt-1">
                   {{ __('Profile') }}
                </h2>
                <livewire:dashboard.profile/>
            </div>
            </div>
            <div class="md:col-span-4">
                <div class='h-full'>
                    <div class="bg-white overflow-hidden  sm:rounded-lg col-span-2">
                        <h2 class="px-4 py-4 text-xl font-semibold text-gray-900">
                            {{ __('Stats') }}
                         </h2>
                    </div>
                    <livewire:dashboard.stats class='h-full'/>
                </div>
               </div>
               <div class="bg-white overflow-hidden  sm:rounded-lg md:col-span-3 min-h-32">
                <div class='px-4 py-4'>
                    <h2 class="ml-4 mt-3 text-xl font-semibold text-gray-900">
                       {{ __('Distribution of climbed routes') }}
                    </h2>
                    <livewire:dashboard.graph/>
                </div>
               </div>
               
                    <livewire:dashboard.routes/>
                
        </div>

        </div>
    </div>
</x-app-layout>
