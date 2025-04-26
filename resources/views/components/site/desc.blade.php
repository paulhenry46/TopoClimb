@props(['site'])
<div class="bg-white rounded-md mt-3 py-2 h-full">
    <div class="py-5 ml-5 font-semibold text-xl text-gray-700">{{ __('Description') }}</div>
    <div class="px-5">
    <div class=" w-full  border-gray-900 rounded-md border-2 mb-4">
      <div class="ml-4 mt-4 mb-4"> 
        @if(!empty($site->description))
        {!! nl2br($site->description) !!}
        @else
        {{ __('This site has not yet a description...') }}
        @endif
         </div>
    </div>
  </div>
</div>