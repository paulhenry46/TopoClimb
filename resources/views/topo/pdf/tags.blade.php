<x-pdf-layout>
    <div class='grid grid-cols-2 gap-1 mt-2 mx-2'>
    {{-- @foreach ($area->routes() as $route)
        <div class='border-2 rounded-md'>
            <div class='flex items-center justify-between '>
            <div class='flex items-center'>
            <div class='mx-9'>
                <div class='mx-10 mt-10 mb-1 text-4xl'>
                    {{ $route->gradeFormated() }}
                </div>
                <div class='rounded-md h-10 bg-{{ $route->color }}-500 mt-2 mb-10' >
                </div>
            </div>
            <div class=''>
                <div class='text-3xl'>
                    {{ $route->name }}
                </div>
                <div class='text-xl italic text-gray-700'>
                    {{ $route->comment }}
                </div>
                <div class='text-xl'>
                    <span>{{__('By')}}</span>
                    @foreach ($route->users()->pluck('name') as $name)
                    {{ $name }}
                    @endforeach
                </div>
            </div>
        </div>

            
            <img class='h-38 object-contain' src="{{ $route->qrcode() }}"></img>
    </div>
        </div>
    @endforeach --}}

    @foreach ($area->routes() as $route)
    <div class='border-2 rounded-md'>
        <div class='grid grid-cols-6 gap-3 items-center'>
        
        <div class='col-span-2 ml-3 text-center'>
            <div class='mx-10 mt-7 mb-1 text-4xl'>
                {{ $route->gradeFormated() }}
            </div>
            <div class='rounded-md h-10 bg-{{ $route->color }}-500 mt-2 mb-10' >
            </div>
        </div>
        <div class='col-span-4'>
            <div class='text-3xl'>
                {{ $route->name }}
            </div>
            <div class='text-xl italic text-gray-700'>
                {{ $route->comment }}
            </div>
            <div class='text-xl'>
                <span>{{__('By')}}</span>
                @foreach ($route->users()->pluck('name') as $name)
                {{ $name }}
                @endforeach
            </div>
        </div>

        <!--<div class='col-span-2'>
        <img class='h-38 object-contain' src="{{ $route->qrcode() }}"></img>
        </div>-->
</div>
    </div>
@endforeach
    </div>
</x-pdf-layout>