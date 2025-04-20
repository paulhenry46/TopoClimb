<x-pdf-layout>
    <div class="py-12 mx-3">

<div class="rounded-l-xl/ border-2 border-solid flex items-center mb-4">
    <div class="text-center w-16 relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
       <p class="font-mono text-pretty   uppercase text-sm"> {{ __('Line') }}</p>
    </div>
    <div class=" w-full relative whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3"> 
          <div class='justify-between items-center w-full flex'
               
              >
                <div class='flex items-center'>
              <div class=" text-center w-16 bg-gray-300 relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                  {{__('Level')}}
                </div>
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
                <div class=" bg-gray-300 h-8 w-8  rounded-md object-cover object-center" ></div>
                {{ __('Opener name') }}
              </span> </div>

                  <div class=" relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3 w-52">
                    <span class=" mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">{{__('Tag')}}</span>
                </div>


                <div class=" justify-end whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-500 sm:pl-3 flex">
                  {{ __('QR code') }}
                </div>
            </div> 
    </div>
</div>

        <div>
            @foreach ($area->lines() as $line) 

                    <div class="rounded-l-xl/ border-2 border-solid flex items-center mb-3">
                    <div class="text-center w-16 relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                       <p class="font-mono text-pretty   uppercase text-3xl"> {{ $line->local_id }}</p>
                    </div>
                    <div class=" w-full relative whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3"> 
                         @foreach ($area->routes()->where('line_id', $line->id) as $route) <div class=' @if($loop->first and $loop->last) mt-0 @elseif ($loop->first)mb-3 @elseif($loop->last) mt-3 @else my-3 @endif justify-between items-center w-full flex'
                               
                              >
                                <div class='flex items-center'>
                              <div class=" @if($loop->first and $loop->last) rounded-l-md @elseif ($loop->first)rounded-bl-md @elseif($loop->last) rounded-tl-md @else rounded-l-md @endif text-xl text-center w-16 bg-{{$route->color}}-300 relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                                  {{$route->gradeFormated()}}
                                </div>
                                <div class="  whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                                  <div class="flex items-center">
                                    <div>
                                      <div class="font-bold pb-1">{{$route->name}}</div> 
                                      <div class="text-sm opacity-50">{{ $route->created_at->format('d/m/Y') }}</div>
                                    </div>
                                  </div>
                                </div>
                            </div>

                            


                                <div class=" relative whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3"> @forelse ( $route->users as $opener) <span class=" flex-none mr-2 inline-flex items-center gap-x-1.5 rounded-md  px-2 text-sm font-medium text-gray-700">
                                    <img alt="{{ $opener->name }}" src="{{ $opener->profile_photo_url }}" class=" h-8 w-8  rounded-md object-cover object-center" />
                                    {{ $opener->name }}
                                  </span> @empty @endforelse </div>

                                  <div class=" relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3 w-52">
                                    @forelse ($route->tags as $tag)
                                    
                                    <span class=" mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">{{$tag->name}}</span>
                                    @empty
                                    @endforelse
                                </div>

                                <div class=" justify-end whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-500 sm:pl-3 flex">
                                  {{ __('QR code') }}
                                </div>

                                
                            </div> @endforeach 
                    </div>
                </div> @endforeach 
        </div>

    </div>
</x-pdf-layout>