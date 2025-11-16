<div x-data='{                      selected:[], 
                                    open: false, 
                                    active_modal(){this.open = true; $wire.selected_routes = this.selected},
                                    remove(){$wire.set_remove_date(this.selected, $refs.date.value); this.open = false; this.selected = []},
                                    cancel_modal(){this.open = false; this.selected = [], $wire.selected_routes = []},
                                    remove_now(){$wire.set_remove_date(this.selected, "today");  this.open = false; this.selected = []},
                                    }' class="bg-white mt-2 sm:rounded-lg px-6 py-8">
    <div class=" flow-root">
      <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
          <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
        <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Routes')}}</h1>
        <p class="mt-2 text-sm text-gray-700">{{__('Routes of the area')}}</p>
      </div>
      <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
       <x-button x-on:click='open = !open' type="button" x-show='selected.length >0'>{{__('Remove')}}</x-button>
       </div>
    </div>
          <table class="border-separate border-spacing-y-3 min-w-full divide-y divide-gray-300 table-fixed">
            <tbody class="bg-white"> @foreach ($routes as $route) <tr 
              @if($this->area->type == 'bouldering')
              x-on:mouseout="hightlightSector(0)" x-on:mouseover="hightlightSector({{$route->line->sector->id}})" 
              @click="selectRoute({{$route->id}}); $dispatch('route-changed', { id: {{$route->id}} })"
              @else
              x-on:mouseout="hightlightRoute(0)" x-on:mouseover="hightlightRoute({{$route->id}})" 
              @click="selectRoute({{$route->id}}); $dispatch('route-changed', { id: {{$route->id}} })"
              @endif
              class="hover:bg-gray-50 cursor-pointer">
              <td @click.stop class="hidden md:block whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                  <input value="{{$route->id}}" x-model="selected" type="checkbox" class='w-5 h-5 rounded-sm border-gray-300 text-gray-600 shadow-xs focus:ring-gray-500'>
                </td>
              <td class="bg-{{$route->color}}-300 border-2 border-{{$route->color}}-300 rounded-l-md text-center h-16 w-16 relative whitespace-nowrap font-medium text-gray-900">
               <div :class='hightlightedRoute == {{$route->id}} ? "grayscale-0" : "grayscale"' class='rounded-l h-full w-full bg-cover' style="background-image: url({{ $route->thumbnail() }})"></div>
              </td>
                <td class=" text-2xl text-center w-16 bg-{{$route->color}}-300 relative whitespace-nowrap py-4 pl-4 pr-3 font-medium text-gray-900 sm:pl-3">
                  {{$route->gradeFormated}}
                </td>
                <td class="  whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                  <div class="flex items-center">
                    <div>
                      <div class="font-bold pb-1">{{$route->name}}
                         @if($route->created_at >= now()->subDays(7))
      <span class=" top-1 left-1 bg-gray-900 text-white text-xs font-bold px-2 py-0.5 rounded shadow">New</span>
    @endif
                      </div> 
                      @if($route->line->local_id == 0) 
                      <div class="text-sm opacity-50">{{__('Sector')}} {{$route->line->sector->local_id}}
                        </div> 
                      @else 
                      <div class="text-sm opacity-50">{{__('Line')}} {{$route->line->local_id}}
                        </div> 
                        @endif
                    </div>
                  </div>
                </td>
                <td class="hidden sm:table-cell relative whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3"> @forelse ( $route->users as $opener) <span class=" flex-none mr-2 inline-flex items-center gap-x-1.5 rounded-md  px-2 text-sm font-medium text-gray-700">
                    <img alt="{{ $opener->name }}" src="{{ $opener->profile_photo_url }}" class=" h-8 w-8  rounded-md object-cover object-center" />
                    {{ $opener->name }}
                  </span> @empty @endforelse </td>
                <td class=" justify-end whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-500 sm:pl-3 flex">
                  <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
                    <path d="M268-240 42-466l57-56 170 170 56 56-57 56Zm226 0L268-466l56-57 170 170 368-368 56 57-424 424Zm0-226-57-56 198-198 57 56-198 198Z" />
                  </svg>
                  {{ $route->logs->count() }}
                  <svg class="ml-4 mr-2" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
                    <path d="M840-136q-8 0-15-3t-13-9l-92-92H320q-33 0-56.5-23.5T240-320v-40h440q33 0 56.5-23.5T760-440v-280h40q33 0 56.5 23.5T880-640v463q0 18-12 29.5T840-136ZM120-336q-16 0-28-11.5T80-377v-423q0-33 23.5-56.5T160-880h440q33 0 56.5 23.5T680-800v280q0 33-23.5 56.5T600-440H240l-92 92q-6 6-13 9t-15 3Z" />
                  </svg>
                  {{ $route->logs->where('comment', '!=', null)->count() }}
                  <svg class="ml-4 mr-2" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
                    <path d="m426-330 195-125q14-9 14-25t-14-25L426-630q-15-10-30.5-1.5T380-605v250q0 18 15.5 26.5T426-330Zm54 250q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z" />
                  </svg>
                  {{ $route->logs->where('video_url', '!=', null)->count() }}
                </td>
              </tr> @endforeach </tbody>
          </table>
          {{ $routes->links() }}
        </div>
      </div>
    </div>


    <div x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" class="fixed inset-0 bg-gray-500/75  transition-opacity" x-show='open' x-cloak></div>
          <div class="fixed inset-0 z-10 overflow-y-auto" x-show='open' x-cloak>
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0" x-show='open' x-cloak>
              <div x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-300" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90" class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg" x-show='open' x-cloak>
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                  <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                      <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                      </svg>
                    </div>
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                      <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">{{__(' Set the route dismantling date') }}</h3>
                      <div class="mt-2">
                        <p class="text-sm text-gray-500">{{ __('Are you sure you want to remove this routes ? Set the date below') }}</p>
                        <x-input x-ref='date' id="date" type="date" class="mt-1 block w-full"/>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                  <button @click='remove()' type="button" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">{{ __('Set date of removing') }}</button>
                  <button @click='remove_now()' type="button" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">{{ __('Remove now') }}</button>
                  <button @click='cancel_modal()' type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">{{ __('Cancel') }}</button>
                </div>
              </div>
            </div>
          </div>
  </div>