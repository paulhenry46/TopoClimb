<?php

use Livewire\Volt\Component;
use App\Models\Area;
use App\Models\Site;
use App\Models\Sector;
use App\Models\Line;
use App\Models\Route as ModelRoute;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
new class extends Component {
  use WithFileUploads;

    public Area $area;
    public Site $site;
    public ModelRoute $route;
    public $edit;
    public $url;
    public $path;
    public $file_content;

    public function mount(Site $site, Area $area, ModelRoute $route){
      $this->site = $site;
      $this->area = $area;
      $this->route = $route;
      $this->url = Storage::url('photos/site-'.$route->line->sector->area->site->id.'/area-'.$route->line->sector->area->id.'/route-'.$route->id.'');
      

      if(Storage::exists('photos/site-'.$this->site->id.'/area-'.$this->area->id.'/circle-'.$this->route->id.'/.svg')){
        $this->file_content = str_replace(array("\r", "\n"), '', Storage::get('photos/site-'.$this->site->id.'/area-'.$this->area->id.'/circle-'.$this->route->id.'.original.svg'));
      }
      if($this->route->id == session('route_creating')){
        $this->edit = false;
      }else{
        $this->edit = true;
      }
    }

    public function save(){
      //dd($this->path);
     
      $filePath = 'photos/site-'.$this->site->id.'/area-'.$this->area->id.'/route-'.$this->route->id.'.svg';
      Storage::put('photos/site-'.$this->site->id.'/area-'.$this->area->id.'/route-'.$this->route->id.'.original.svg', $this->path);

      $input_file_path = Storage::path('photos/site-'.$this->site->id.'/area-'.$this->area->id.'/route-'.$this->route->id.'.original.svg');
      $output_file_path= storage_path('app/public/'.$filePath.'');
      
      $result = Process::run('inkscape --export-type=svg -o '.$output_file_path.' --export-area-drawing --export-plain-svg '.$input_file_path.'');
      //dd($result);
      $xml = simplexml_load_string(Storage::get($filePath));
      $dom = new DOMDocument('1.0');
      $dom->preserveWhiteSpace = false;
      $dom->formatOutput = true;
      $dom->loadXML($xml->asXML());

      $xpath = new DOMXPath($dom);
      $item = $xpath->query("//*[@id='area']")->item(0);
      $item->remove();

      Storage::put($filePath, $dom->saveXML());
      //dd(Storage::get($filePath));

      if($this->route->id == session('route_creating')){
        session()->forget('route_creating');
      }

      $this->redirectRoute('admin.sectors.manage', ['site' => $this->site->id, 'area' => $this->area->id], navigate: true);
    }
}; ?>

