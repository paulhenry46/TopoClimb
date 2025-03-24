<?php

use Livewire\Volt\Component;
use App\Models\Area;
use App\Models\Site;
use App\Models\Line;
use App\Models\Route;
use App\Models\Tag;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;
new class extends Component {
  use WithPagination, WithFileUploads;

  
    public Area $area;
    public Site $site;
    public Route $route;
    public array $url_map = [];
    public $cotations = [];
    public $tags_available;

    public $selected_sector;
    public array $tags_choosen;
    public array $tags_id;
    public $search;
    public int $cotation_from;
    public int $cotation_to;



    public function mount(Area $area){
      $this->area = $area;
      $this->site = $this->area->site;
        foreach ($area->sectors as $sector) {
            array_push($this->url_map, Storage::get('plans/site-'.$this->site->id.'/area-'.$this->area->id.'/edited/admin.svg'));
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
      $this->route = Route::find(4);
      }

    public function with(){
      if($this->selected_sector != null and $this->selected_sector != '0'){
        $lines = Line::where('sector_id', $this->selected_sector)->pluck('id');
      }else{
        $lines = Line::whereIn('sector_id', $this->area->sectors()->pluck('id'))->pluck('id');
      }
      //return $routes;
    $routesQuery = Route::whereIn('line_id', $lines)
      ->when($this->search, function($query, $search) {
          return $query->where('name', 'LIKE', "%{$this->search}%");
      })
      ->when($this->cotation_to != 0, function($query, $cotation) {
          return $query->where('grade', '<=', $this->cotation_to);
      })
      ->when($this->cotation_from != 0, function($query, $cotation) {
          return $query->where('grade', '>=', $this->cotation_from);
      })
      ->when(!empty($this->tags_id), function($query) {
          return $query->whereHas('tags', function ($query) {
              $query->whereIn('tags.id', $this->tags_id);
          }, '>=', count($this->tags_id));
      });
    return ['routes' => $routesQuery->paginate(10)];
    }

    public function selectSector($id){
      $this->selected_sector = $id;
    }
}; ?>

<div class="grid grid-cols-3 mt-8 gap-4 pt-2">
  <div class="col-span-2 flex flex-col" x-data="{
        hightlightedSector: 0,
        selectedSector: 0,
        selectSector(id){ this.selectedSector = id; $wire.selectSector(id); },
        hightlightSector(id){ this.hightlightedSector = id; },
        }">
    <div class="bg-white overflow-hidden /*shadow-xl*/ sm:rounded-lg">
      <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="sm:flex sm:items-center">
          <div class="sm:flex-auto stroke-indigo-500">
            <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Map')}}</h1>
            <p class="mt-2 text-sm text-gray-700">{{__('Map of the area with sectors and lines')}}</p>
            <div class="flex justify-center [&>*]:max-h-96 max-h-96 rounded-xl object-contain pt-4"> {!!$this->url_map[0]!!} </div>
          </div>
        </div>
      </div>
    </div>
    <div class="bg-white overflow-hidden /*shadow-xl*/ sm:rounded-lg mt-2">
      <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="sm:flex sm:items-center">
          <div class="sm:flex-auto stroke-indigo-500">
            <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Filters')}}</h1>
            <p class="mt-2 text-sm text-gray-700">{{__('Map of the area with sectors and lines')}}</p>
            <div class=" rounded-xl pt-4"> 
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
                      <option value="0">0</option>
                      @foreach ($this->cotations as $key => $value)
                      <option value="{{ $value }}">{{ $key }}</option>
                      @endforeach
                    </select>
                     {{ __('to') }} 
                     <select wire:model.live='cotation_to' id="location" name="location" class=" block w-24 rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6">
                      <option value="0">+ ∞</option>
                      @foreach ($this->cotations as $key => $value)
                      <option value="{{ $value }}">{{ $key }}</option>
                      @endforeach
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
                    <div @click.outside="showListe = false" class="sm:col-span-2" >
                      <div>
                        <div class="relative mt-2 ">
                          <input placeholder="Add a tag" x-model="term" @click="showListe = true" id="combobox" type="text" class="mt-2 w-full rounded-md border-0 bg-white py-1.5 pl-3 pr-12 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6" role="combobox" aria-controls="options" aria-expanded="false">
                          <ul x-show="showListe" class=" z-20 mt-1 max-h-20 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm" id="options" role="listbox">
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
                </div>
                <div class="col-span-1">
                  <div class="space-y-2 px-4">
                    <div class="w-full mt-3">
                      <x-label for="name" value="{{ __('Sector') }}" />
                      <div class="mt-4">
                     <select wire:model.live="selected_sector" id="location" name="location" class=" block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6">
                      <option value="0">{{__('All')}}</option>
                      @foreach ($this->area->sectors as $sector)
                      <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                      @endforeach
                    </select>
                      <x-input-error for="name" class="mt-2" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <!--  Secteur sur map 
              Difficulté sur curseur
              Tags (meme composant que pour la création)
              Recherche (barre de recherche)
              Personnel : Réusssi/projet/non réussi-->
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="bg-white mt-2 sm:rounded-lg px-6 py-8">
      <div class=" flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Routes')}}</h1>
            <p class="mt-2 text-sm text-gray-700 mb-2">{{__('Routes of the area')}}</p>
            <table class="border-separate border-spacing-y-3 min-w-full divide-y divide-gray-300 table-fixed">
              <tbody class="bg-white"> @foreach ($routes as $route) <tr x-on:mouseout="hightlightSector(0)" x-on:mouseover="hightlightSector({{$route->line->sector->id}})" class="hover:bg-gray-50">
                  <td class="rounded-l-md text-xl text-center w-16 bg-{{$route->color}}-300 relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
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
                  <td class=" relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3"></td>
                </tr> @endforeach </tbody>
            </table>
            {{ $routes->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
        <div>
          <div class="bg-center bg-cover h-96 rounded-t-2xl" style="background-image: url('{{ $this->route->picture() }}'); background-position-y: 50%; filter: opacity(37.9%) grayscale(100%);">
          </div>
          <div class="rounded-2xl bg-center bg-cover *bg-gradient-to-tl *from-gray-600 *to-gray-400  z-10 h-96 -mt-96" style="
              background-image: url('http://127.0.0.1:8000/storage/photos/site-1/area-1/route-4.svg');">
          </div>
          
          <div class="bg-white overflow-hidden /*shadow-xl*/ sm:rounded-b-lg">
            <div class="px-4 sm:px-6 lg:px-8 py-8">
              <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                  <h1 class="text-2xl font-semibold leading-6 text-gray-900">{{$this->route->name}}</h1>
                  <p class="mt-1 text-sm text-gray-700">{{$this->route->line->sector->name}}</p>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                  <button type="button" class="rounded-md bg-gray-800 p-2 text-white shadow-sm hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="m480-240-168 72q-40 17-76-6.5T200-241v-519q0-33 23.5-56.5T280-840h400q33 0 56.5 23.5T760-760v519q0 43-36 66.5t-76 6.5l-168-72Zm0-88 200 86v-518H280v518l200-86Zm0-432H280h400-200Z"/></svg>
                  </button>  
                  <button type="button" class="rounded-md bg-gray-800 p-2 text-white shadow-sm hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"  fill="currentColor"><path d="m382-354 339-339q12-12 28-12t28 12q12 12 12 28.5T777-636L410-268q-12 12-28 12t-28-12L182-440q-12-12-11.5-28.5T183-497q12-12 28.5-12t28.5 12l142 143Z"/></svg>
                  </button>                  
                </div>
              </div>
              <div class="grid grid-cols-3 mt-4 gap-x-2">
                <div class="text-gray-500 mt-4 flex w-full flex-none gap-x-2">
                  <dt class="flex-none">
                    <span class="sr-only">Mail</span>
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M480-440q58 0 99-41t41-99q0-58-41-99t-99-41q-58 0-99 41t-41 99q0 58 41 99t99 41ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-46q-54-53-125.5-83.5T480-360q-83 0-154.5 30.5T200-246v46Z"/></svg>
                  </dt>
                  <dd class="text-sm leading-6 ">
                     
                    @foreach ($this->route->users as $user)
                      {{ $user->name }}
                    @endforeach
                  </dd>
                </div>
                <div class="text-gray-500 mt-4 flex w-full flex-none gap-x-2">
                  <dt class="flex-none">
                    <span class="sr-only">Mail</span>
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M360-300q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29ZM200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-40q0-17 11.5-28.5T280-880q17 0 28.5 11.5T320-840v40h320v-40q0-17 11.5-28.5T680-880q17 0 28.5 11.5T720-840v40h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Z"/></svg>
                  </dt>
                  <dd class="text-sm leading-6 ">{{ $this->route->created_at->format('d/m/y') }}</dd>
                </div>
                <div class="text-gray-500 mt-4 flex w-full flex-none gap-x-2">
                  <dt class="flex-none">
                    <span class="sr-only">Mail</span>
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M480-269 314-169q-11 7-23 6t-21-8q-9-7-14-17.5t-2-23.5l44-189-147-127q-10-9-12.5-20.5T140-571q4-11 12-18t22-9l194-17 75-178q5-12 15.5-18t21.5-6q11 0 21.5 6t15.5 18l75 178 194 17q14 2 22 9t12 18q4 11 1.5 22.5T809-528L662-401l44 189q3 13-2 23.5T690-171q-9 7-21 8t-23-6L480-269Z"/></svg>
                  </dt>
                  <dd class="text-sm leading-6 ">{{ $this->route->gradeFormated() }}</dd>
                </div>
                <div class="text-gray-500 mt-5 flex w-full flex-none gap-x-2">
                  <dt class="flex-none">
                    <span class="sr-only">Mail</span>
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M856-390 570-104q-12 12-27 18t-30 6q-15 0-30-6t-27-18L103-457q-11-11-17-25.5T80-513v-287q0-33 23.5-56.5T160-880h287q16 0 31 6.5t26 17.5l352 353q12 12 17.5 27t5.5 30q0 15-5.5 29.5T856-390ZM260-640q25 0 42.5-17.5T320-700q0-25-17.5-42.5T260-760q-25 0-42.5 17.5T200-700q0 25 17.5 42.5T260-640Z"/></svg>
                  </dt>
                  <dd class="text-sm leading-6 flex gap-x-1">
                    @foreach ($this->route->tags as $tag)
                    <span class=" mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                      <svg class="h-1.5 w-1.5 fill-gray-500" viewBox="0 0 6 6" aria-hidden="true">
                        <circle cx="3" cy="3" r="3"></circle>
                      </svg>
                      {{ $tag->name }}
                    </span>
                    @endforeach
                  </dd>
                </div>
              </div>
            </div>
          </div>
</div>