<?php

use Livewire\Volt\Component;
use App\Models\Area;
use App\Models\Site;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
new class extends Component {
  use WithFileUploads;

    public Area $area;
    public Site $site;
    public int $step;
  public $path;
    #[Validate('mimes:svg|required')]
    public $photo;

    #[Validate('required')]
    public $name;
    #[Validate('required')]
    public $type;


    public function mount(Site $site, Area $area){
      $this->modal_subtitle = __('Get started by filling in the information below to create a new area.');
      $this->modal_title = __('New area');
      $this->modal_submit_message = __('Create');
      $this->site = $site;
      $this->area = $area;
      $this->step = 1;
    }

    public function save_step_2(){
      dd($this->path);
    }
}; ?>

<div>
  <nav aria-label="Progress" class="p-4">
    <ol role="list" class="space-y-4 md:flex md:space-x-8 md:space-y-0"> 
      <li class="md:flex-1">
        <!-- Current Step -->
        <a class="flex flex-col border-l-4 border-indigo-600 py-2 pl-4 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4" aria-current="step">
          <span class="text-sm font-medium text-gray-500 group-hover:text-gray-700">{{__('Step')}} 1</span>
          <span class="text-sm font-medium">{{__('Upload your map')}}</span>
        </a>
      </li>
      <li class="md:flex-1">
        <!-- Current Step -->
        <a class="flex flex-col border-l-4 border-indigo-600 py-2 pl-4 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4" aria-current="step">
          <span class="text-sm font-medium text-indigo-600">{{__('Step')}} 2</span>
          <span class="text-sm font-medium">{{__('Create sectors')}}</span>
        </a>
      </li>
      <li class="md:flex-1">
        <!-- Upcoming Step -->
        <a class="group flex flex-col border-l-4 border-gray-200 py-2 pl-4 hover:border-gray-300 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4">
          <span class="text-sm font-medium text-gray-500 group-hover:text-gray-700">{{__('Step')}} 3</span>
          <span class="text-sm font-medium">{{__('Preview')}}</span>
        </a>
      </li>
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
              <svg class="h-5 w-5 text-indigo-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-indigo-800">{{__('We have detected 15 sectors according to your file. Now, you can attach the number of your sector to the part of the plan. ')}}</h3>
              <div class="mt-2 text-sm text-indigo-700">
                <ul role="list" class="list-disc space-y-1 pl-5">
                  <li>{{__('Simply click on the part of the plan you want to attach to the sector.')}}</li>
                  <li>{{__('You can scale the width of number with + and - key.')}}</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <script type="text/javascript" src="http://127.0.0.1:8000/dist/paper-full.js"></script>
      
  <script type="text/paperscript" canvas="myCanvas">
    var path;
    var strokeWidth = 10;
    var strokeColor = 'black';
    var group;
    var num_line = '211';
    
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
        console.log(group.exportJSON());
    }
    
    function exportToJSON(){
        console.log(group.exportJSON());
    }
</script>
  <canvas id="myCanvas"></canvas>
        
      </div>
    </div>
    <div class="flex-shrink-0 border-t border-gray-200 px-4 py-5 sm:px-6">
      <div class="flex justify-end space-x-3">
        <x-secondary-button type="button">{{__('Cancel')}}</x-secondary-button> 
        <x-button wire:click="save_step_2">{{('Continue')}}</x-button> 
      </div>
    </div>
  </div>
</div>