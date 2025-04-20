<x-pdf-layout>
    <div class="py-12 mx-3">
        <div>
               <img src="url" />

               <div class="relative -bottom-px col-span-full col-start-1 row-start-2 h-px bg-gray-200"></div>
               <div class="flex">
                <div>
                    <img class="size-24 rounded-md object-cover" src="{{ $site->profile_picture() }}" alt="Admin">
                </div>
                <div class='mt-2'>
                    <h1 class="text-2xl text-uppercase">{{$area->name}}</h1>
                    <h2 class="text-xl text-uppercase">{{$site->name}}</h2>
                </div>
                <div>
                    QR CODE
                </div>
               </div>
        </div>
    </div>
</x-pdf-layout>