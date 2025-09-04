<?php

use Livewire\Volt\Component;
use App\Models\Area;
use App\Models\Site;
use App\Models\Log;
use App\Models\Line;
use App\Models\Route;

use Livewire\Attributes\On;
use Livewire\Attributes\Url;

new class extends Component {

    #[Url(keep: true)]
    public string $route_id ='';
    public Route $route;
    public Area $area;
    public $mobile_first_open;

    public function mount($area){
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
    }

    #[On('route-changed')] 
    public function readRoute($id)
    {
        $this->route  = Route::find($id);
        $this->route_id = $id;
    }

    public function with(){
      return ['logs' => Log::where('route_id', $this->route->id)->orderBy('created_at', 'desc')->take(3)->get(), 'route'=> $this->route];
    }
   
}; ?>
<div>
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
</div>