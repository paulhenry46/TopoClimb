<?php

use Livewire\Volt\Component;
use App\Models\Area;
use App\Models\Sector;
use App\Models\Site;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Process;
new class extends Component {
  use WithPagination;

    public Area $area;
    public Sector $sector;
    public $modal_open;
    public $modal_title;
    public $modal_subtitle;
    public $modal_submit_message;
    public $map;

    #[Validate('required')]
    public $name;

    public $id;

    public $local_id;

    public $id_editing;
    

    public function save()
    {
        $this->validate(); 
        $this->sector->name = $this->name;
        $this->sector->slug = Str::slug($this->name, '-');
        $this->sector->save();
        $this->dispatch('action_ok', title: 'Sector saved', message: 'Your modifications has been registered !');
        
        $this->modal_open = false;
        $this->render();
    }

    #[Computed]
    public function sectors()
    {
        return Sector::where('area_id', $this->area->id)->paginate(10);
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
      if(Storage::missing('plans/site-'.$this->area->site->id.'-area-'.$this->area->id.'-edited.svg')){
        $this->ProcessMaps();
      }
      $this->map = Storage::get('plans/site-'.$this->area->site->id.'-area-'.$this->area->id.'-edited.svg');
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
      $input_file_path = Storage::path('plans/site-'.$this->area->site->id.'-area-'.$this->area->id.'-edited.temp.svg');
      $output_file_path= storage_path('app/public/plans/site-'.$this->area->site->id.'-area-'.$this->area->id.'-edited.svg');
      $result = Process::run('inkscape --export-type=svg -o '.$output_file_path.' --export-area-drawing --export-plain-svg '.$input_file_path.'');

      
      $xml = simplexml_load_string(Storage::get('plans/site-'.$this->area->site->id.'-area-'.$this->area->id.'-edited.svg'));
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

      foreach (Sector::where('area_id', $this->area->id)->get() as $sector) {
        $xpath = new DOMXPath($dom);
        $item = $xpath->query("//*[@id='sector_$sector->local_id']")->item(0);
        $item->setAttribute("x-on:mouseover", "selectSector($sector->id)");
        $item->setAttribute(":class", "currentSector == $sector->id ? 'stroke-indigo-500' : ''");
      }

      Storage::put('plans/site-'.$this->area->site->id.'-area-'.$this->area->id.'-edited.svg', $dom->saveXML());
        
    }
}; ?>

<div class="py-12">

  <div class="grid grid-cols-1 items-start gap-4 lg:grid-cols-3 lg:gap-8" x-data="{currentSector: 0, selectSector(id){ this.currentSector = id; }}">
      <div class="max-w-7xl  sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="px-4 sm:px-6 lg:px-8 py-8">
              <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto stroke-indigo-500">
                  <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Map')}}</h1>
                  <p class="mt-2 text-sm text-gray-700">{{__('Map of the area with sectors ')}}</p>
                 
                  <div class=" w-full rounded-xl object-contain pt-4">
                    {!!$this->map!!}
                  </div>
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
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Number of routes')}}</th>
                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-3">
                  <span class="sr-only">Edit</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white"> @foreach ($this->sectors as $sector) <tr  x-on:mouseover="selectSector({{$sector->id}})" :class="currentSector == {{$sector->id}} ? 'bg-indigo-100' : 'even:bg-gray-50'">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">{{$sector->local_id}}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{$sector->name}}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{$sector->lines->count()}}</td>
                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-3">
                  <button wire:click="open_item({{$sector->id}})" class="text-gray-600 hover:text-gray-900 mr-2"><x-icon-edit/></button>
                  
                </td>
              </tr> @endforeach
            </tbody>
          </table>
          {{ $this->sectors->links() }}
        </div>
      </div>
    </div>
  </div>
<div x-data="{ open: $wire.entangle('modal_open') }">
  <div class="relative z-10" aria-labelledby="slide-over-title" role="dialog" aria-modal="true" x-show="open" style="display: none;">
    <!-- Background backdrop, show/hide based on slide-over state. -->
    <div class="fixed inset-0"></div>
    <div class="fixed inset-0 overflow-hidden">
      <div class="absolute inset-0 overflow-hidden">
        <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
          <div class="pointer-events-auto w-screen max-w-2xl" x-show="open" x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
            <form wire:submit="save" class="flex h-full flex-col bg-white shadow-xl">
              <div class="flex-1">
                <!-- Header -->
                <div class="bg-gray-50 px-4 py-6 sm:px-6">
                  <div class="flex items-start justify-between space-x-3">
                    <div class="space-y-1">
                      <h2 class="text-base font-semibold leading-6 text-gray-900" id="slide-over-title">{{$this->modal_title}}</h2>
                      <p class="text-sm text-gray-500">{{$this->modal_subtitle}}</p>
                    </div>
                    <div class="flex h-7 items-center">
                      <button x-on:click="open = ! open" type="button" class="relative text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
                <!-- Divider container -->
                <div class="space-y-6 py-6 sm:space-y-0 sm:divide-y sm:divide-gray-200 sm:py-0">
                  <!-- Project name -->
                  <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                      <x-label for="name" value="{{ __('Sector name') }}" />
                    <div class="sm:col-span-2">
                      <x-input wire:model="name" type="text" name="name" id="project-name" class="block w-full" />
                      <x-input-error for="name" class="mt-2" />
                    </div>
                  </div>
                  <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                    <x-label for="id" value="{{ __('Sector ID') }}" />
                  <div class="sm:col-span-2">
                    <x-input disabled wire:model="local_id" type="text" name="id" id="project-name" class="block w-full" />
                  </div>
                </div>
                </div>
              </div>
              <div class="flex-shrink-0 border-t border-gray-200 px-4 py-5 sm:px-6">
                <div class="flex justify-end space-x-3">
                  <x-secondary-button x-on:click="open = ! open" type="button">{{__('Cancel')}}</x-secondary-button>
                  <x-button type="submit">{{$this->modal_submit_message}}</x-button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

</div>
</div>
</div>