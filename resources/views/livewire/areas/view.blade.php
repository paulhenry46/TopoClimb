<?php

use Livewire\Volt\Component;
use App\Models\Area;
use App\Models\Site;
use App\Models\Line;
use App\Models\Route;
use App\Models\Tag;
use App\Models\Log;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
new class extends Component {
  use WithPagination;

  
    public Area $area;
    public Site $site;
    #[Url(keep: true)]
    public string $route_id ='';
    public Route $route;
    public array $schema_data = [];
    public $cotations = [];
    public $tags_available;
    public bool $new;

    public $selected_sector;
    public $selected_line;
    public array $tags_choosen;
    public array $tags_id;
    public $search;
    public int $cotation_from;
    public int $cotation_to;
    public $user_state;
    public $mobile_first_open;
    public $filtered_routes;

    private $routes_query;
    private $available_lines;


  public function open_route($id){
    $this->route = Route::find($id);
    $this->route_id = $id;
    $this->updateRoutesQuery();
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
          $data = ['id' => $sector->local_id,
                    'name' => $sector->name,
                   'paths' => Storage::get('paths/site-'.$this->site->id.'/area-'.$this->area->id.'/sector-'.$sector->id.'/edited/common_paths.svg'),
                   'bg' => Storage::url('plans/site-'.$this->site->id.'/area-'.$this->area->id.'/sector-'.$sector->id.'/schema')
                  ];
            array_push($this->schema_data['data'], $data);
            array_push($this->schema_data['sectors'], $sector->local_id);
          }
      }
        $this->cotations = config('climb.default_cotation');
        
      $tags_temp = Tag::all()->pluck('id', 'name')->toArray();
      $tags = [];
      foreach($tags_temp as $name => $key){
      $tags[] = ['name' => $name, 'id' => $key];
      }
      $this->tags_available = $tags;
      $this->tags_id = [];
      $this->cotation_to = 0;
      $this->cotation_from = 0;
      $this->new = false;
      if(!empty($this->route_id)){
        $this->route = Route::find($this->route_id);
        $this->mobile_first_open = true;

      }else{
        $this->route = Route::where(function($query) {
      $query->whereNull('removing_at')
          ->orWhere('removing_at', '>', now());
      })->whereIn('line_id', Line::whereIn('sector_id', $this->area->sectors()->pluck('id'))->pluck('id'))->first();
        $this->mobile_first_open = false;
      }
      
      $this->user_state = 'all';
      $this->selected_line = 0;
      //dump($this->route);
      $this->updateRoutesQuery();
    }

    public function updated($property, $value){
      if(true){
         $this->updateRoutesQuery();
        }
    }

    private function updateRoutesQuery(){
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
        $this->available_lines = $lines;
        
        //return $routes;
        $this->routes_query = Route::whereIn('line_id', $lines_selected)
        ->where(function($query) {
          $query->whereNull('removing_at')
              ->orWhere('removing_at', '>', now());
        })
        ->when($this->search, function($query, $search) {
            return $query->where('name', 'LIKE', "%{$this->search}%");
        })
        ->when($this->cotation_to != 0, function($query, $cotation) {
            return $query->where('grade', '<=', $this->cotation_to);
        })
        ->when($this->cotation_from != 0, function($query, $cotation) {
            return $query->where('grade', '>=', $this->cotation_from);
        })
        ->when($this->new, function($query, $cotation) {
            return $query->where('created_at', '>=', now()->subDays(7));
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
        $this->filtered_routes = $this->routes_query->pluck('id');
    }

    public function with(){
      return ['routes' => $this->routes_query->paginate(10), 'logs' => Log::where('route_id', $this->route->id)->orderBy('created_at', 'desc')->take(3)->get(), 'lines' => $this->available_lines->get(), 'route'=> $this->route];
    }

    public function selectSector($id){
      $this->selected_sector = $id;
    }
    public function selectLine($id){
      $this->selected_line = $id;
    }
}; ?>
<div class="grid grid-cols-3 md:mt-8 gap-4 md:pt-2">
  <div class="col-span-3 md:col-span-2 flex flex-col" 
    @if($this->area->type == 'bouldering') 
      x-data="{ hightlightedSector: 0, selectedSector: 0, selectSector(id){ this.selectedSector = id; $wire.selectSector(id); }, hightlightSector(id){ this.hightlightedSector = id; }, }" 
    @else 
      x-data="{ hightlightedRoute: 0, 
            selectedRoute: 0, 
            selectRoute(id){ this.selectedRoute = id; $wire.open_route(id); }, 
            hightlightRoute(id){ this.hightlightedRoute = id; }, 
            hightlightedLine: 0, 
            selectedLine: 0, 
            selectLine(id){ this.selectedLine = id; $wire.selectLine(id); }, 
            hightlightLine(id){ this.hightlightedLine = id; }, 
            filtered_routes : $wire.entangle('filtered_routes'),
            }" > 
    @endif 
    
    <x-area.map /> 

    <x-area.filter :lines=$lines/>

    <x-area.table-routes :routes=$routes />

  </div>

  <div class='hidden md:block'>
    <x-area.card-route :logs=$logs key='card-md' :key_button="'button-md'"/>
  </div>

<div x-data="{ open: $wire.mobile_first_open }" @open_modal.window="open=true" class="relative md:hidden">
  <!-- Drawer -->
  <div x-cloak
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
              class="cursor-pointer text-gray-500 hover:text-gray-700 focus:outline-none">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
          </button>
      </div>

      <!-- Drawer Content -->
      <div class="p-4 overflow-y-auto max-h-[75vh]">
          <x-area.card-route :logs=$logs key='card-sm' :key_button="'button-sm'"/>
      </div>
  </div>
  <!--hdhf/-->
</div>