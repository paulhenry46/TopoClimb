<?php

use Livewire\Volt\Component;
use App\Models\Area;
use App\Models\Site;
use App\Models\Sector;
use App\Models\Line;
use App\Models\Route as ModelRoute;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use App\Jobs\CropPhotoOfRoute;
use Livewire\Attributes\Computed;
new class extends Component {

    public Area $area;
    public Site $site;
    public ModelRoute $route;
    public $file_content;
    public float $x;
    public float $y;
    public float $w;
    public float $h;
    public float $t_w;
    public float $t_h;
    public array $transform;

    public function mount(Site $site, Area $area, ModelRoute $route){
      if (!$route->users()->where('user_id', auth()->id())->exists() && !auth()->user()->can('lines-sectors' . $site->id)) {
        abort(403, 'You are not authorized to access this route.');
    }
      $this->site = $site;
      $this->area = $area;
      $this->route = $route;

      if(Storage::exists('photos/site-'.$this->site->id.'/area-'.$this->area->id.'/route-'.$this->route->id.'.svg')){
        $this->file_content = Storage::get('photos/site-'.$this->site->id.'/area-'.$this->area->id.'/route-'.$this->route->id.'.svg');
      }
      
      $xml = simplexml_load_string($this->file_content);
      $dom = new DOMDocument('1.0');
      $dom->preserveWhiteSpace = false;
      $dom->formatOutput = true;
      $dom->loadXML($xml->asXML());

      foreach ($dom->getElementsByTagName('svg') as $item) {
          $this->t_w = $item->getAttribute('width');
          $this->t_h = $item->getAttribute('height'); 
      }
      $xpath = new DOMXPath($dom);
      $item = $xpath->query("//*[@id='g1']")->item(0);
      $transform = $item->getAttribute("transform");

      $matches = [];
    if (preg_match('/translate\(([^,]+),\s*([^)]+)\)/', $transform, $matches)) {
        $this->transform['x'] = (float) $matches[1];
        $this->transform['y'] = (float) $matches[2];
    }
  }

    public function save(){
      CropPhotoOfRoute::dispatchSync($this->site, $this->area, $this->route, ['x' => $this->x, 'y' => $this->y, 'h' => $this->h, 'w' => $this->w, 't_w' => $this->t_w, 't_h' => $this->t_h], $this->transform);

      if($this->route->id == session('route_creating')){
        session()->forget('route_creating');
      }

      $this->redirectRoute('admin.sectors.manage', ['site' => $this->site->id, 'area' => $this->area->id], navigate: true);
    }
}; ?>

<div>
  <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="px-4 sm:px-6 lg:px-8 py-8">
      <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
          <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Finish !')}}</h1>
          <p class="mt-2 text-sm text-gray-700">{{$this->route->name}} ({{__('Line')}} {{$this->route->line->id}})</p>
        </div>
      </div>
              
              <div x-data="{message: '', show :true,
              getBBox(){BBox = document.getElementById('circle_{{$this->route->id}}').getBBox(); 
                        $wire.x = BBox.x ; 
                        $wire.y = BBox.y ; 
                        $wire.w = BBox.width ; 
                        $wire.h = BBox.height ;
                        $wire.save() ;
                        this.show = false; }, }">
                <span x-init="getBBox()"></span>
                <div x-show='show' class='opacity-0'>
                    {!! $this->file_content !!}
                  </div>
        
      </div>
    </div>
    
    <div class="shrink-0 border-t border-gray-200 px-4 py-5 sm:px-6">
      <div class="flex justify-end space-x-3">
        <x-secondary-button type="button">{{__('Cancel')}}</x-secondary-button>
        <x-button @click="$dispatch('terminated')">{{__('Continue')}}</x-button>
      </div>
    </div>
  </div>
</div>