<?php

use Livewire\Volt\Component;
use App\Models\Area;
use App\Models\Site;
use App\Models\Line;
use App\Models\Route;
use App\Models\Tag;
use App\Models\Log;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;
use Livewire\Attributes\Url;
new class extends Component {
  use WithPagination, WithFileUploads;

  
    public Area $area;
    public Site $site;
    #[Url(keep: true)]
    public string $route_id ='';
    public Route $route;
    public array $schema_data = [];
    public $cotations = [];
    public $tags_available;

    public $selected_sector;
    public $selected_line;
    public array $tags_choosen;
    public array $tags_id;
    public $search;
    public int $cotation_from;
    public int $cotation_to;
    public $user_state;

  public function open_route($id){
    $this->route = Route::find($id);
    $this->route_id = $id;
  }

    public function mount(Area $area){
      $this->area = $area;
      $this->site = $this->area->site;
      if($area->type == 'bouldering'){
        foreach ($area->sectors as $sector) {
            array_push($this->schema_data, Storage::get('plans/site-'.$this->site->id.'/area-'.$this->area->id.'/edited/user.svg'));
        }
      }else{
        $sectors_id = [];
        $this->schema_data['data'] = [];
        $this->schema_data['sectors'] = [];
        foreach ($area->sectors as $sector) {
          $data = ['id' => $sector->local_id, 'paths' => Storage::get('paths/site-'.$this->site->id.'/area-'.$this->area->id.'/edited/common_paths.svg'),'bg' => Storage::url('plans/site-'.$this->site->id.'/area-'.$this->area->id.'/sector-'.$sector->id.'/schema')];
            //array_push($this->url_map, Storage::get('paths/site-'.$this->site->id.'/area-'.$this->area->id.'/edited/common_paths.svg'));
            array_push($this->schema_data['data'], $data);
            //array_push($this->url_map, Storage::get('paths/site-'.$this->site->id.'/area-'.$this->area->id.'/common.src.svg'));//TEST
            //array_push($this->url_background, Storage::url('plans/site-'.$this->site->id.'/area-'.$this->area->id.'/sector-'.$sector->id.'/schema'));
            array_push($this->schema_data['sectors'], $sector->local_id);
          }
      }
        $this->cotations = [
        '3a' => 300, '3a+' => 310, '3b' => 320, '3b+' => 330, '3c' => 340, '3c+' => 350, 
        '4a' => 400, '4a+' => 410, '4b' => 420, '4b+' => 430, '4c' => 440, '4c+' => 450, 
        '5a' => 500, '5a+' => 510, '5b' => 520, '5b+' => 530, '5c' => 540, '5c+' => 550, 
        '6a' => 600, '6a+' => 610, '6b' => 620, '6b+' => 630, '6c' => 640, '6c+' => 650, 
        '7a' => 700, '7a+' => 710, '7b' => 720, '7b+' => 730, '7c' => 740, '7c+' => 750, 
        '8a' => 800, '8a+' => 810, '8b' => 820, '8b+' => 830, '8c' => 840, '8c+' => 850, 
        '9a' => 900, '9a+' => 910, '9b' => 920, '9b+' => 930, '9c' => 940, '9c+' => 950,];
        
      $tags_temp = Tag::all()->pluck('id', 'name')->toArray();
      $tags = [];
      foreach($tags_temp as $name => $key){
      $tags[] = ['name' => $name, 'id' => $key];
      }
      $this->tags_available = $tags;
      $this->tags_id = [];
      $this->cotation_to = 0;
      $this->cotation_from = 0;
      if($this->route_id != null){
        $this->route = Route::find($this->route_id);
      }else{
        $this->route = Route::first();
      }
      
      $this->user_state = 'all';
      $this->selected_line = 0;
      }

    public function with(){
      if($this->selected_sector != null and $this->selected_sector != '0'){
        $lines = Line::where('sector_id', $this->selected_sector);
      }else{
        $lines = Line::whereIn('sector_id', $this->area->sectors()->pluck('id'));
      }
      if($this->selected_line == 0){
        $lines_selected = $lines->pluck('id');
      }else{
        $lines_selected = [$this->selected_line];
      }
      
      //return $routes;
    $routesQuery = Route::whereIn('line_id', $lines_selected)
      ->when($this->search, function($query, $search) {
          return $query->where('name', 'LIKE', "%{$this->search}%");
      })
      ->when($this->cotation_to != 0, function($query, $cotation) {
          return $query->where('grade', '<=', $this->cotation_to);
      })
      ->when($this->cotation_from != 0, function($query, $cotation) {
          return $query->where('grade', '>=', $this->cotation_from);
      })
      ->when($this->user_state == 'success', function($query) {
          return $query->whereHas('logs', function ($query) {
        $query->where('user_id', '=', Auth::id());
    });
      })
      ->when($this->user_state == 'fail', function($query) {
          return $query->whereDoesntHave('logs', function ($query) {
        $query->where('user_id', '=', Auth::id());
    });
      })
      ->when(!empty($this->tags_id), function($query) {
          return $query->whereHas('tags', function ($query) {
              $query->whereIn('tags.id', $this->tags_id);
          }, '>=', count($this->tags_id));
      });
    return ['routes' => $routesQuery->paginate(10), 'logs' => Log::where('route_id', $this->route->id)->get(), 'lines' => $lines->get()];
    }

    public function selectSector($id){
      $this->selected_sector = $id;
    }
    public function selectLine($id){
      $this->selected_line = $id;
    }
}; ?>

