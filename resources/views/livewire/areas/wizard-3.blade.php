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
    public $lines_sectors;
    public string $url;
    public $svg_lines;
    public $svg_lines_without_numbers;
    public $saving;


    public function mount(Site $site, Area $area){
      $this->site = $site;
      $this->area = $area;
      $this->saving = false;
      $this->url = Storage::disk('public')->url('plans/site-'.$this->site->id.'/area-'.$this->area->id.'/sectors.svg');
    }

    public function save(){
      if($this->saving == false){
      $this->saving = true;
      dump($this->lines_sectors);
      Storage::put('plans/site-'.$this->site->id.'/area-'.$this->area->id.'/lines-numbers.svg',$this->removeClipPath($this->svg_lines));
      Storage::put('plans/site-'.$this->site->id.'/area-'.$this->area->id.'/lines.svg',$this->removeClipPath($this->svg_lines_without_numbers));
      
      $localIDToID = Sector::where('area_id', $this->area->id)->pluck('id', 'local_id')->toArray();
      
      foreach ($this->lines_sectors as $key => $value)  {
        if($value !== null and $key != 0){
          $line = new Line;
          $line->local_id = $key;
          $line->sector_id = $localIDToID[$value];
          $line->save();
          dump($line);
        }
      }
      //dd($this->lines_sectors);
      $this->redirectRoute('admin.sectors.manage', ['site' => $this->site->id, 'area' => $this->area->id], navigate: true);
      }
      }

    public function removeClipPath($svg){
      $xml = simplexml_load_string($svg);
      $dom = new DOMDocument('1.0');
      $dom->preserveWhiteSpace = false;
      $dom->formatOutput = true;
      $dom->loadXML($xml->asXML());
      $items = $dom->getElementsByTagName('defs');
      foreach ($items as $item) {
          $item->remove();
      }
      return $dom->saveXML();
    }
}; ?>

