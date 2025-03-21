<?php

use Livewire\Volt\Component;
use App\Models\Area;
use App\Models\Site;
use App\Models\Line;
use App\Models\Route;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;
new class extends Component {
  use WithPagination, WithFileUploads;

  
    public Area $area;
    public Site $site;
    public array $url_map = [];

    public $selected_sector;

    public function mount(Area $area){
      $this->area = $area;
      $this->site = $this->area->site;
        foreach ($area->sectors as $sector) {
            array_push($this->url_map, Storage::get('plans/site-'.$this->site->id.'/area-'.$this->area->id.'/edited/admin.svg'));
        }

    }

    public function with(){
      if($this->selected_sector != null){
        $lines = Line::where('sector_id', $this->selected_sector)->pluck('id');
      }else{
        $lines = Line::whereIn('sector_id', $this->area->sectors()->pluck('id'))->pluck('id');
      }
      //return $routes;
      return ['routes' => Route::whereIn('line_id', $lines)->paginate(10)];
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
    <div class="bg-white overflow-hidden /*shadow-xl*/ sm:rounded-lg">
      <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="sm:flex sm:items-center">
          <div class="sm:flex-auto stroke-indigo-500">
            <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Filters')}}</h1>
            <p class="mt-2 text-sm text-gray-700">{{__('Map of the area with sectors and lines')}}</p>
            <div class="flex justify-center [&>*]:max-h-96 max-h-96 rounded-xl object-contain pt-4"> 
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
                  <td class="rounded-l-md text-xl text-center w-4 bg-{{$route->color}}-300 relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                    {{$route->grade}}
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
  <div class="flex flex-col">
    <x-site.infobox :site='$site' />
  </div>
</div>