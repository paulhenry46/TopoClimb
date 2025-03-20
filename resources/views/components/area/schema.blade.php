@props(['area', 'schema_url'])
<div class="bg-white rounded-md mt-3 py-2 h-full">
    <div class="py-5 ml-5 font-semibold text-xl text-gray-700">{{ __('Image') }}</div>
    <div class="px-5">
    <div class=" w-full  border-gray-900 rounded-md border-2 mb-4">
      <div class="ml-4 mt-4 mb-4"> 
        @if($area->type == 'bloc')
        <div class="bg-white overflow-hidden /*shadow-xl*/ sm:rounded-lg">
            <div class="px-4 sm:px-6 lg:px-8 py-8">
              <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto stroke-indigo-500">
                  <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Map')}}</h1>
                  <p class="mt-2 text-sm text-gray-700">{{__('Map of the area with sectors and lines')}}</p>
                  <div class=" w-full rounded-xl object-contain pt-4"> {!!$schema_url!!} </div>
                </div>
              </div>
            </div>
          </div>
        @else

        @endif
         </div>
    </div>
  </div>
</div>