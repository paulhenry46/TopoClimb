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
    public int $step;
    public $number_sectors;
    public string $url;
    public $svg_edited;
    public $svg_with_numbers;
    #[Validate('mimes:svg|required')]
    public $photo;

    #[Validate('required')]
    public $name;
    #[Validate('required')]
    public $type;


    public function mount(Site $site, Area $area){
      $this->site = $site;
      $this->area = $area;
      $map = Storage::disk('public')->get('plans/site-'.$this->site->id.'-area-'.$this->site->id.'.svg');
      $this->url = Storage::disk('public')->url('plans/site-'.$this->site->id.'-area-'.$this->area->id.'.svg');
      $this->number_sectors = preg_match_all('<path.*\/>', $map, $matches);
      
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

    public function save(){
      Storage::put('plans/site-'.$this->site->id.'-area-'.$this->area->id.'-edited.temp.svg', $this->removeClipPath($this->svg_edited));
      Storage::put('plans/site-'.$this->site->id.'-area-'.$this->area->id.'-numbers.temp.svg',$this->removeClipPath($this->svg_with_numbers));
      
for ($i = 1; $i <= $this->number_sectors; $i++) {
    $sector = new Sector;
    $sector->local_id = $i;
    $sector->name = ''.__('Sector ').''.$i.'';
    $sector->slug = Str::slug($sector->name, '-');
    $sector->area_id = $this->area->id;
    $sector->save();
    $line = new Line;
    $line->sector_id = $sector->id;
    $line->number = 0;
    $line->save();
}
if($this->area->type == 'voie'){
  $this->redirectRoute('areas.initialize.lines', ['site' => $this->site->id, 'area' => $this->area->id], navigate: true);
}else{
$this->redirectRoute('sectors.manage', ['site' => $this->site->id, 'area' => $this->area->id], navigate: true);
}
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
        <!-- Current Step -->
        <a class="flex flex-col border-l-4 border-indigo-600 py-2 pl-4 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4" aria-current="step">
          <span class="text-sm font-medium text-indigo-600">{{__('Step')}} 3</span>
          <span class="text-sm font-medium">{{__('Create Lines')}}</span>
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
              <div x-data="{message: ''}" @svg_with_numbers.window="$wire.svg_with_numbers = $event.detail.message" 
              @svg_edited.window="$wire.svg_edited = $event.detail.message"
              @sent_to_wire.window="$wire.save()">
                <span x-text="message"></span>
            </div>
            </div>
          </div>
        </div>
        <script type="text/javascript" src="http://127.0.0.1:8000/dist/paper-full.js"></script>
        <script src='https://cdnjs.cloudflare.com/ajax/libs/acorn/8.8.2/acorn.js'></script>
      
  <script type="text/paperscript" canvas="myCanvas">
    var number_processing = 1;
    var lines_sectors = [] //Array of type line_number => sector_number
    var diameter = 60;
    var fontsize = 12;
    var num_line = 211;
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
              message: 'ok',
          }
      });
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

          createTruc(event.point, path);
          number_processing++;
          project.activeLayer.fitBounds(view.bounds);
          }
      }
  }

  function exportTheProject() {

      var evt = new CustomEvent('svg_with_numbers', {
          detail: {
              message: project.activeLayer.exportSVG({
                  asString: true
              }),
          }
      });
      window.dispatchEvent(evt);

      for (var item of project.activeLayer.getItems({
              class: Path
          })) {
          if (item.name.replace(/[^a-z]/g, '') == 'text' || item.name.replace(/[^a-z]/g, '') == 'circle') {
              item.remove();
          }
      }

      for (var item of project.activeLayer.getItems({
              class: PointText
          })) {
          item.remove();
      }
      var evt = new CustomEvent('svg_edited', {
          detail: {
              message: project.activeLayer.exportSVG({
                  asString: true
              }),
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
  if (path) {
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
    <div class="flex-shrink-0 border-t border-gray-200 px-4 py-5 sm:px-6">
      <div class="flex justify-end space-x-3" x-data="{svg_edited : '', svg_with_numbers : ''}">
        <x-secondary-button type="button">{{__('Cancel')}}</x-secondary-button> 
        <x-button @click="$dispatch('restart')">{{__('Restart')}}</x-button>
        <x-button @click="$dispatch('terminated')">{{__('Continue')}}</x-button>
      </div>
    </div>
  </div>
</div>