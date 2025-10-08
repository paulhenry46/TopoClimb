<?php

use Livewire\Volt\Component;
use App\Models\Area;
use App\Models\Site;
use App\Models\Line;
use App\Models\Route;
use App\Models\Tag;
use App\Models\Log;
use Livewire\WithPagination;
new class extends Component {
  use WithPagination;

  
    public Area $area;
    public Site $site;
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


    public function mount(Area $area){
      $this->area = $area;
      $this->site = $this->area->site;
      if($area->type == 'bouldering'){
        foreach ($area->sectors as $sector) {
            array_push($this->schema_data, Storage::get('plans/site-'.$this->site->id.'/area-'.$this->area->id.'/edited/users.svg'));
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
        $this->cotations = $this->site->cotations();/* config('climb.default_cotation');*/
        
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

       $grades = $this->site->cotations_reverse();
      $routes = $this->routes_query->paginate(10);

     // Transform each route to add gradeFormated property
    $routes->getCollection()->transform(function ($route) use ($grades) {
        // Format the grade using site cotations (or your own logic)
        $route->gradeFormated = $grades[$route->grade] ?? $route->grade;
        return $route;
    }); 

      return ['routes' => $routes, 'lines' => $this->available_lines->get()];
    }

    public function selectSector($id){
      $this->selected_sector = $id;
      $this->updateRoutesQuery();
    }
    public function selectLine($id){
      $this->selected_line = $id;
      $this->updateRoutesQuery();
    }
}; ?>
  <div class="col-span-3 md:col-span-2 flex flex-col" 
    @if($this->area->type == 'bouldering') 
      x-data="{ selectedRoute: 0, 
                hightlightedRoute: 0,
                hightlightRoute(id){ this.hightlightedRoute = id; }, 
                selectRoute(id){ this.selectedRoute = id; $dispatch('route-changed', { id: id}) }, 
                hightlightedSector: 0, 
                selectedSector: 0, 
                selectSector(id){ this.selectedSector = id; $wire.selectSector(id); }, 
                hightlightSector(id){ this.hightlightedSector = id; }, 
                }" 
    @else 
      x-data="{ hightlightedRoute: 0, 
            selectedRoute: 0, 
            selectRoute(id){ this.selectedRoute = id; $dispatch('route-changed', { id: id}) }, 
            hightlightRoute(id){ this.hightlightedRoute = id; }, 
            hightlightedLine: 0, 
            selectedLine: 0, 
            selectLine(id){ this.selectedLine = id; $wire.selectLine(id); }, 
            hightlightLine(id){ this.hightlightedLine = id; }, 
            filtered_routes : $wire.entangle('filtered_routes'),
            }" > 
    @endif 
    
    <x-area.map /> 

    <x-area.filter :lines=$lines :admin='false'/>

    <x-area.table-routes :routes=$routes />

  </div>
