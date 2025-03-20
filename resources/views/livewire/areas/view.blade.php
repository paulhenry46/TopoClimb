<?php

use Livewire\Volt\Component;
use App\Models\Area;
use App\Models\Site;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;
new class extends Component {
  use WithPagination, WithFileUploads;

  
    public Area $area;
    public Site $site;
    public array $url_map = [];

    public function mount(Area $area){
      $this->area = $area;
      $this->site = $this->area->site;
        foreach ($area->sectors as $sector) {
            array_push($this->url_map, Storage::get('plans/site-'.$this->site->id.'/area-'.$this->area->id.'/edited/admin.svg'));
        }

    }
}; ?>



<div class="grid grid-cols-3 mt-8 gap-4 pt-2">
    <div class="col-span-2 flex flex-col" x-data="{
        currentSector: 0, 
        currentLine: 0, 
        selectSector(id){ this.currentSector = id; this.currentLine = 0;},
        selectLine(id){ this.currentLine = id; this.currentSector = 0; }
        }">
      
        <div class="bg-white overflow-hidden /*shadow-xl*/ sm:rounded-lg">
            <div class="px-4 sm:px-6 lg:px-8 py-8">
              <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto stroke-indigo-500">
                  <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Map')}}</h1>
                  <p class="mt-2 text-sm text-gray-700">{{__('Map of the area with sectors and lines')}}</p>
                  <div class="flex justify-center [&>*]:max-h-96 max-h-96 rounded-xl object-contain pt-4"> {!!$this->url_map[0]!!} </div>
                </div>
              </div>
            </div>
          </div>

    </div>
    <div class="flex flex-col">
      <x-site.infobox :site='$site'/>
    </div>
  </div>