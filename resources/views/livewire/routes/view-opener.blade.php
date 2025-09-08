<?php

use Livewire\Volt\Component;
use App\Models\Area;
use App\Models\Site;
use App\Models\Log;
use App\Models\Line;
use App\Models\Route;
use App\Models\Tag;
use App\Models\Sector;
use Carbon\Carbon;
use App\Jobs\RouteColorChanged;

use Livewire\Attributes\On;
use Livewire\Attributes\Url;

new class extends Component {

    #[Url(keep: true)]
    public string $route_id ='';
    public Route $route;
    public Area $area;
    public $mobile_first_open;

     public $all_routes;
    public $lines;
    public $lines_available;

    public $modal_open;
    public $modal_title;
    public $modal_subtitle;
    public $modal_submit_message;

    #[Validate('required')]
    public $name;
    #[Validate('string|nullable')]
    public $comment;
    #[Validate('required')]
    public $line;
    #[Validate('required|regex:/[3-9][abc][+]?/')]
    public string $grade;
    #[Validate('required')]
    public string $color;
    #[Validate('required')]
    public $date;

    public $sector;
    public $sectors;
    public $data_routes_week;

    public $opener_search;
    public $opener_selected  = [];
    public $error_user;
    public $slug;
    public array $tags_id;
    public array $tags_choosen;
    public $tags_available;
    public $id_editing;
    public $gradeUser;

    public function mount( Site $site, Area $area){
        $lines = Line::whereIn('sector_id', $this->area->sectors->pluck('id'))->get();
        $this->area = $area;
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

     if(auth()->user()->can('lines-sectors.'.$area->site->id)){
        $this->all_routes == true;
      }else{
        $this->all_routes == false;
      }

      $this->modal_title = __('Editing route ').$this->name;
      $this->modal_subtitle = __('Check the informations about this route.');
      $this->modal_submit_message = __('Edit');
      
      $this->lines = $lines;
      $this->lines_available = $lines;
      $this->line = null;
      $this->site = $site;

      $tags_temp = Tag::all()->pluck('id', 'name')->toArray();

      $tags = [];
     foreach($tags_temp as $name => $key){
      array_push($tags, ['name' => $name, 'id' => $key]);
     }
      $this->sectors = Sector::where('area_id', $this->area->id)->get();
      $this->sector = Sector::where('area_id', $this->area->id)->first()->id;
     

     $this->tags_available = $tags;
     $this->getDataForGraph();
     $this->gradeAccordingToUsers();
    }

    private function gradeAccordingToUsers(){
        $grades = $this->route->logs->pluck('grade')->toArray();
        if(count($grades)>0){
             $average = array_sum($grades) / count($grades);
             $this->gradeUser = config('climb.default_cotation_reverse')[$this->findClosest(config('climb.default_cotation_reverse'), $average)];
        }else{
            $this->gradeUser = $this->route->gradeFormated();
        }
    }
    private function findClosest($array, $target) {
        $closest = null;
        $minDiff = 100;

        foreach ($array as $value => $key) {
            $diff = abs($target - $value);
            if ($diff < $minDiff) {
                $minDiff = $diff;
                $closest = $value;
            }
        }

        return $closest;
}

    #[On('route-changed')] 
    public function readRoute($id)
    {
        $this->route  = Route::find($id);
        $this->route_id = $id;
        $this->getDataForGraph();
        $this->gradeAccordingToUsers();
    }

    public function with(){
      return ['logs' => Log::where('route_id', $this->route->id)->orderBy('created_at', 'desc')->take(3)->get(), 'route'=> $this->route];
    }


    public function updated($property, $value)
    {
        if ($property === 'sector') {
            $this->line = Line::where('sector_id', $this->sector)->first()->id;
            $this->lines_available = Line::where('sector_id', $this->sector)->get();
        }
    }

    public function saveRoute()
    {
      if($this->all_routes or $this->route->users()->where('user_id', auth()->id())->exists()){
        $this->validate(); 
        $color = $this->route->color;
      $this->route->slug = Str::slug($this->name, '-');
      $this->route->name = $this->name;
      $this->route->comment = $this->comment;
      $this->route->line_id = $this->line;
      $this->route->grade = $this->gradeToInt($this->grade);
      $this->route->color = $this->color;
      $this->route->created_at = $this->date;
      $this->route->save();
      $this->route->tags()->sync($this->tags_id);
      $temp_openers_id = [];

      foreach ($this->opener_selected as $key => $opener) {
        array_push($temp_openers_id, $opener['id']);
      }
      $this->route->users()->sync($temp_openers_id);
      if($color != $this->color){
        RouteColorChanged::dispatchSync($this->site, $this->area, $this->route);
      }
      $this->dispatch('action_ok', title: 'Route saved', message: 'Your modifications has been registered !');
        
        $this->modal_open = false;
        $this->render();
      }
      
    }

    public function open_item($id){
      $item = Route::find($id);
      $this->route = $item;
      $this->name = $item->name;
      $this->comment = $item->comment;
      $this->line = $item->line_id;
      $this->sector = $item->line->sector_id;
      $this->lines_available = Line::where('sector_id', $this->sector)->get();
      $this->grade = $this->IntToGrade($item->grade);
      $this->color = $item->color;
      $this->date = $item->created_at->format('Y-m-d');

      $this->modal_open = true;

      $tags_temp = $this->route->tags()->pluck('tags.id', 'name')->toArray();
    

      $tags = [];
      $tags_id = [];
     foreach($tags_temp as $name => $key){
      array_push($tags, ['name' => $name, 'id' => $key]);
      array_push($tags_id, $key);
     }

     $this->tags_id = $tags_id;
     $this->tags_choosen = $tags;
     $this->opener_selected = [];
     $users_temp = $this->route->users;
     foreach($users_temp as $user){
      array_push($this->opener_selected, ['name' => $user->name, 'id' => $user->id, 'url' => $user->profile_photo_url]);
      
    }
  }

    public function remove_current_now(){
      $item = $this->route;
      $item->removing_at = Carbon::today()->toDateTime();
      $item->save();
      $this->dispatch('action_ok', title: 'Route deleted', message: 'Your modifications has been registered !');
      $this->render();
    }

    public function remove_current($date){
      if($date == 'today'){
        $date = Carbon::today()->toDateTime();
      }

      Route::whereIn('id', $this->route->id)->update(['removing_at' => $date]);
    }



    public function add_opener(){
      $user = User::where('name', $this->opener_search)->first();
      if($user != null){
        array_push($this->opener_selected, ['name' => $user->name, 'id' => $user->id, 'url' => $user->profile_photo_url]);
        $this->opener_search = null;
        $this->error_user = false;
      }else{
        $this->error_user = true;
      }

    }

    public function remove_opener($id)
    {
      foreach ($this->opener_selected as $subKey => $opener){
        if($opener['id'] == $id){
          unset($this->opener_selected[$subKey]);
        }
      }
    }

    protected function gradeToInt($grade){
        $array = config('climb.default_cotation');
        return $array[$grade];
    }

    protected function intToGrade($int){
        $grades = config('climb.default_cotation_reverse');

        return $grades[$int] ?? null;
    }

    private function getDataForGraph(){
        $weeks = collect();
        for ($i = 7; $i >= 0; $i--) {
            $start = now()->subWeeks($i)->startOfWeek();
            $end = now()->subWeeks($i)->endOfWeek();
            $count = $this->route->logs()
                ->whereBetween('created_at', [$start, $end])
                ->count();
            $weeks->push([
                'label' => $start->format('d/m'),
                'count' => $count,
            ]);
        }
        $this->data_routes_week = [
                'labels' => $weeks->pluck('label'),
                'datasets' => [
                    [
                        'label' => __('Number of logs by week'),
                        'data' => $weeks->pluck('count'),
                        'backgroundColor' => 'rgba(0, 0, 0, 0.2)',
                        'borderColor' => 'rgba(0, 0, 0, 1)',
                        'borderWidth' => 1,
                    ],
                ],
            ];
    }
   
}; ?>
<div>
<div>
  <div class='hidden md:block'>
    <x-area.card-route-opener :logs=$logs key='card-md' :key_button="'button-md'"/>
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
</div>
</div>