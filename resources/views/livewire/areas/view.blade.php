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
    public $mobile_first_open;

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
      if(!empty($this->route_id)){
        $this->route = Route::find($this->route_id);
        $this->mobile_first_open = true;

      }else{
        $this->route = Route::first();
        $this->mobile_first_open = false;
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
    return ['routes' => $routesQuery->paginate(10), 'logs' => Log::where('route_id', $this->route->id)->orderBy('created_at', 'desc')->take(3)->get(), 'lines' => $lines->get()];
    }

    public function selectSector($id){
      $this->selected_sector = $id;
    }
    public function selectLine($id){
      $this->selected_line = $id;
    }
}; ?>

<div class="grid grid-cols-3 mt-8 gap-4 pt-2">
  <div class="col-span-3 md:col-span-2 flex flex-col" 
    @if($this->area->type == 'bouldering') 
      x-data="{ hightlightedSector: 0, selectedSector: 0, selectSector(id){ this.selectedSector = id; $wire.selectSector(id); }, hightlightSector(id){ this.hightlightedSector = id; }, }" 
    @else 
      x-data="{ hightlightedRoute: 0, selectedRoute: 0, selectRoute(id){ this.selectedRoute = id; $wire.open_route(id); }, hightlightRoute(id){ this.hightlightedRoute = id; }, hightlightedLine: 0, selectedLine: 0, selectLine(id){ this.selectedLine = id; $wire.selectLine(id); }, hightlightLine(id){ this.hightlightedLine = id; }, }" > 
    @endif 
    
    <x-area.map /> 

    <x-area.filter :lines=$lines/>

    <x-area.table-routes :routes=$routes />

  </div>
  <div class='hidden md:block'>
    <x-area.card-route :logs=$logs key='card-md' :key_button="'button-md'"/>
  </div>

<div x-data="{ open: $wire.mobile_first_open }" @open_modal.window="open=true" class="relative md:hidden">
  <!-- Drawer Toggle Button -->

  <!-- Drawer -->
  <div style='display: none;'
      x-show="open" 
      x-transition:enter="transition ease-out duration-300" 
      x-transition:enter-start="translate-y-full" 
      x-transition:enter-end="translate-y-0" 
      x-transition:leave="transition ease-in duration-300" 
      x-transition:leave-start="translate-y-0" 
      x-transition:leave-end="translate-y-full" 
      class="fixed bottom-0 left-0 right-0 z-40 bg-white shadow-lg rounded-t-lg overflow-hidden">
      
      <!-- Drawer Header -->
      <div class="flex justify-between items-center p-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">{{ __('Route Details') }}</h2>
          <button 
              @click="open = false" 
              class="text-gray-500 hover:text-gray-700 focus:outline-none">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
          </button>
      </div>

      <!-- Drawer Content -->
      <div class="p-4 overflow-y-auto max-h-[75vh]">
          <x-area.card-route :logs="$logs" key='card-sm' :key_button="'button-sm'"/>
      </div>
  </div>
</div>