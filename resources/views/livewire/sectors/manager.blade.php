<?php

use Livewire\Volt\Component;
use App\Models\Area;
use App\Models\Line;
use App\Models\Sector;
use App\Models\Site;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Process;
new class extends Component {
  use WithPagination, WithFileUploads;

    public Area $area;
    public Sector $sector;
    public $sectors;
    public $lines;
    public $modal_open;
    public $modal_title;
    public $modal_subtitle;
    public $modal_submit_message;
    public $map;

    #[Validate('required')]
    public $name;

    #[Validate(['schemas.*' => 'image|max:1024'])]
    public $schemas = [];


    public $id;

    public $local_id;

    public $id_editing;
    

    public function save()
    {
        $this->validateOnly('name'); 
        $this->sector->name = $this->name;
        $this->sector->slug = Str::slug($this->name, '-');
        $this->sector->save();
        $this->dispatch('action_ok', title: 'Sector saved', message: 'Your modifications has been registered !');
        
        $this->modal_open = false;
        $this->render();
    }

    public function saveSchema()
    {
      $this->validateOnly('schemas'); 
        //dd($this->schemas);
        foreach ($this->schemas as $key => $value) {
          
         if ($value != null){
          $id = $key;
         }
        }
        $file = $this->schemas[$id];
        
      $name = 'schema';
      $file->storeAs(path: 'plans/site-'.$this->area->site->id.'/area-'.$this->area->id.'/sector-'.$id.'', name: $name);
      $this->schemas[$id] = null;
    }

    #[Computed]
    public function lines()
    {
        return Line::whereIn('sector_id', $this->sectors);
    }

    public function open_item($id){
      $item = Sector::find($id);
      $this->sector = $item;
      $this->name = $item->name;
      $this->local_id = $item->local_id;
      $this->id_editing = $id;
      $this->modal_title = __('Editing ').$this->name;
      $this->modal_subtitle = __('Check the informations about this sector.');
      $this->modal_submit_message = __('Edit');
      $this->modal_open = true;
    }

    public function mount(Area $area){
      
      $this->area = $area;
      if($this->area->sectors->count() == 0){
        return $this->redirectRoute('areas.initialize', ['site'=>$this->area->site->id, 'area' => $this->area->id ], navigate: true);
        
      }
      $this->area_id = $area->id;
      $this->sectors = Sector::where('area_id', $this->area->id)->get();
      $this->lines = Line::whereIn('sector_id', $this->sectors->pluck('id'))->get();
      if(Storage::missing('plans/site-'.$this->area->site->id.'/area-'.$this->area->id.'/edited/admin.svg')){
        $this->ProcessMaps();
      }
      $this->map = Storage::get('plans/site-'.$this->area->site->id.'/area-'.$this->area->id.'/edited/admin.svg');
      foreach ($this->sectors as $sector) {
        $this->schemas[$sector->id] = null;
        $this->schemas[14] = null;
      }
    }

    public function open_modal(){
      $this->modal_subtitle = __('Get started by filling in the information below to create a new site.');
      $this->modal_title = __('New site');
      $this->id_editing = 0;
      $this->modal_submit_message = __('Create');
      $this->modal_open = true;
    }

    public function ProcessMaps(){
      //Use inkscape to fit map to grid (don't keep blank space around map)
      if($this->area->type == 'voie'){
        $input_file_path = Storage::path('plans/site-'.$this->area->site->id.'/area-'.$this->area->id.'/lines.svg');
      }else{
        $input_file_path = Storage::path('plans/site-'.$this->area->site->id.'/area-'.$this->area->id.'/sectors.svg');
      }
      
      $output_file_path= storage_path('app/public/plans/site-'.$this->area->site->id.'/area-'.$this->area->id.'/edited/admin.svg');
      $result = Process::run('inkscape --export-type=svg -o '.$output_file_path.' --export-area-drawing --export-plain-svg '.$input_file_path.'');

      
      $xml = simplexml_load_string(Storage::get('plans/site-'.$this->area->site->id.'/area-'.$this->area->id.'/edited/admin.svg'));
      $dom = new DOMDocument('1.0');
      $dom->preserveWhiteSpace = false;
      $dom->formatOutput = true;
      $dom->loadXML($xml->asXML());

      //In order to make svg responsive, delete height and width attributes and replace them by a viewBox attribute
      $items = $dom->getElementsByTagName('svg');
      foreach ($items as $item) {
          $width = $item->getAttribute('width');
          $height = $item->getAttribute('height');
          $item->removeAttribute('width');
          $item->removeAttribute('height');
          $item->setAttribute("viewBox", "0 0 $width $height");

      }
      foreach ($this->sectors as $sector) {
        $xpath = new DOMXPath($dom);
        $item = $xpath->query("//*[@id='sector_$sector->local_id']")->item(0);
        $item->setAttribute("x-on:mouseover", "selectSector($sector->id)");
        $item->setAttribute(":class", "currentSector == $sector->id ? 'stroke-indigo-500' : ''");
      }

      if($this->area->type == 'voie'){
        foreach ($this->lines as $line) {
        $xpath = new DOMXPath($dom);
        $item = $xpath->query("//*[@id='circle_$line->local_id']")->item(0);
        $item->setAttribute("x-on:mouseover", "selectLine($line->id)");
        $item->setAttribute(":class", "currentLine == $line->id ? 'fill-indigo-500' : ''");
      }
      }

      Storage::put('plans/site-'.$this->area->site->id.'/area-'.$this->area->id.'/edited/admin.svg', $dom->saveXML());
        
    }
}; ?>