<div>
  @if(!$this->edit)
  <nav aria-label="Progress" class="p-4">
    <ol role="list" class="space-y-4 md:flex md:space-x-8 md:space-y-0"> 
      <li class="md:flex-1">
        <!-- Current Step -->
        <a class="flex flex-col border-l-4 border-indigo-600 py-2 pl-4 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4" aria-current="step">
          <span class="text-sm font-medium text-gray-500">{{__('Step')}} 1</span>
          <span class="text-sm font-medium">{{__('Add informations')}}</span>
        </a>
      </li>
      <li class="md:flex-1">
        <!-- Upcoming Step -->
        <a class="group flex flex-col border-l-4 border-indigo-600 py-2 pl-4 hover:border-indigo-500 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4">
          <span class="text-sm font-medium text-gray-500 group-hover:text-gray-700">{{__('Step')}} 2</span>
          <span class="text-sm font-medium">{{__('Draw path')}}</span>
        </a>
      </li>
      <li class="md:flex-1">
        <!-- Upcoming Step -->
        <a class="group flex flex-col border-l-4 border-indigo-600 py-2 pl-4 hover:border-indigo-500 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4">
          <span class="text-sm font-medium text-gray-500 group-hover:text-gray-700">{{__('Step')}} 3</span>
          <span class="text-sm font-medium">{{__('Upload photo')}}</span>
        </a>
      </li>
      <li class="md:flex-1">
        <!-- Upcoming Step -->
        <a class="group flex flex-col border-l-4 border-indigo-600 py-2 pl-4 hover:border-indigo-500 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4">
          <span class="text-sm font-medium text-indigo-600 group-hover:text-indigo-700">{{__('Step')}} 4</span>
          <span class="text-sm font-medium">{{__('Identify start')}}</span>
        </a>
      </li>
    </ol>
  </nav>
  @endif
  <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="px-4 sm:px-6 lg:px-8 py-8">
      <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
          <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Identify start zone')}}</h1>
          <p class="mt-2 text-sm text-gray-700">{{$this->route->name}} ({{__('Line')}} {{$this->route->line->id}})</p>
        </div>
      </div>
              
              <div x-data="{message: ''}" @svg_sent.window="$wire.path = $event.detail.message"
              @sent_to_wire.window="$wire.save()">
                <span x-text="message"></span>
        <script type="text/javascript" src="http://127.0.0.1:8000/dist/paper-full.js"></script>
        <script src='https://cdnjs.cloudflare.com/ajax/libs/acorn/8.8.2/acorn.js'></script>
        

        @if($this->file_content)
        <script type="text/paperscript" canvas="myCanvas">
          const diameter = 70;
          const num_line = {{$this->route->id}};
          const color = '{{$this->route->color}}';

          var raster = new Raster('photo');
          raster.position = view.center;
          project.activeLayer.fitBounds(view.bounds);
          view.bounds =  raster.internalBounds;

          rectangle = new Path.Rectangle(raster.bounds);
          rectangle.name = 'area';
          rectangle.strokeWidth = 1;
          rectangle.strokeColor = 'black';
          rectangle.fillColor = 'red';
          rectangle.opacity = 0;
          
          item = project.importSVG('{!! $this->file_content !!}');
          item.position = view.center;
          item.opacity = 0.5;
          item.fitBounds(view.bounds);

          function createTruc(event) {
              point_cool = event.point;

              var circle = new Path.Circle({
                  center: point_cool,
                  radius: diameter / 2,
                  strokeColor : color,
                  strokeWidth : 7,
                  opacity : 0.5
              });
              circle.name = 'circle_' + num_line;
              group = new Group([circle]);
              group.name = 'group_' + num_line;
          }

          function onMouseDown(event) {
                  project.activeLayer.removeChildren();
                  createTruc(event);
          }


          document.addEventListener('terminated', () => {
            raster.remove();
            var evt = new CustomEvent('svg_sent', {
              detail: {
                  message: project.exportSVG({
                      asString: true
                  }),
              }
          });
          window.dispatchEvent(evt);
          
            var evt = new CustomEvent('sent_to_wire', {
                detail: {
                    message: 'ok',
                }
            });
            window.dispatchEvent(evt);
        })
        </script>
        @else
        <script type="text/paperscript" canvas="myCanvas">
          const diameter = 70;
          const num_line = {{$this->route->id}};
          const color = '{{$this->route->color}}';

          var raster = new Raster('photo');
          raster.position = view.center;
          project.activeLayer.fitBounds(view.bounds);
          view.bounds =  raster.internalBounds;

          rectangle = new Path.Rectangle(raster.bounds);
          rectangle.name = 'area';
          rectangle.strokeWidth = 1;
          rectangle.strokeColor = 'black';
          rectangle.fillColor = 'red';
          rectangle.opacity = 0;

          var group = null;


          function createTruc(event) {
              point_cool = event.point;

              var circle = new Path.Circle({
                  center: point_cool,
                  radius: diameter / 2,
                  strokeColor : color,
                  strokeWidth : 7,
                  opacity : 0.5
              });
              circle.name = 'circle_' + num_line;
              group = new Group([circle]);
              group.name = 'group_' + num_line;
              return group;
          }

          function onMouseDown(event) {
            if(group){
                  group.remove();
          }
                group = createTruc(event);
              }

          document.addEventListener('terminated', () => {
            raster.remove();
            var evt = new CustomEvent('svg_sent', {
              detail: {
                  message: project.exportSVG({
                      asString: true
                  }),
              }
          });
          window.dispatchEvent(evt);
          
            var evt = new CustomEvent('sent_to_wire', {
                detail: {
                    message: 'ok',
                }
            });
            window.dispatchEvent(evt);
        })
        </script>
        @endif
        <canvas id="myCanvas" class="min-h-full min-w-full"></canvas>
          <img  id="photo" class="hidden rounded-lg" src="{{$url}}">
      </div>
    </div>
    <div class="flex-shrink-0 border-t border-gray-200 px-4 py-5 sm:px-6">
      <div class="flex justify-end space-x-3">
        <x-secondary-button type="button">{{__('Cancel')}}</x-secondary-button>
        <x-button @click="$dispatch('terminated')">{{__('Continue')}}</x-button>
      </div>
    </div>
  </div>
</div>