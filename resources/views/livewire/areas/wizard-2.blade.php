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
      $this->url = Storage::disk('public')->url('plans/site-'.$this->site->id.'-area-'.$this->site->id.'.svg');
      $this->number_sectors = preg_match_all('<path.*\/>', $map, $matches);
      
    }

    public function save(){
      //dump($this->svg_edited);
      //dd($this->svg_with_numbers);
      $xml = simplexml_load_string($this->svg_with_numbers);
      //dump($xml);
      $dom = new DOMDocument('1.0');
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($xml->asXML());
//dump($dom);
$divs = $dom->getElementsByTagName('defs');
foreach ($divs as $div) {
    //dump($div);
    dump($div->remove());
    
}
//$dom->removeChild($dom->getElementsByTagName('clipPath')->item(0));
dd($dom->saveXML());




      Storage::put('plans/site-'.$this->site->id.'-area-'.$this->site->id.'-edited.svg', $this->svg_edited);
      Storage::put('plans/site-'.$this->site->id.'-area-'.$this->site->id.'-numbers.svg', $this->svg_with_numbers);
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
    var number_sectors = 0;
    var number_processing = 1;
    var sectors_numbered = []
    var diameter = 60;
    var fontsize = 12;
    var num_line = 211;
    var color = 'white';
    var hitOptions = {
        segments: true,
        stroke: true,
        fill: false,
        tolerance: 5
    };
    project.importSVG('{{$this->url}}', function() {
        project.activeLayer.fitBounds(view.bounds);
        console.log(project.activeLayer.exportJSON());

        for (var item of project.activeLayer.getItems({
                class: Path
            })) {
            item.strokeWidth = 10;
            item.strokeColor = 'gray';
            number_sectors++;
            createNewSeparator(item);

        }
        console.log(number_sectors)
    });
    document.addEventListener('restart', () => {
        project.clear()
        number_sectors = 0;
        number_processing = 1;
        sectors_numbered = [];

        project.importSVG('{{$this->url}}', function() {
            project.activeLayer.fitBounds(view.bounds);
            console.log(project.activeLayer.exportJSON());

            for (var item of project.activeLayer.getItems({
                    class: Path
                })) {
                item.strokeWidth = 10;
                item.strokeColor = 'gray';
                number_sectors++;
                createNewSeparator(item);

            }
            console.log(number_sectors)
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

    function createTruc(point) {
        // Add a segment to the path at the position of the mouse:
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


        // Set the content of the text item:
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

    function createNewSeparator(item) {
        console.log('coucou')
        // Add a segment to the path at the position of the mouse:
        start = item.getPointAt(item.length - 10)
        end = item.getPointAt(item.length) + item.getTangentAt(item.length - 1) * 2

        var path = new Path.Line(start, end);
        path.strokeColor = 'white';
        path.strokeWidth = 11;
        path.name = 'separator';

        start = item.getPointAt(0) + item.getTangentAt(0) * -2
        end = item.getPointAt(10)

        var path = new Path.Line(start, end);
        path.strokeColor = 'white';
        path.strokeWidth = 11;
        path.name = 'separator';
    }

    function createSeparator(point) {
        // Add a segment to the path at the position of the mouse:
        point_cool = point;

        var circle = new Path.Circle({
            center: point_cool,
            radius: 10,
            fillColor: color
        });
        circle.name = 'separator_' + number_processing;
    }

    function onMouseDown(event) {
        segment = path = null;
        var hitResult = project.hitTest(event.point, hitOptions);
        if (!hitResult) {
            return;
        }

        if (hitResult) {
            hit = true;
            console.log('ok')
            path = hitResult.item;
            console.log(path)
            if (number_processing > number_sectors) {
                var evt = new CustomEvent('svg_with_numbers', {
                    detail: {
                        message: project.activeLayer.exportSVG({
                            asString: true
                        }),
                    }
                });
                window.dispatchEvent(evt);

                console.log('doing that');
                console.log(project.activeLayer.exportJSON());

                for (var item of project.activeLayer.getItems({
                        class: Path
                    })) {
                    console.log(item.name);
                    if (item.name.replace(/[^a-z]/g, '') == 'text' || item.name.replace(/[^a-z]/g, '') == 'circle') {
                        item.remove();

                    }
                }

                for (var item of project.activeLayer.getItems({
                        class: PointText
                    })) {
                    console.log(item.name);
                    item.remove();
                }
                console.log(project.activeLayer.exportJSON());
                var evt = new CustomEvent('svg_edited', {
                    detail: {
                        message: project.activeLayer.exportSVG({
                            asString: true
                        }),
                    }
                });
                window.dispatchEvent(evt);
                return;
            }


            if (sectors_numbered.includes(path.name)) {
                return;
            }

            if (path.name == 'separator') {
                return;
            }
            createTruc(path.getPointAt(path.length / 2));
            path.name = 'sector_' + number_processing;
            sectors_numbered.push(path.name);
            number_processing++;
            console.log(sectors_numbered);
            project.activeLayer.fitBounds(view.bounds);

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

        console.log(project.activeLayer.exportJSON());



        for (var item of project.activeLayer.getItems({
                class: Path
            })) {
            console.log(item.name);
            if (item.name.replace(/[^a-z]/g, '') == 'text' || item.name.replace(/[^a-z]/g, '') == 'circle') {
                item.remove();

            }
        }

        for (var item of project.activeLayer.getItems({
                class: PointText
            })) {
            console.log(item.name);
            item.remove();
        }
        console.log(project.activeLayer.exportJSON());
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
            console.log(item.name);
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
            console.log(item.name);
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