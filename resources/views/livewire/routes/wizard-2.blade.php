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
use App\Jobs\ProcessPathOfRoute;

# Wizard for step 2 : draw path of route
new class extends Component {
  use WithFileUploads;

    public Area $area;
    public Site $site;
    public ModelRoute $route;
    public $edit;
    public $url;
    public $path;
    public $file_content;

    public function mount(Site $site, Area $area, ModelRoute $route, $edit = false){
      if (!$route->users()->where('user_id', auth()->id())->exists() && !auth()->user()->can('lines-sectors' . $site->id)) {
        abort(403, 'You are not authorized to access this route.');
    }

      $this->site = $site;
      $this->area = $area;
      $this->route = $route;
      $this->url = Storage::url('plans/site-'.$route->line->sector->area->site->id.'/area-'.$route->line->sector->area->id.'/sector-'.$route->line->sector->id.'/schema');
      if($this->area->type == 'bouldering'){
        $this->redirectRoute('admin.routes.photo', ['site' => $this->site->id, 'area' => $this->area->id, 'route' => $this->route->id], navigate: true);
      }
      $this->edit = $edit;
      if(Storage::exists('paths/site-'.$this->site->id.'/area-'.$this->area->id.'/route-'.$this->route->id.'.svg')){
        $this->file_content = str_replace(array("\r", "\n"), '', Storage::get('paths/site-'.$this->site->id.'/area-'.$this->area->id.'/route-'.$this->route->id.'.original.svg'));
      }
      if($this->route->id == session('route_creating')){
        $this->edit = false;
      }else{
        $this->edit = true;
      }
    }

    public function save(){
      ProcessPathOfRoute::dispatchSync($this->area, $this->route, $this->path);

      $this->redirectRoute('admin.routes.photo', ['site' => $this->site->id, 'area' => $this->area->id, 'route' => $this->route->id], navigate: true);
    }
}; ?>

<div>
  @if(!$this->edit)
  <nav aria-label="Progress" class="p-4">
    <ol role="list" class="space-y-4 md:flex md:space-x-8 md:space-y-0"> 
      <li class="md:flex-1">
        <!-- Current Step -->
        <a class="flex flex-col border-l-4 border-gray-600 py-2 pl-4 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4" aria-current="step">
          <span class="text-sm font-medium text-gray-500">{{__('Step')}} 1</span>
          <span class="text-sm font-medium">{{__('Add informations')}}</span>
        </a>
      </li>
      <li class="md:flex-1">
        <!-- Upcoming Step -->
        <a class="group flex flex-col border-l-4 border-gray-600 py-2 pl-4 hover:border-gray-500 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4">
          <span class="text-sm font-medium text-gray-600 group-hover:text-gray-700">{{__('Step')}} 2</span>
          <span class="text-sm font-medium">{{__('Draw path')}}</span>
        </a>
      </li>
      @if($this->area->type == 'trad')
      <li class="md:flex-1">
        <!-- Upcoming Step -->
        <a class="group flex flex-col border-l-4 border-gray-200 py-2 pl-4 hover:border-gray-300 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4">
          <span class="text-sm font-medium text-gray-500 group-hover:text-gray-700">{{__('Step')}} 3</span>
          <span class="text-sm font-medium">{{__('Upload photo')}}</span>
        </a>
      </li>
      @endif
    </ol>
  </nav>
  @endif
  <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="px-4 sm:px-6 lg:px-8 py-8">
      <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
          <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Draw path of the route')}}</h1>
          <p class="mt-2 text-sm text-gray-700">{{$this->route->name}}</p>
        </div>
      </div>
              
              <div x-data="{message: ''}" @svg_sent.window="$wire.path = $event.detail.message"
              @sent_to_wire.window="$wire.save()">
                <span x-text="message"></span>
                <script type="text/javascript" src="{{ asset('dist/paper-full.js') }}"></script>
        <script src="{{ asset('dist/acorn.js') }}"></script>
        

        @if($this->file_content)
        <script type="text/paperscript" canvas="myCanvas">
          var path;
          const strokeWidth = 3;
          const strokeColor = '{{$this->route->colorToHex()}}';
          var group;
          const num_line = '{{$this->route->id}}';

          var raster = new Raster('schema');
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


          function onMouseDown(event) {
            if (path) {
              path.remove();
            }
            path = new Path({
              segments: [event.point],
              strokeColor: strokeColor,
              strokeWidth : strokeWidth,
              name : 'path_' + num_line
            });
          }

          function onMouseDrag(event) {
            if(rectangle.hitTest(event.point)!= null){

            path.add(event.point);
            }
          }

          function onMouseUp(event) {
            path.simplify(10);
            group = new Group([path]);
              group.name = 'id_' + num_line;
          }


          document.addEventListener('terminated', () => {
            raster.remove();
            item.remove();
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
          var path;
          const strokeWidth = 7;
          const strokeColor = '{{$this->route->colorToHex()}}';
          var group;
          const num_line = '{{$this->route->id}}';

          var raster = new Raster('schema');
          raster.position = view.center;
          project.activeLayer.fitBounds(view.bounds);
          view.bounds =  raster.internalBounds;

          rectangle = new Path.Rectangle(raster.bounds);
          rectangle.name = 'area';
          rectangle.strokeWidth = 1;
          rectangle.strokeColor = 'black';
          rectangle.fillColor = 'red';
          rectangle.opacity = 0;

          function onMouseDown(event) {
            if (path) {
              path.remove();
            }
            path = new Path({
              segments: [event.point],
              strokeColor: strokeColor,
              strokeWidth : strokeWidth,
              name : 'path_' + num_line
            });
          }

          function onMouseDrag(event) {
            if(rectangle.hitTest(event.point)!= null){

            path.add(event.point);
            }
          }

          function onMouseUp(event) {
            path.simplify(10);
            group = new Group([path]);
              group.name = 'id_' + num_line;
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
          <img  id="schema" class="hidden rounded-lg" src="{{$url}}">
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