<div class="py-12">
  @if($this->area->type == 'bloc')
  <div class="grid grid-cols-1 items-start gap-4 lg:grid-cols-3 lg:gap-8" x-data="{currentSector: 0, selectSector(id){ this.currentSector = id; }}">
    <div class="max-w-7xl  sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="px-4 sm:px-6 lg:px-8 py-8">
          <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto stroke-indigo-500">
              <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Map')}}</h1>
              <p class="mt-2 text-sm text-gray-700">{{__('Map of the area with sectors ')}}</p>
              <div class=" w-full rounded-xl object-contain pt-4"> {!!$this->map!!} </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="max-w-7xl  sm:px-6 lg:px-8 lg:col-span-2">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="px-4 sm:px-6 lg:px-8 py-8">
          <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
              <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Sectors')}}</h1>
              <p class="mt-2 text-sm text-gray-700">{{__('Registered sectors in this area')}}</p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
              <x-button wire:click="open_modal()" type="button">{{__('Add sector')}}</x-button>
            </div>
          </div>
          <div class="mt-8 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
              <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <table class="min-w-full divide-y divide-gray-300">
                  <thead>
                    <tr>
                      <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-3">{{__('Local ID')}}</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Name')}}</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Number of lines')}}</th>
                      <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-3">
                        <span class="sr-only">Edit</span>
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white"> @foreach ($this->sectors as $sector) <tr x-on:mouseover="selectSector({{$sector->id}})" :class="currentSector == {{$sector->id}} ? 'bg-indigo-100' : 'even:bg-gray-50'">
                      <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">{{$sector->local_id}}</td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{$sector->name}}</td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{$sector->lines->count()}}</td>
                      <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-3">
                        <button wire:click="open_item({{$sector->id}})" class="text-gray-600 hover:text-gray-900 mr-2">
                          <x-icon-edit />
                        </button>
                      </td>
                    </tr> @endforeach </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@else
  <div class="grid grid-cols-1 items-start gap-4 lg:grid-cols-3 lg:gap-8" 
  x-data="{
  currentSector: 0, 
  currentLine: 0, 
  selectSector(id){ this.currentSector = id; this.currentLine = 0;},
  selectLine(id){ this.currentLine = id; this.currentSector = 0; }
  }">
    <div class="max-w-7xl  sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="px-4 sm:px-6 lg:px-8 py-8">
          <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto stroke-indigo-500">
              <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Map')}}</h1>
              <p class="mt-2 text-sm text-gray-700">{{__('Map of the area with sectors and lines')}}</p>
              <div class=" w-full rounded-xl object-contain pt-4"> {!!$this->map!!} </div>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mt-3">

        <div class="px-4 sm:px-6 lg:px-8 py-8">
          <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto ">
              <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Schemas')}}</h1>
              <p class="mt-2 mb-4 text-sm text-gray-700">{{__('Schemas of the sectors of this area')}}</p>
              @foreach ($this->sectors as $sector)
              @if ($this->schemas[$sector->id]) 
              <h1 class="align-center text-base font-semibold leading-6 text-gray-900">{{$sector->name}}</h1>
              <p class="mt-2 mb-4 text-sm text-gray-700">{{__('Your schema :')}}</p>
              <img class="rounded-lg" src="{{$this->schemas[$sector->id]->temporaryUrl() }}">
              <div class="mt-4 flex items-center justify-end gap-x-6">
              <x-button wire:click="saveSchema()" class="mt-1">{{__('Validate')}}</x-button>
              </div>
              @elseif(Storage::exists('plans/site-'.$this->area->site->id.'/area-'.$this->area->id.'/sector-'.$sector->id.'/schema'))
              <div class="mt-4 mb-2 align-center flex items-center justify-between gap-x-6">
                <h1 class="align-center text-base font-semibold leading-6 text-gray-900">{{$sector->name}}</h1>
                <label for="file-edit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none disabled:opacity-50 transition ease-in-out duration-150">
                  <span>{{__('Edit')}}</span>
                  <input wire:model="schemas.{{$sector->id}}" id="file-edit" name="file-edit" type="file" class="sr-only">
                </label> 
                </div>
              <img class="rounded-lg" src="{{Storage::url('plans/site-'.$this->area->site->id.'/area-'.$this->area->id.'/sector-'.$sector->id.'/schema')}}">
              @else
              <h1 class="align-center text-base font-semibold leading-6 text-gray-900">{{$sector->name}}</h1>
              <div class="relative block w-full rounded-lg border-2 border-dashed  p-12 text-center border-gray-400">
                  <svg class="mx-auto size-12 text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" data-slot="icon">
                    <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 0 1 2.25-2.25h16.5A2.25 2.25 0 0 1 22.5 6v12a2.25 2.25 0 0 1-2.25 2.25H3.75A2.25 2.25 0 0 1 1.5 18V6ZM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0 0 21 18v-1.94l-2.69-2.689a1.5 1.5 0 0 0-2.12 0l-.88.879.97.97a.75.75 0 1 1-1.06 1.06l-5.16-5.159a1.5 1.5 0 0 0-2.12 0L3 16.061Zm10.125-7.81a1.125 1.125 0 1 1 2.25 0 1.125 1.125 0 0 1-2.25 0Z" clip-rule="evenodd" />
                  </svg>
                  <h3 class="mt-2 text-sm font-semibold text-gray-900">{{__('No schema')}}</h3>
                  <p class="mt-1 text-sm text-gray-500">{{('Schemas allow to draw the route over a schema of the sector')}}</p>
                  <div class="mt-6">
                    <label for="file-upload" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none disabled:opacity-50 transition ease-in-out duration-150">
                      <span>{{__('Add Schema')}}</span>
                      <input wire:model="schemas.{{$sector->id}}" id="file-upload" name="file-upload" type="file" class="sr-only">
                    </label>
                  </div>
              </div>
              @endif

              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="max-w-7xl  sm:px-6 lg:px-8 lg:col-span-2">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="px-4 sm:px-6 lg:px-8 py-8">
          <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
              <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Sectors and lines')}}</h1>
              <p class="mt-2 text-sm text-gray-700">{{__('Registered sectors and lines in this area')}}</p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
              <x-button wire:click="open_modal()" type="button">{{__('Edit')}}</x-button>
            </div>
          </div>
          <div class="mt-8 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
              <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <table class="min-w-full divide-y divide-gray-300">
                  <thead>
                    <tr>
                      <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-3">{{__('Local ID')}}</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Number of routes')}}</th>
                      <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-3">
                        <span class="sr-only">Edit</span>
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white"> 
                    @foreach ($this->sectors as $sector) 
                    <tr class="border-t border-gray-200" x-on:mouseover="selectSector({{$sector->id}})" :class="currentSector == {{$sector->id}} ? 'bg-indigo-100' : ''">
                      <th colspan="5" scope="colgroup" class="bg-gray-50 py-2 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-3">{{$sector->name}} ({{$sector->local_id}})</th>
                    </tr>
                    @foreach($this->lines->where('sector_id', $sector->id)->all() as $line)
                    <tr x-on:mouseover="selectLine({{$line->id}})" :class="currentLine == {{$line->id}} ? 'bg-indigo-100' : ''">
                      <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">{{$line->local_id}}</td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{$line->routes->count()}}</td>
                      <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-3">
                        <button wire:click="open_item({{$line->id}})" class="text-gray-600 hover:text-gray-900 mr-2">
                          <x-icon-edit />
                        </button>
                      </td>
                    </tr> 
                    @endforeach
                    @endforeach 
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        
      </div>
    </div>
  </div>
  @endif
</div>