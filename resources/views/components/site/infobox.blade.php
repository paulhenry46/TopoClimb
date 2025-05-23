@props(['site'])
<div class="bg-white rounded-md mt-3 h-full">
    <div class="py-6 ml-6 font-semibold text-xl text-gray-700">{{ __('Contact') }}</div>
    @if(!empty($site->website))
    <div class="mt-2 flex w-full flex-none gap-x-4  px-6">
        <dt class="flex-none">
          <span class="sr-only">Website</span>
          <svg class="h-6 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentcolor"><path d="M480-80q-82 0-155-31.5t-127.5-86Q143-252 111.5-325T80-480q0-83 31.5-155.5t86-127Q252-817 325-848.5T480-880q83 0 155.5 31.5t127 86q54.5 54.5 86 127T880-480q0 82-31.5 155t-86 127.5q-54.5 54.5-127 86T480-80Zm0-82q26-36 45-75t31-83H404q12 44 31 83t45 75Zm-104-16q-18-33-31.5-68.5T322-320H204q29 50 72.5 87t99.5 55Zm208 0q56-18 99.5-55t72.5-87H638q-9 38-22.5 73.5T584-178ZM170-400h136q-3-20-4.5-39.5T300-480q0-21 1.5-40.5T306-560H170q-5 20-7.5 39.5T160-480q0 21 2.5 40.5T170-400Zm216 0h188q3-20 4.5-39.5T580-480q0-21-1.5-40.5T574-560H386q-3 20-4.5 39.5T380-480q0 21 1.5 40.5T386-400Zm268 0h136q5-20 7.5-39.5T800-480q0-21-2.5-40.5T790-560H654q3 20 4.5 39.5T660-480q0 21-1.5 40.5T654-400Zm-16-240h118q-29-50-72.5-87T584-782q18 33 31.5 68.5T638-640Zm-234 0h152q-12-44-31-83t-45-75q-26 36-45 75t-31 83Zm-200 0h118q9-38 22.5-73.5T376-782q-56 18-99.5 55T204-640Z"/>
        </svg>
        </dt>
        <a href='{{ $site->website }}'  class="text-sm font-medium leading-6  text-gray-500">{{ $site->website }}</a>
      </div>
@endif
@if(!empty($site->phone))
      <div class="mt-4 flex w-full flex-none gap-x-4 px-6">
        <dt class="flex-none">
          <span class="sr-only">Phone</span>
          <svg class="h-6 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentcolor" ><path d="M798-120q-125 0-247-54.5T329-329Q229-429 174.5-551T120-798q0-18 12-30t30-12h162q14 0 25 9.5t13 22.5l26 140q2 16-1 27t-11 19l-97 98q20 37 47.5 71.5T387-386q31 31 65 57.5t72 48.5l94-94q9-9 23.5-13.5T670-390l138 28q14 4 23 14.5t9 23.5v162q0 18-12 30t-30 12Z"/>
        </svg>
        </dt>
        <a href='tel:{{ $site->phone }}'  class="text-sm leading-6 text-gray-500">{{ $site->phone }}</a>
      </div>
@endif
@if(!empty($site->email))
      <div class="mt-4 flex w-full flex-none gap-x-4 px-6">
        <dt class="flex-none">
          <span class="sr-only">Mail</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-5 text-gray-400" viewBox="0 -960 960 960" fill="currentcolor" ><path d="M160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h640q33 0 56.5 23.5T880-720v480q0 33-23.5 56.5T800-160H160Zm320-280 320-200v-80L480-520 160-720v80l320 200Z"/>
        </svg>
        </dt>
        <a href='mailto:{{ $site->email }}'  class="text-sm leading-6 text-gray-500">{{ $site->email }}</a>
      </div>
      @endif
      @if(!empty($site->coord))
      <div class="py-4 flex w-full flex-none gap-x-4 px-6">
        <dt class="flex-none">
          <span class="sr-only">Coord</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-5 text-gray-400" viewBox="0 -960 960 960" fill="currentcolor"><path d="M480-480q33 0 56.5-23.5T560-560q0-33-23.5-56.5T480-640q-33 0-56.5 23.5T400-560q0 33 23.5 56.5T480-480Zm0 400Q319-217 239.5-334.5T160-552q0-150 96.5-239T480-880q127 0 223.5 89T800-552q0 100-79.5 217.5T480-80Z"/>
        </svg>
        </dt>
        <dd class="text-sm leading-6 text-gray-500">{{ $site->coord }}</dd>
      </div>
      @endif
      @auth
      <div class="mt-3 inset-x-0 bottom-0 bg-gray-50 px-4 py-4 sm:px-6">
        <div class="text-sm">
          
      <livewire:sites.favorited :site='$site' />
        </div>
      </div>
      @endauth

</div>