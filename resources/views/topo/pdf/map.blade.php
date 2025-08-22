<x-pdf-layout>
<div class="pt-12 mx-3">
    <div class='justify-center flex'>
      @if($type == 'schema')

        <div class="relative w-full h-full min-h-96">
                <div class="w-full h-96 z-0 flex items-center justify-center">
                  <img class="object-contain h-96" src="{{ Storage::url('plans/site-'.$site->id.'/area-'.$area->id.'/sector-'.$sector->id.'/schema') }}" />
                </div>
                <div class="absolute inset-0 flex justify-center items-center z-10"> {!! Storage::get('paths/site-'.$site->id.'/area-'.$area->id.'/sector-'.$sector->id.'/edited/topo_export.svg') !!} </div>
        </div> 

        @else
        <div>
            <img src='{{ Storage::url('plans/site-'.$site->id.'/area-'.$area->id.'/topo_export_'.$type.'.svg') }}'/>
        </div>
        @endif
    </div>
    <div>
        <div class="relative mt-2 -bottom-px col-span-full col-start-1 row-start-2 h-px bg-gray-200">
        </div>
        <div class="flex justify-between">
            <div class='flex'>
                <img class="h-24 mr-2 rounded-md object-cover" src="{{ $site->profile_picture() }}" alt="Admin">
                <div class=' border-x px-2 pt-2'>
                            <h1 class="text-4xl text-uppercase">{{$area->name}}</h1>
                            <h2 class="text-2xl text-uppercase">{{$site->name}}</h2>
                </div>
                <div class='border-r'>
                    @if(!empty($site->phone))
      <div class="py-2 flex w-full flex-none gap-x-4 px-2 font-mono border-b">
        <dt class="flex-none">
          <span class="sr-only">Phone</span>
          <svg class="h-6 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentcolor" ><path d="M798-120q-125 0-247-54.5T329-329Q229-429 174.5-551T120-798q0-18 12-30t30-12h162q14 0 25 9.5t13 22.5l26 140q2 16-1 27t-11 19l-97 98q20 37 47.5 71.5T387-386q31 31 65 57.5t72 48.5l94-94q9-9 23.5-13.5T670-390l138 28q14 4 23 14.5t9 23.5v162q0 18-12 30t-30 12Z"/>
        </svg>
        </dt>
        <dd class="text-sm leading-6 text-gray-500">{{ $site->phone }}</dd>
      </div>
@endif
@if(!empty($site->email))
      <div class="py-2 flex w-full flex-none gap-x-4 px-2 font-mono border-b">
        <dt class="flex-none">
          <span class="sr-only">Mail</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-5 text-gray-400" viewBox="0 -960 960 960" fill="currentcolor" ><path d="M160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h640q33 0 56.5 23.5T880-720v480q0 33-23.5 56.5T800-160H160Zm320-280 320-200v-80L480-520 160-720v80l320 200Z"/>
        </svg>
        </dt>
        <dd class="text-sm leading-6 text-gray-500">{{ $site->email }}</dd>
      </div>
      @endif
                </div>
            </div>
            <div>
 <img class="h-24 mr-2 rounded-md object-cover" src="{{Storage::url('qrcode/site-'.$site->id.'/qrcode.svg')}}"/>
            </div>
        </div>
        <div class="h-10 -top-px  border-b border-t border-t-(--pattern-fg) bg-[image:repeating-linear-gradient(-45deg,_var(--pattern-fg)_0,_var(--pattern-fg)_1px,_transparent_0,_transparent_50%)] bg-[size:10px_10px] bg-fixed">
        </div>
    </div>
    @if($type !== 'schema')
    <div class=' print:hidden flex justify-end'>
      <a class='mr-2 mt-2' wire:navigate href='{{ route("admin.areas.topo.result.routes.$type", ['site'=>$area->site, 'area'=> $area]) }}'  > <x-button type="button">{{ __('Next') }}</x-button></a>
    </div>
    @else
    <div class=' print:hidden flex justify-end'>
    <a class='mr-2 mt-2' wire:navigate href='{{route('admin.sectors.manage', ['site' => $site->id, 'area' => $area->id])}}'  > <x-button type="button">{{ __('Finish') }}</x-button></a>
  </div>
    @endif
</x-pdf-layout>