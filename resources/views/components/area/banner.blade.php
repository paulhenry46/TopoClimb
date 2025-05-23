@props(['area'])

<div>
<div class=" flex items-center p-0 overflow-hidden bg-center bg-cover sm:h-96 h-68 rounded-2xl" style="background-image: url('{{ $area->banner() }}'); background-position-y: 50%; filter: opacity(37.9%) grayscale(100%);">

</div>
</div>

<div class="rounded-2xl bg-center bg-cover *bg-gradient-to-tl *from-gray-600 *to-gray-400  z-10 h-68 sm:h-96 -mt-68 sm:-mt-96" style="
    background-image: linear-gradient(to left top, rgba(75, 85, 99, 0.6), rgba(156, 163, 175, 0.6));">
</div>

<div class="hidden sm:block rounded-md ml-10 min z-40 min-h-24 -mt-36 w-fit bg-gray-900/50 " style="opacity: 0.999999;">
    <div class=" py-3 px-3 flex mb-2">
      <img class="size-24 rounded-md object-contain" src="{{ $area->site->profile_picture() }}" alt="Admin">
      <div class="ml-3 grid grid-rows-2">
        <div class="font-semibold text-white content-center text-3xl">{{$area->name }}</div>
        <a wire:navigate href='{{route('site.view', $area->site->slug) }}'  class=" text-white content-top">{{ $area->site->name }}</a>
      </div>
    </div>
</div>

<div class="sm:hidden rounded-b-2xl min z-40 min-h-24 -mt-30 w-full bg-gray-900/50 " style="opacity: 0.999999;">
  <div class=" py-3 px-3 flex mb-2">
    <img class=" size-24 rounded-md object-contain" src="{{ $area->site->profile_picture() }}" alt="Admin">
    <div class="ml-3 grid grid-rows-2">
      <div class="truncate font-semibold text-white content-center text-3xl">{{$area->name }}</div>
      <div class=" text-white content-top">{{ $area->site->name }}</div>
    </div>
  </div>
</div>