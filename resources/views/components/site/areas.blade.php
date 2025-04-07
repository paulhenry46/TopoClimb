<div class="bg-white rounded-md mt-4 py-2">
    <div class="py-5 ml-5 font-semibold text-xl text-gray-700">{{ __('Areas') }}</div>
    <div class="px-5">
      @foreach($site->areas as $area)
      <div class=" w-full  border-gray-900 rounded-md border-2 mb-4 flex justify-between">
        <div class="ml-4 mt-4 mb-4">{{ $area->name}} 
        </div>
        <a wire:navigate href="{{ route('area.view', [$site->slug, $area->slug]) }}" class="cursor-pointer w-32  bg-gray-900 text-white hover:bg-gray-700" ><p class="flex ml-4 mt-4 mb-4 font-semibold">{{ __('See topo') }} <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M504-480 320-664l56-56 240 240-240 240-56-56 184-184Z"/></svg></p></a>
      </div>
      @endforeach
  </div>
</div>