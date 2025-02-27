<?php

use Livewire\Volt\Component;
use App\Models\Area;
use App\Models\Site;
use App\Models\Sector;
use App\Models\Line;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
new class extends Component {
  use WithFileUploads;

    public Area $area;
    public Site $site;
    public Line $line;
    public $url;
    public $path;

    public function mount(Site $site, Area $area, Line $line){
      $this->site = $site;
      $this->area = $area;
      $this->line = $line;
      $this->url = Storage::url('plans/site-'.$line->sector->area->site->id.'/area-'.$line->sector->area->id.'/sector-'.$line->sector->id.'/schema')
    }

    public function save(){
      dd($this->path);
      $this->validateOnly('photo');
      
      $this->redirectRoute('admin.areas.initialize.sectors', ['site' => $this->site->id, 'area' => $this->area->id], navigate: true);
    }
}; ?>

<div>
  <nav aria-label="Progress" class="p-4">
    <ol role="list" class="space-y-4 md:flex md:space-x-8 md:space-y-0"> 
      <li class="md:flex-1">
        <!-- Current Step -->
        <a class="flex flex-col border-l-4 border-indigo-600 py-2 pl-4 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4" aria-current="step">
          <span class="text-sm font-medium text-indigo-600">{{__('Step')}} 1</span>
          <span class="text-sm font-medium">{{__('Add informations')}}</span>
        </a>
      </li>
      <li class="md:flex-1">
        <!-- Upcoming Step -->
        <a class="group flex flex-col border-l-4 border-gray-200 py-2 pl-4 hover:border-gray-300 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4">
          <span class="text-sm font-medium text-gray-500 group-hover:text-gray-700">{{__('Step')}} 2</span>
          <span class="text-sm font-medium">{{__('Draw path')}}</span>
        </a>
      </li>
      @if($this->area->type == 'voie')
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
  <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="px-4 sm:px-6 lg:px-8 py-8">
      <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
          <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Upload map of the area')}}</h1>
          <p class="mt-2 text-sm text-gray-700">{{$this->site->adress}}</p>
        </div>
      </div>
      <div class="mt-4 flow-root">
        <div class="rounded-md bg-indigo-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                <path d="m424-408-86-86q-11-11-28-11t-28 11q-11 11-11 28t11 28l114 114q12 12 28 12t28-12l226-226q11-11 11-28t-11-28q-11-11-28-11t-28 11L424-408Zm56 328q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/>
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-indigo-800">{{__('We have detected')}} {{$this->number_sectors}} {{__('sectors according to your file. Now, you can attach the number of your sector to the part of the plan. ')}}</h3>
              <div class="mt-2 text-sm text-indigo-700">
                <ul role="list" class="list-disc space-y-1 pl-5">
                  <li>{{__('Simply click on the part of the plan you want to attach to the sector.')}}</li>
                  <li>{{__('You can scale the width of number with + and - key.')}}</li>
                </ul>
              </div>
              <div x-data="{message: ''}" @svg.window="$wire.path = $event.detail.message"
              @sent_to_wire.window="$wire.save()">
                <span x-text="message"></span>
            </div>
            </div>
          </div>
        </div>
        <script type="text/javascript" src="http://127.0.0.1:8000/dist/paper-full.js"></script>
        <script src='https://cdnjs.cloudflare.com/ajax/libs/acorn/8.8.2/acorn.js'></script>
        <script type="text/paperscript" canvas="myCanvas">
          var path;
          const strokeWidth = 10;
          const strokeColor = '{{$this->line->color}}';
          var group;
          const num_line = '{{$this->line->id}}';

          function onMouseDown(event) {
            // If we produced a path before, deselect it:
            if (path) {
              path.remove();
            }
            // Create a new path and set its stroke color to black:
            path = new Path({
              segments: [event.point],
              strokeColor: strokeColor,
              strokeWidth : strokeWidth,
              name : 'path_' + num_line
            });
          }

          // While the user drags the mouse, points are added to the path
          // at the position of the mouse:
          function onMouseDrag(event) {
            path.add(event.point);
          }

          // When the mouse is released, we simplify the path:
          function onMouseUp(event) {
            // When the mouse is released, simplify it:
            path.simplify(10);
            group = new Group([path]);
              group.name = 'id_' + num_line;
          }

          function exportToJSON(){
              console.log(group.exportJSON());
          }

          document.addEventListener('terminated', () => {
            
            var evt = new CustomEvent('svg', {
              detail: {
                  message: project.activeLayer.exportSVG({
                      asString: true
                  }),
              }
          });
            var evt = new CustomEvent('sent_to_wire', {
                detail: {
                    message: 'ok',
                }
            });
            window.dispatchEvent(evt);
        })
        </script>
        <div class="relative min-h-60 md:min-h-72 w-full flex justify-center items-center">
          <img class="rounded-lg" src="{{$url}}">
          <div class="absolute inset-0 flex justify-center items-center ">
            <canvas id="myCanvas" class="min-h-full min-w-full"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="flex-shrink-0 border-t border-gray-200 px-4 py-5 sm:px-6">
      <div class="flex justify-end space-x-3" x-data="{svg_edited : '', svg_with_numbers : ''}">
        <x-secondary-button type="button">{{__('Cancel')}}</x-secondary-button>
        <x-button @click="$dispatch('terminated')">{{__('Continue')}}</x-button>
      </div>
    </div>
  </div>
</div>