<div class="grid grid-cols-3 mt-8 gap-4 pt-2">

  <div class="col-span-2 flex flex-col" 
    @if($this->area->type == 'bouldering') 
      x-data="{ hightlightedSector: 0, selectedSector: 0, selectSector(id){ this.selectedSector = id; $wire.selectSector(id); }, hightlightSector(id){ this.hightlightedSector = id; }, }" 
    @else 
      x-data="{ hightlightedRoute: 0, selectedRoute: 0, selectRoute(id){ this.selectedRoute = id; $wire.open_route(id); }, hightlightRoute(id){ this.hightlightedRoute = id; }, hightlightedLine: 0, selectedLine: 0, selectLine(id){ this.selectedLine = id; $wire.selectLine(id); }, hightlightLine(id){ this.hightlightedLine = id; }, }" > 
    @endif 
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
                <button @click="expanded = ! expanded" type="button" class=" inline-flex items-center px-2 py-2 border border-transparent rounded-md font-semibold text-sm tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-200 focus:outline-hidden transition ease-in-out duration-150">
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
                    <button @click="expanded = ! expanded" type="button" class="py-2 px-2 inline-flex items-center border border-transparent rounded-md font-semibold text-sm tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-200 focus:outline-hidden transition ease-in-out duration-150">
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
                  <img class="h-96" src="{{ $this->schema_data['data'][0]['bg'] }}" />
                </div>
                <div class="absolute inset-0 flex justify-center items-center z-10"> {!! $this->schema_data['data'][0]['paths'] !!} </div>
            </div> 
            @else 
            <div > 
              @foreach ($this->schema_data['data'] as $data) <div class="relative w-full h-full min-h-96" x-show="sector_selected == {{$data['id']}}" style='display : none;'>
                <div class="w-full h-96 z-0 flex items-center justify-center">
                  <img class="h-96" src="{{ $data['bg'] }}" />
                </div>
                <div class="absolute inset-0 flex justify-center items-center z-10"> {!! $data['paths'] !!} </div>
              </div> 
              @endforeach 
            </div> 
            <div class="mt-4 flex justify-end gap-2">
              <button @click='prev()' type="button" class="inline-flex items-center px-2 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-hidden disabled:opacity-50 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
                  <path d="m432-480 156 156q11 11 11 28t-11 28q-11 11-28 11t-28-11L348-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 28-11t28 11q11 11 11 28t-11 28L432-480Z" />
                </svg>
              </button>
              <button @click='next()' type="button" class="inline-flex items-center px-2 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-hidden disabled:opacity-50 transition ease-in-out duration-150">
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
  </div> @endif <div class="bg-white overflow-hidden sm:rounded-lg mt-2">
    <div class="px-4 sm:px-6 lg:px-8 py-8">
      <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto stroke-indigo-500" x-data="{ expanded: false }">
          <div class="flex justify-between items-center" >
            <div @click="expanded = ! expanded" >
              <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Filters')}}</h1>
              <p class="mt-2 text-sm text-gray-700">{{__('Choose which routes you want to see.')}}</p>
            </div> 
             <div class="sm:ml-16 sm:mt-0 sm:flex-none">
              <button @click="expanded = ! expanded" type="button" class=" inline-flex items-center px-2 py-2 border border-transparent rounded-md font-semibold text-sm tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-200 focus:outline-hidden transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" x-show="!expanded" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/>
                </svg>
                <svg x-show="expanded" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="m432-480 156 156q11 11 11 28t-11 28q-11 11-28 11t-28-11L348-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 28-11t28 11q11 11 11 28t-11 28L432-480Z"/>
                </svg>
              </button>
            </div>
          </div>
          <div class=" rounded-xl pt-4" x-show="expanded" x-collapse.duration.1000ms >
            <div class="grid grid-cols-2">
              <div class="col-span-1">
                <div class="space-y-2 px-4">
                  <div class="w-full">
                    <x-label for="name" value="{{ __('Search') }}" />
                    <x-input wire:model.live="search" type="text" name="name" id="project-name" class="block w-full mt-2" />
                    <x-input-error for="name" class="mt-2" />
                  </div>
                </div>
              </div>
              <div class="col-span-1">
                <div class="space-y-2 px-4">
                  <div class="w-full">
                    <x-label for="name" value="{{ __('Cotation') }}" />
                    <div class="mt-2 flex items-center gap-2">
                      {{ __('From') }}
                      <select wire:model.live='cotation_from' id="location" name="location" class=" block w-24 rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6">
                        <option value="0">0</option> @foreach ($this->cotations as $key => $value) <option value="{{ $value }}">{{ $key }}</option> @endforeach
                      </select>
                      {{ __('to') }}
                      <select wire:model.live='cotation_to' id="location" name="location" class=" block w-24 rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6">
                        <option value="0">+ ∞</option> @foreach ($this->cotations as $key => $value) <option value="{{ $value }}">{{ $key }}</option> @endforeach
                      </select>
                      <x-input-error for="name" class="mt-2" />
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-span-1" x-data="{tags: $wire.tags_available, 
                SelectedID: $wire.entangle('tags_id'), 
                SelectedTags: $wire.entangle('tags_choosen'),
                term : '',
                showListe: false, 
                toogle(id){
                          if (this.SelectedID.includes(id)) {
                              this.SelectedID = this.SelectedID.filter(item => item !== id);
                          } else {
                              this.SelectedID.push(id);
                          }
                          this.SelectedTags = this.tags.filter(obj => {
                              return this.SelectedID.includes(obj.id)
                            })
                          this.term = '';
                          $wire.$refresh();
                      }
                  }">
                <div class="space-y-2 px-4">
                  <div class="flex mt-3">
                    <x-label class="mr-1" for="creators" value="{{ __('Tags') }} : " />
                    <template x-for="tag in SelectedTags">
                      <span x-text="tag['name']" class=" mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                        <svg class="h-1.5 w-1.5 fill-gray-500" viewBox="0 0 6 6" aria-hidden="true">
                          <circle cx="3" cy="3" r="3"></circle>
                        </svg>
                      </span>
                    </template>
                  </div>
                  <div @click.outside="showListe = false" class="sm:col-span-2">
                    <div>
                      <div class="relative mt-2 ">
                        <input placeholder="Add a tag" x-model="term" @click="showListe = true" id="combobox" type="text" class="mt-2 w-full rounded-md border-0 bg-white py-1.5 pl-3 pr-12 text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6" role="combobox" aria-controls="options" aria-expanded="false">
                        <ul x-show="showListe" class=" z-20 mt-1 max-h-20 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-hidden sm:text-sm" id="options" role="listbox">
                          <template x-for="tag in tags">
                            <li x-show="!(term.length > 0 && !tag['name'].toLowerCase().includes(term.toLowerCase()))" :class="SelectedID.includes(tag['id']) ? 'font-semibold' : 'text-gray-900'" @click="toogle(tag['id'])" class="hover:bg-gray-100 relative cursor-default select-none py-2 pl-8 pr-4 text-gray-900" id="option-0" role="option" tabindex="-1">
                              <span class="block truncate" x-text="tag['name']"></span>
                              <span :class="SelectedID.includes(tag['id']) ? 'text-gray-600' : ' hidden'" class="absolute inset-y-0 left-0 flex items-center pl-1.5 text-gray-600">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                  <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                </svg>
                              </span>
                            </li>
                          </template>
                        </ul>
                      </div>
                    </div>
                    <x-input-error for="tags" class="mt-2" />
                  </div>
                </div>
              </div> @if($this->area->sectors->count() > 1) <div class="col-span-1">
                <div class="space-y-2 px-4">
                  <div class="w-full mt-3">
                    <x-label for="name" value="{{ __('Sector') }}" />
                    <div class="mt-4">
                      <select wire:model.live="selected_sector" id="location" name="location" class=" block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6">
                        <option value="0">{{__('All')}}</option> @foreach ($this->area->sectors as $sector) <option value="{{ $sector->id }}">{{ $sector->name }}</option> @endforeach
                      </select>
                      <x-input-error for="name" class="mt-2" />
                    </div>
                  </div>
                </div>
              </div> @endif @if($this->area->type == 'trad') <div class="col-span-1">
                <div class="space-y-2 px-4">
                  <div class="w-full mt-3">
                    <x-label for="name" value="{{ __('Line') }}" />
                    <div class="mt-4">
                      <select wire:model.live="selected_line" id="location" name="location" class=" block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6">
                        <option value="0">{{__('All')}}</option> @foreach ($lines as $line) <option value="{{ $line->id }}">{{ $line->local_id }}</option> @endforeach
                      </select>
                      <x-input-error for="name" class="mt-2" />
                    </div>
                  </div>
                </div>
              </div> @endif <div class="col-span-1">
                <div class="space-y-2 px-4">
                  <div class="w-full mt-3">
                    <x-label for="name" value="{{ __('State') }}" />
                    <div class="mt-4">
                      <select wire:model.live="user_state" id="location" name="location" class=" block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6">
                        <option value="all">{{__('All')}}</option>
                        <option value="success">{{__('Success')}}</option>
                        <option value="fail">{{__('Not climbed')}}</option>
                      </select>
                      <x-input-error for="name" class="mt-2" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="bg-white mt-2 sm:rounded-lg px-6 py-8">
    <div class='hidden'>
      <div class='border-red-300 border-orange-300 border-amber-300 border-green-300 border-blue-300 border-violet-300 border-purple-300 border-yellow-300 border-emerald-300 border-pink-300'></div>
    </div>
    <div class=" flow-root">
      <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
          <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Routes')}}</h1>
          <p class="mt-2 text-sm text-gray-700 mb-2">{{__('Routes of the area')}}</p>
          <table class="border-separate border-spacing-y-3 min-w-full divide-y divide-gray-300 table-fixed">
            <tbody class="bg-white"> @foreach ($routes as $route) <tr 
              @if($this->area->type == 'bouldering')
              x-on:mouseout="hightlightSector(0)" x-on:mouseover="hightlightSector({{$route->line->sector->id}})" 
              @click="$wire.open_route({{$route->id}})"
              @else
              x-on:mouseout="hightlightRoute(0)" x-on:mouseover="hightlightRoute({{$route->id}})" 
              @click="selectRoute({{$route->id}})"
              @endif
              class="hover:bg-gray-50 cursor-pointer">
              <td class="bg-{{$route->color}}-300 border-2 border-{{$route->color}}-300 rounded-l-md text-center h-16 w-16 relative whitespace-nowrap font-medium text-gray-900">
               <div :class='hightlightedRoute == {{$route->id}} ? "grayscale-0" : "grayscale"' class='rounded-l h-full w-full bg-cover' style="background-image: url({{ $route->thumbnail() }})"></div>
              </td>
                <td class=" text-2xl text-center w-16 bg-{{$route->color}}-300 relative whitespace-nowrap py-4 pl-4 pr-3 font-medium text-gray-900 sm:pl-3">
                  {{$route->gradeFormated()}}
                </td>
                <td class="  whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                  <div class="flex items-center">
                    <div>
                      <div class="font-bold pb-1">{{$route->name}}</div> @if($route->line->local_id == 0) <div class="text-sm opacity-50">{{__('Sector')}} {{$route->line->sector->local_id}}</div> @else <div class="text-sm opacity-50">{{__('Line')}} {{$route->line->local_id}}</div> @endif
                    </div>
                  </div>
                </td>
                <td class=" relative whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3"> @forelse ( $route->users as $opener) <span class=" flex-none mr-2 inline-flex items-center gap-x-1.5 rounded-md  px-2 text-sm font-medium text-gray-700">
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
  </div>
