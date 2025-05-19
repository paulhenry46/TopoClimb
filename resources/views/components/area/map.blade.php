@if($this->area->type == 'bouldering') 
    <div class="bg-white overflow-hidden sm:rounded-lg" x-data="{ expanded: false }" >
      <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="sm:flex sm:items-center">
          <div class="sm:flex-auto stroke-indigo-500">
            <div class="flex justify-between items-center" >
              <div @click="expanded = ! expanded" >
                <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Map')}}</h1>
                <p class="mt-2 text-sm text-gray-700">{{__('Map of the area with sectors and lines')}}</p>
              </div> 
               <div class="sm:ml-16 sm:mt-0 sm:flex-none">
                <button @click="expanded = ! expanded" type="button" class="cursor-pointer inline-flex items-center px-2 py-2 border border-transparent rounded-md font-semibold text-sm tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-200 focus:outline-hidden transition ease-in-out duration-150">
                  <svg xmlns="http://www.w3.org/2000/svg" x-show="!expanded" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/>
                  </svg>
                  <svg x-show="expanded" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="m432-480 156 156q11 11 11 28t-11 28q-11 11-28 11t-28-11L348-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 28-11t28 11q11 11 11 28t-11 28L432-480Z"/>
                  </svg>
                </button>
              </div>
            </div>
            <div x-show="expanded" x-collapse.duration.1000ms class="flex justify-center *:max-h-96 max-h-96 rounded-xl object-contain pt-4"> {!!$this->schema_data[0]!!} </div>
          </div>
        </div>
      </div>
    </div> 
    @else 
    <div class="bg-white overflow-hidden sm:rounded-lg">
      <div class="px-4 sm:px-6 lg:px-8 py-8" 
      @if(count($this->schema_data['sectors']) > 1) 
          x-data="{
          number_sectors : {{ count($this->schema_data['sectors']) }}, 
          sector_selected : {{ $this->schema_data['data'][0]['id'] }}, 
          sectors : {{ json_encode($this->schema_data['data'])}},
          next()
              { 
                if(this.sector_selected == this.number_sectors){
                this.sector_selected = 1;
                }else{
                  this.sector_selected = this.sector_selected +1;
                }
              },
          prev()
              { 
                if(this.sector_selected == 1){
                this.sector_selected = this.number_sectors;
                }else{
                  this.sector_selected = this.sector_selected -1;
                }
              } 
          }" 
      @endif >
      <div class="sm:flex sm:items-center">
          <div x-data="{ expanded: false }"  class="sm:flex-auto stroke-indigo-500">
            <div class="flex justify-between items-center" >
                  <div @click="expanded = ! expanded" >
                    <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Map')}}</h1>
                    <p class="mt-2 text-sm text-gray-700">{{__('Map of the area with sectors and lines')}}</p>
                  </div> 
                   <div class="sm:ml-16 sm:mt-0 sm:flex-none">
                    <button @click="expanded = ! expanded" type="button" class="cursor-pointer py-2 px-2 inline-flex items-center border border-transparent rounded-md font-semibold text-sm tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-200 focus:outline-hidden transition ease-in-out duration-150">
                      <svg xmlns="http://www.w3.org/2000/svg" x-show="!expanded" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/>
                      </svg>
                      <svg x-show="expanded" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="m432-480 156 156q11 11 11 28t-11 28q-11 11-28 11t-28-11L348-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 28-11t28 11q11 11 11 28t-11 28L432-480Z"/>
                      </svg>
                    </button>
              </div> 
            </div>
            <div  x-show="expanded" x-collapse.duration.1000ms class=" rounded-xl object-contain pt-4"> 
              @if(count($this->schema_data['sectors']) <= 1) <div class="relative w-full h-full min-h-96">
                <div class="w-full h-96 z-0 flex items-center justify-center">
                  <img class="object-contain h-96" src="{{ $this->schema_data['data'][0]['bg'] }}" />
                </div>
                <div class="absolute inset-0 flex justify-center items-center z-10"> {!! $this->schema_data['data'][0]['paths'] !!} </div>
            </div> 
            @else 
            <div > 
              @foreach ($this->schema_data['data'] as $data) <div class="relative w-full h-full min-h-96" x-show="sector_selected == {{$data['id']}}" style='display : none;'>
                <div class="w-full h-96 z-0 flex items-center justify-center">
                  <img class="object-contain h-96" src="{{ $data['bg'] }}" />
                </div>
                <div class="absolute inset-0 flex justify-center items-center z-10"> {!! $data['paths'] !!} </div>
              </div> 
              @endforeach 
            </div> 
            <div class="mt-4 flex justify-end gap-2">
              <button @click='prev()' type="button" class="cursor-pointer inline-flex items-center px-2 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-hidden disabled:opacity-50 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
                  <path d="m432-480 156 156q11 11 11 28t-11 28q-11 11-28 11t-28-11L348-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 28-11t28 11q11 11 11 28t-11 28L432-480Z" />
                </svg>
              </button>
              <button @click='next()' type="button" class="cursor-pointer inline-flex items-center px-2 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-hidden disabled:opacity-50 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
                  <path d="M504-480 348-636q-11-11-11-28t11-28q11-11 28-11t28 11l184 184q6 6 8.5 13t2.5 15q0 8-2.5 15t-8.5 13L404-268q-11 11-28 11t-28-11q-11-11-11-28t11-28l156-156Z" />
                </svg>
              </button>
            </div> 
            @endif
          </div>
        </div>
      </div>
    </div>
  </div> 
  @endif