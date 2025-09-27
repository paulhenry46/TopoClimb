<x-pdf-layout>
  <div class=" mx-3">
    <div class="rounded-l-xl/ flex items-center mb-4">
        <div class="text-center w-16 relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
            <p class="font-mono text-pretty   uppercase text-sm"> 
                @if($type == 'lines')
                {{ __('Line') }}
                @elseif ($type == 'sectors')
                {{ __('Sector') }}
                @endif
            </p>
        </div>
        <div class=" w-full relative whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
            <div class='justify-between items-center w-full flex'>
                <div class='flex items-center'>
                    <div class=" text-center w-16 bg-gray-300 relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3"> {{__('Level')}} </div>
                    <div class="  whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                        <div class="flex items-center">
                            <div>
                                <div class="font-bold pb-1">{{__('Name')}}</div>
                                <div class="text-sm opacity-50">{{ __('Date') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class=" relative whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3"> <span class=" flex-none mr-2 inline-flex items-center gap-x-1.5 rounded-md  px-2 text-sm font-medium text-gray-700">
                        <div class=" bg-gray-300 h-8 w-8  rounded-md object-cover object-center"></div> {{ __('Opener name') }}
                    </span> </div>
                <div class=" relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3 w-52"> <span class=" mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">{{__('Tag')}}</span> </div>
                <div class=" justify-end whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-500 sm:pl-3 flex"> {{ __('QR code') }} </div>
            </div>
        </div>
    </div>
    @if($type == 'lines')
    <div> @foreach ($area->lines() as $line) <div class="my-1 relative -bottom-px col-span-full col-start-1 row-start-2 h-px bg-gray-700"></div>
        <div class="rounded-l-xl/  flex items-center mb-3">
            <div class="text-center w-16 relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                <p class="font-mono text-pretty   uppercase text-3xl"> {{ $line->local_id }}</p>
            </div>
            <div class=" w-full relative whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3"> @foreach ($area->routes()->where('line_id', $line->id) as $route) <div class=' my-3 justify-between items-center w-full flex'>
                    <div class='flex items-center'>
                        <div class="bg-{{$route->color}}-300 border-4 border-{{$route->color}}-300  rounded-l-md text-center h-16 w-16 relative whitespace-nowrap font-medium text-gray-900">
                            <div class='grayscale  rounded-l h-full w-full bg-cover' style="background-image: url({{ $route->thumbnail() }})"></div>
                        </div>
                        <div class=" text-2xl text-center w-16 bg-{{$route->color}}-300 relative whitespace-nowrap py-4 pl-4 pr-3 font-medium text-gray-900 sm:pl-3"> {{$route->defaultGradeFormated()}} </div>
                        <div class="  whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                            <div class="flex items-center">
                                <div>
                                    <div class="font-bold pb-1">{{$route->name}}</div>
                                    <div class="text-sm opacity-50">{{ $route->created_at->format('d/m/Y') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class=" relative whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3"> @forelse ( $route->users as $opener) <span class=" flex-none mr-2 inline-flex items-center gap-x-1.5 rounded-md  px-2 text-sm font-medium text-gray-700"> <img alt="{{ $opener->name }}" src="{{ $opener->profile_photo_url }}" class=" h-8 w-8  rounded-md object-cover object-center" /> {{ $opener->name }} </span> @empty @endforelse </div>
                    <div class=" relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3 w-52"> @forelse ($route->tags as $tag) <span class=" mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">{{$tag->name}}</span> @empty @endforelse </div>
                    <div class=" justify-end whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-500 sm:pl-3 flex"> <img class='h-16 object-contain' src="{{ $route->qrcode() }}"></img> </div>
                </div> @endforeach </div>
        </div> @endforeach
    </div>
@elseif($type == 'sectors')
    <div> @foreach ($area->sectors as $sector) <div class="my-1 relative -bottom-px col-span-full col-start-1 row-start-2 h-px bg-gray-700"></div>
        <div class="rounded-l-xl/  flex items-center mb-3">
            <div class="text-center w-16 relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                <p class="font-mono text-pretty   uppercase text-3xl"> {{ $sector->local_id }}</p>
            </div>
            <div class=" w-full relative whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3"> @foreach ($sector->routes() as $route) <div class=' my-3 justify-between items-center w-full flex'>
                    <div class='flex items-center'>
                        <div class="bg-{{$route->color}}-300 border-4 border-{{$route->color}}-300  rounded-l-md text-center h-16 w-16 relative whitespace-nowrap font-medium text-gray-900">
                            <div class='grayscale  rounded-l h-full w-full bg-cover' style="background-image: url({{ $route->thumbnail() }})"></div>
                        </div>
                        <div class=" text-2xl text-center w-16 bg-{{$route->color}}-300 relative whitespace-nowrap py-4 pl-4 pr-3 font-medium text-gray-900 sm:pl-3"> {{$route->defaultGradeFormated()}} </div>
                        <div class="  whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                            <div class="flex items-center">
                                <div>
                                    <div class="font-bold pb-1">{{$route->name}}</div>
                                    <div class="text-sm opacity-50">{{ $route->created_at->format('d/m/Y') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class=" relative whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3"> @forelse ( $route->users as $opener) <span class=" flex-none mr-2 inline-flex items-center gap-x-1.5 rounded-md  px-2 text-sm font-medium text-gray-700"> <img alt="{{ $opener->name }}" src="{{ $opener->profile_photo_url }}" class=" h-8 w-8  rounded-md object-cover object-center" /> {{ $opener->name }} </span> @empty @endforelse </div>
                    <div class=" relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3 w-52"> @forelse ($route->tags as $tag) <span class=" mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">{{$tag->name}}</span> @empty @endforelse </div>
                    <div class=" justify-end whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-500 sm:pl-3 flex"> <img class='h-16 object-contain' src="{{ $route->qrcode() }}"></img> </div>
                </div> @endforeach </div>
        </div> @endforeach
    </div>
    @endif

</div>
<div class=' print:hidden flex justify-end'>
    <a class='mr-2 mt-2' wire:navigate href='{{route('admin.sectors.manage', ['site' => $site->id, 'area' => $area->id])}}'  > <x-button type="button">{{ __('Finish') }}</x-button></a>
  </div>
</x-pdf-layout>