<div>
  <nav aria-label="Progress" class="p-4">
    <ol role="list" class="space-y-4 md:flex md:space-x-8 md:space-y-0"> 
      <li class="md:flex-1">
        <!-- Past Step -->
        <a class="flex flex-col border-l-4 border-gray-600 py-2 pl-4 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4" aria-current="step">
          <span class="text-sm font-medium text-gray-500 group-hover:text-gray-700">{{__('Step')}} 1</span>
          <span class="text-sm font-medium">{{__('Upload your map')}}</span>
        </a>
      </li>
      <li class="md:flex-1">
        <!-- Past Step -->
        <a class="flex flex-col border-l-4 border-gray-600 py-2 pl-4 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4" aria-current="step">
          <span class="text-sm font-medium text-gray-500">{{__('Step')}} 2</span>
          <span class="text-sm font-medium">{{__('Create sectors')}}</span>
        </a>
      </li>
      <li class="md:flex-1">
        <!-- Current Step -->
        <a class="flex flex-col border-l-4 border-gray-600 py-2 pl-4 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4" aria-current="step">
          <span class="text-sm font-medium text-gray-600">{{__('Step')}} 3</span>
          <span class="text-sm font-medium">{{__('Create Lines')}}</span>
        </a>
      </li>
    </ol>
  </nav>
  <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="px-4 sm:px-6 lg:px-8 py-8">
      <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
          <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Add Lines to the area')}}</h1>
          <p class="mt-2 text-sm text-gray-700">{{$this->site->address}}</p>
        </div>
      </div>
      <div class="mt-4 flow-root">
        <div class="rounded-md bg-gray-50 p-4">
          <div class="flex">
            <div class="shrink-0">
              <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                <path d="m424-408-86-86q-11-11-28-11t-28 11q-11 11-11 28t11 28l114 114q12 12 28 12t28-12l226-226q11-11 11-28t-11-28q-11-11-28-11t-28 11L424-408Zm56 328q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/>
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-gray-800">{{__('This is the last step ! Now, you can add lines to your area.')}}</h3>
              <div class="mt-2 text-sm text-gray-700">
                <ul role="list" class="list-disc space-y-1 pl-5">
                  <li>{{__('Simply click on the part of the plan you want to add a line.')}}</li>
                  <li>{{__('You can scale the width of circle with + and - key.')}}</li>
                  <li>{{__('You can move the number once it is placed.')}}</li>
                </ul>
              </div>
              <div x-data="{message: ''}" 
              @svg_lines.window="$wire.svg_lines = $event.detail.message"
              @svg_lines_without_numbers.window="$wire.svg_lines_without_numbers = $event.detail.message"
              @export_lines_sectors.window="$wire.lines_sectors = $event.detail.message"
              @sent_to_wire2.window="console.log($event.detail.lines_sectors)"
              @sent_to_wire.window="$wire.save()">
                <span x-text="message"></span>
            </div>
            </div>
          </div>
        </div>
        <script type="text/javascript" src="http://127.0.0.1:8000/dist/paper-full.js"></script>
        <script src='http://127.0.0.1:8000/dist/acorn.js'></script>
      
  <script type="text/paperscript" canvas="myCanvas">
    var number_processing = 1;
    var creating = false;
    var lines_sectors = [] //Array of type line_number => sector_number
    var diameter = 60;
    var fontsize = 12;
    var color = 'white';
    var hitOptions = {
        segments: true,
        stroke: true,
        fill: true,
        tolerance: 5
    };
    project.importSVG('{{$this->url}}', function() {
        project.activeLayer.fitBounds(view.bounds);
    });
    document.addEventListener('restart', () => {
      project.clear()
      number_lines = 0;

      project.importSVG('{{$this->url}}', function() {
          project.activeLayer.fitBounds(view.bounds);
      });

  })

  document.addEventListener('terminated', () => {
      exportTheProject();
      var evt = new CustomEvent('sent_to_wire', {
          detail: {
            lines_sectors: lines_sectors,
          }
      });
      console.log(lines_sectors);
      window.dispatchEvent(evt);
  })

  function createTruc(point, path) {
      lines_sectors[number_processing] = Number(path.name.replace(/[^0-9]/g, ''));
      
      point_cool = point;

      var circle = new Path.Circle({
          center: point_cool,
          radius: diameter / 2,
          fillColor: color
      });
      circle.name = 'circle_' + number_processing;
      point_cool.y = point_cool.y + 4;

      var text = new PointText(point_cool);
      text.fillColor = 'black';
      text.content = number_processing;
      text.name = 'text_' + number_processing;
      text.justification = "center";

      var box = new Path.Rectangle({
          center: circle.position,
          size: [diameter - 8, diameter - 8],
          fillColor: 'black'
      });
      text.fitBounds(box.bounds);
      box.remove();

      group = new Group([circle, text]);
      group.name = 'group_' + number_processing;
  }

  function onMouseDown(event) {
      path = null;
      var hitResult = project.hitTest(event.point, hitOptions);
      if (!hitResult) {
          return;
      }

      if (hitResult) {
          hit = true;
          path = hitResult.item;
          creating = false;

          if(path.name.replace(/[^a-z]/g, '') == 'text'){
            var name = path.name.replace(/text/g, 'circle')
            associated_paths = project.getItems({
              name: name
          });
          }else if(path.name.replace(/[^a-z]/g, '') == 'circle'){
            var name = path.name.replace(/circle/g, 'text')
            associated_paths = project.getItems({
              name: name
          });
          }else if(path.name.replace(/[^a-z]/g, '') == 'sector'){
            creating = true;
          createTruc(path.getNearestPoint(event.point), path);
          number_processing++;
          project.activeLayer.fitBounds(view.bounds);
          }
      }
  }

  function exportTheProject() {
    var evt = new CustomEvent('svg_lines', {
      detail: {
        message: project.exportSVG({
          asString: true
        }),
      }
    });
    window.dispatchEvent(evt);
    
    for (var item of project.activeLayer.getItems({
        class: Path
      })) {
      if (item.name.replace(/[^a-z]/g, '') == 'circle') {
    
        var box = new Path.Rectangle({
          center: item.position,
          size: [14, 14],
          fillColor: 'black'
        });
        item.fitBounds(box.bounds);
        box.remove();
      }
    }
    
    for (var item of project.activeLayer.getItems({
        class: PointText
      })) {
      if (item.name.replace(/[^a-z]/g, '') == 'text') {
        item.remove();
      }
    }
    
    var evt = new CustomEvent('svg_lines_without_numbers', {
      detail: {
        message: project.exportSVG({
          asString: true
        }),
      }
    });
    window.dispatchEvent(evt);

    var evt = new CustomEvent('export_lines_sectors', {
      detail: {
        message: lines_sectors,
      }
    });
    window.dispatchEvent(evt);
  }

  function scaleAllItems() {
      for (var item of project.activeLayer.getItems({
              class: Path
          })) {
          if (item.name.replace(/[^a-z]/g, '') == 'circle') {

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
          if (item.name.replace(/[^a-z]/g, '') == 'text') {

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

  function onKeyDown(event) {
      if (event.key == '+') {
          diameter = diameter + 2;
          scaleAllItems();
      }

      if (event.key == '-') {
          diameter = diameter - 2;
          scaleAllItems();
      }
  }

function onMouseDrag(event) {
  if (path && (creating == false)) {
      var sector_path = project.getItem({
        name: 'sector_' + lines_sectors[path.name.replace(/[^0-9]/g, '')]
      });
      var nearestPoint = sector_path.getNearestPoint(event.point);
      path.position = nearestPoint;

      for (var item of associated_paths){
        item.position = nearestPoint;
      }
  }
}
</script>
  <canvas class=" min-h-full min-w-full" id="myCanvas"></canvas>
        
      </div>
    </div>
    <div class="shrink-0 border-t border-gray-200 px-4 py-5 sm:px-6">
      <div class="flex justify-end space-x-3" x-data="{svg_edited : '', svg_with_numbers : ''}">
        <x-secondary-button type="button">{{__('Cancel')}}</x-secondary-button> 
        <x-button @click="$dispatch('restart')">{{__('Restart')}}</x-button>
        <x-button @click="$dispatch('terminated')">{{__('Continue')}}</x-button>
      </div>
    </div>
  </div>
</div>