</div>
<div>
  <div class="bg-center bg-cover h-96 rounded-t-2xl " style="background-image: url('{{ $this->route->picture() }}'); background-position-y: 50%; filter: opacity(99.9%) grayscale(100%);"></div>
  <div class="rounded-2xl bg-center bg-cover  z-10 h-96 -mt-96" style="
              background-image: url('{{$this->route->circle()}}'); filter: opacity(99.9%);"></div>
  <div class="bg-white overflow-hidden /*shadow-xl*/ sm:rounded-b-lg">
    <div class="px-4 sm:px-6 lg:px-8 py-8">
      <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
          <h1 class="text-2xl font-semibold leading-6 text-gray-900">{{$this->route->name}}</h1>
          <p class="mt-1 text-sm text-gray-700">
            @if($this->area->type == 'bouldering')
            {{$this->route->line->sector->name}}
            @else
           {{ __('Line') }}  {{$this->route->line->local_id}}
            @endif
          </p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 flex gap-x-1">
          <button type="button" class="rounded-md bg-gray-800 p-2 text-white shadow-xs hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
              <path d="m480-240-168 72q-40 17-76-6.5T200-241v-519q0-33 23.5-56.5T280-840h400q33 0 56.5 23.5T760-760v519q0 43-36 66.5t-76 6.5l-168-72Zm0-88 200 86v-518H280v518l200-86Zm0-432H280h400-200Z" />
            </svg>
          </button>
          <livewire:routes.logger :route='$this->route' />
        </div>
      </div>
      <div class="grid grid-cols-3 mt-4 gap-x-2">
        <div class="text-gray-500 mt-4 flex w-full flex-none gap-x-2">
          <dt class="flex-none">
            <span class="sr-only">Mail</span>
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
              <path d="M480-440q58 0 99-41t41-99q0-58-41-99t-99-41q-58 0-99 41t-41 99q0 58 41 99t99 41ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-46q-54-53-125.5-83.5T480-360q-83 0-154.5 30.5T200-246v46Z" />
            </svg>
          </dt>
          <dd class="text-sm leading-6 "> @foreach ($this->route->users as $user) {{ $user->name }} @endforeach </dd>
        </div>
        <div class="text-gray-500 mt-4 flex w-full flex-none gap-x-2">
          <dt class="flex-none">
            <span class="sr-only">Mail</span>
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
              <path d="M360-300q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29ZM200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-40q0-17 11.5-28.5T280-880q17 0 28.5 11.5T320-840v40h320v-40q0-17 11.5-28.5T680-880q17 0 28.5 11.5T720-840v40h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Z" />
            </svg>
          </dt>
          <dd class="text-sm leading-6 ">{{ $this->route->created_at->format('d/m/y') }}</dd>
        </div>
        <div class="text-gray-500 mt-4 flex w-full flex-none gap-x-2">
          <dt class="flex-none">
            <span class="sr-only">Mail</span>
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
              <path d="M480-269 314-169q-11 7-23 6t-21-8q-9-7-14-17.5t-2-23.5l44-189-147-127q-10-9-12.5-20.5T140-571q4-11 12-18t22-9l194-17 75-178q5-12 15.5-18t21.5-6q11 0 21.5 6t15.5 18l75 178 194 17q14 2 22 9t12 18q4 11 1.5 22.5T809-528L662-401l44 189q3 13-2 23.5T690-171q-9 7-21 8t-23-6L480-269Z" />
            </svg>
          </dt>
          <dd class="text-sm leading-6 ">{{ $this->route->gradeFormated() }}</dd>
        </div>
        <div class="text-gray-500 mt-5 flex w-full flex-none gap-x-2">
          <dt class="flex-none">
            <span class="sr-only">Mail</span>
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
              <path d="M856-390 570-104q-12 12-27 18t-30 6q-15 0-30-6t-27-18L103-457q-11-11-17-25.5T80-513v-287q0-33 23.5-56.5T160-880h287q16 0 31 6.5t26 17.5l352 353q12 12 17.5 27t5.5 30q0 15-5.5 29.5T856-390ZM260-640q25 0 42.5-17.5T320-700q0-25-17.5-42.5T260-760q-25 0-42.5 17.5T200-700q0 25 17.5 42.5T260-640Z" />
            </svg>
          </dt>
          <dd class="text-sm leading-6 flex gap-x-1"> @foreach ($this->route->tags as $tag) <span class=" mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
              <svg class="h-1.5 w-1.5 fill-gray-500" viewBox="0 0 6 6" aria-hidden="true">
                <circle cx="3" cy="3" r="3"></circle>
              </svg>
              {{ $tag->name }}
            </span> @endforeach </dd>
        </div>
      </div>
      <div class="mt-12" x-data="{ activeTab:  0 }">
        <h1 class="text-2xl font-semibold leading-6 text-gray-900">{{__('Activity')}}</h1>
        <div class="hidden sm:block">
          <div class="border-b border-gray-200">
            <nav class="-mb-px flex justify-between" aria-label="Tabs">
              <a @click="activeTab = 0" :class="activeTab == 0 ? 'border-gray-800 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-200 hover:text-gray-700 cursor-pointer'" class="flex whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                {{ __('Comments') }}
                <span :class="activeTab == 0 ? 'bg-gray-100 text-gray-600' : 'bg-gray-100 text-gray-900'" class="ml-3 hidden rounded-full py-0.5 px-2.5 text-xs font-medium md:inline-block">{{$logs->where('comment', '!=', null)->count()}}</span>
              </a>
              <a @click="activeTab = 1" :class="activeTab == 1 ? 'border-gray-800 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-200 hover:text-gray-700 cursor-pointer'" class=" flex whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                {{ __('Ascents') }}
                <span :class="activeTab == 1 ? 'bg-gray-100 text-gray-600' : 'bg-gray-100 text-gray-900'" class=" ml-3 hidden rounded-full py-0.5 px-2.5 text-xs font-medium md:inline-block">{{$logs->count()}}</span>
              </a>
              <a @click="activeTab = 2" :class="activeTab == 2 ? 'border-gray-800 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-200 hover:text-gray-700 cursor-pointer'" class="flex whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium" aria-current="page">
                {{ __('Video') }}
                <span :class="activeTab == 2 ? 'bg-gray-100 text-gray-600' : 'bg-gray-100 text-gray-900'" class="ml-3 hidden rounded-full py-0.5 px-2.5 text-xs font-medium md:inline-block">{{$logs->where('video_url', '!=', null)->count()}}</span>
              </a>
            </nav>
          </div>
        </div>
        <div x-show="activeTab == 0"> @foreach ($logs->where('comment','!=', null) as $log) <div class=" mt-2 flex  items-start space-x-3">
            <div>
              <div class=" px-1">
                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-gray-100 ring-8 ring-white">
                  <img class="rounded-md" src="{{ $log->user->profile_photo_url }}" />
                </div>
              </div>
            </div>
            <div class="min-w-0 flex-1 py-0">
              <div class="text-sm leading-6 text-gray-500">
                <span class="">
                  <a href="#" class="font-medium text-gray-900">{{ $log->user->name }}</a>
                  <span class="whitespace-nowrap">{{ $log->created_at->format('d/m/Y') }}</span>
                  </br>
                </span>
                <span class="">
                  {{ $log->comment }}
                </span>
              </div>
            </div>
          </div> @endforeach </div>
        <div x-show="activeTab == 1"> @foreach ($logs as $log) <div class=" mt-2 flex items-center items-start space-x-3">
            <div>
              <div class=" px-1">
                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-gray-100 ring-8 ring-white">
                  <img class="rounded-md" src="{{ $log->user->profile_photo_url }}" />
                </div>
              </div>
            </div>
            <div class="min-w-0 flex-1 py-0">
              <div class="text-sm leading-6 text-gray-500">
                <span class="">
                  <a href="#" class="font-medium text-gray-900">{{ $log->user->name }}</a>
                  <span class="whitespace-nowrap">{{ $log->created_at->format('d/m/Y') }}</span>
                  </br>
                </span>
                <span class=""> @if($log->way == 'top-rope') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                    <svg class="h-1.5 w-1.5 fill-red-500" viewBox="0 0 6 6" aria-hidden="true">
                      <circle cx="3" cy="3" r="3" />
                    </svg>
                    {{ __('Top-rope') }}
                  </a> @elseif($log->way == 'leading') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                    <svg class="h-1.5 w-1.5 fill-green-500" viewBox="0 0 6 6" aria-hidden="true">
                      <circle cx="3" cy="3" r="3" />
                    </svg>
                    {{ __('Leading') }}
                  </a> @endif @if($log->type == 'view') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                    <svg class="h-1.5 w-1.5 fill-indigo-500" viewBox="0 0 6 6" aria-hidden="true">
                      <circle cx="3" cy="3" r="3" />
                    </svg>
                    {{ __('View') }}
                  </a> @elseif($log->type == 'work') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                    <svg class="h-1.5 w-1.5 fill-emerald-500" viewBox="0 0 6 6" aria-hidden="true">
                      <circle cx="3" cy="3" r="3" />
                    </svg>
                    {{ __('After work') }}
                  </a> @elseif($log->type == 'flash') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                    <svg class="h-1.5 w-1.5 fill-amber-500" viewBox="0 0 6 6" aria-hidden="true">
                      <circle cx="3" cy="3" r="3" />
                    </svg>
                    {{ __('Flash') }}
                  </a> @endif </span>
              </div>
            </div>
          </div> @endforeach </div>
        <div x-show="activeTab == 2"> Videos </div>
      </div>
    </div>
  </div>
</div>