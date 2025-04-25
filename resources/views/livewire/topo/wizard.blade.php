<?php

use Livewire\Volt\Component;
use App\Models\Area;
use App\Models\Site;
use App\Models\Sector;
use App\Models\Line;
use App\Models\User;
use App\Models\Tag;
use App\Models\Route;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use App\Jobs\ProcessMapForTopo;
new class extends Component {

    public Area $area;
    public Site $site;
    public $svg;
    public $url;

    public function mount(Site $site, Area $area){
      $this->site = $site;
      $this->area = $area;

     $this->url = Storage::disk('public')->url('plans/site-'.$this->site->id.'/area-'.$this->area->id.'/sectors-numbers.svg');
    }

    public function save(){
        ProcessMapForTopo::dispatchSync($this->site, $this->area, $this->svg);
    }

}; ?>

<div>
  <nav aria-label="Progress" class="p-4">
      <ol role="list" class="space-y-4 md:flex md:space-x-8 md:space-y-0">
          <li class="md:flex-1">
              <!-- Current Step --> <a class="flex flex-col border-l-4 border-indigo-600 py-2 pl-4 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4" aria-current="step"> <span class="text-sm font-medium text-indigo-600">{{__('Step')}} 1</span> <span class="text-sm font-medium">{{__('Edit map')}}</span> </a>
          </li>
          <li class="md:flex-1">
              <!-- Upcoming Step --> <a class="group flex flex-col border-l-4 border-gray-200 py-2 pl-4 hover:border-gray-300 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4"> <span class="text-sm font-medium text-gray-500 group-hover:text-gray-700">{{__('Step')}} 2</span> <span class="text-sm font-medium">{{__('Configure Topo')}}</span> </a>
          </li> @if($this->area->type == 'trad') <li class="md:flex-1">
              <!-- Upcoming Step --> <a class="group flex flex-col border-l-4 border-gray-200 py-2 pl-4 hover:border-gray-300 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4"> <span class="text-sm font-medium text-gray-500 group-hover:text-gray-700">{{__('Step')}} 3</span> <span class="text-sm font-medium">{{__('Save')}}</span> </a>
          </li> @endif
      </ol>

  </nav>
  <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="px-4 sm:px-6 lg:px-8 py-8">
      <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
          <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Draw path of the route')}}</h1>
          <p class="mt-2 text-sm text-gray-700"></p>
        </div>
      </div>
      <div class="rounded-md bg-indigo-50 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
              <path d="m424-408-86-86q-11-11-28-11t-28 11q-11 11-11 28t11 28l114 114q12 12 28 12t28-12l226-226q11-11 11-28t-11-28q-11-11-28-11t-28 11L424-408Zm56 328q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/>
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-indigo-800"> {{__('First, edit the map of the area')}}</h3>
            <div class="mt-2 text-sm text-indigo-700">
              <ul role="list" class="list-disc space-y-1 pl-5">
                <li>{{__('You can scale the width of number with + and - key.')}}</li>
                <li>{{__('When all is ok, click on continue button')}}</li>
                <li @click="$dispatch('toogleStroke')">{{ __('By clicking here, you can choose if the circle have stroke') }}</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
              
              <div x-data="{message: ''}" @svg_sent.window="$wire.svg = $event.detail.message"
              @sent_to_wire.window="$wire.save()">
                <span x-text="message"></span>
        <script type="text/javascript" src="http://127.0.0.1:8000/dist/paper-full.js"></script>
        <script src='http://127.0.0.1:8000/dist/acorn.js'></script>
        
        <script type="text/paperscript" canvas="myCanvas">
            console.log(project.activeLayer.importSVG('{{ $this->url }}'));
            console.log(project.activeLayer.exportJSON());
            var circle_stroke = false;
            var diameter = 30;
            var fontsize = 10
            var hitOptions = {
                segments: false,
                stroke: false,
                fill: true,
                tolerance: 5
            };
            
            function onKeyDown(event) {
                if (event.key == '+') {
                    diameter = diameter + 2;
                    for (var item of project.activeLayer.getItems({
                        class: Path
                    })) {
                        if(item.name.replace(/[0-9]/g, '') == 'circle_' ){
                            var box = new Path.Rectangle({
                                center: item.position,
                                size: [diameter, diameter],
                                fillColor: 'black'
                            });
                            item.fitBounds(box.bounds);
                            box.remove();
                        }  
                    }
                    for (var item of project.activeLayer.getItems({
                        class: PointText
                    })) {
                            var box = new Path.Rectangle({
                                center: item.position,
                                size: [diameter - 8, diameter - 8],
                                fillColor: 'black'
                            });
                            item.fitBounds(box.bounds);
                            box.remove();
                        
                    }
                }

               
            
                if (event.key == '-') {
                    diameter = diameter - 2;
                    for (var item of project.activeLayer.getItems({
                        class: Path
                    })) {
                        if(item.name.replace(/[0-9]/g, '') == 'circle_' ){
                            var box = new Path.Rectangle({
                                center: item.position,
                                size: [diameter, diameter],
                                fillColor: 'black'
                            });
                            item.fitBounds(box.bounds);
                            box.remove();
                        }    
                    }
                    for (var item of project.activeLayer.getItems({
                        class: PointText
                    })) {
                            var box = new Path.Rectangle({
                                center: item.position,
                                size: [diameter - 8, diameter - 8],
                                fillColor: 'black'
                            });
                            item.fitBounds(box.bounds);
                            box.remove();
                        
                    }
                }
                project.activeLayer.fitBounds(view.bounds);
            }

            document.addEventListener('toogleStroke', () => {
                console.log('ok');
                for (var item of project.activeLayer.getItems({
                    class: Path
                })) {
                    if(item.name.replace(/[0-9]/g, '') == 'circle_' ){
                        if(!circle_stroke){
                       item.strokeWidth = 3;
                        }else{
                            item.strokeWidth = 0;
                        }
                       item.strokeColor = 'black';
                       console.log('edited');
                    }
                }
                circle_stroke = !circle_stroke;
                project.activeLayer.fitBounds(view.bounds);
            })


          document.addEventListener('terminated', () => {
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
        <canvas id="myCanvas" class="min-h-full min-w-full"></canvas>

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