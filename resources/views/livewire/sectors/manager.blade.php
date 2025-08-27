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
use Livewire\Attributes\Locked;
use App\Jobs\ProcessMapOfArea;
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
    #[Locked]
    public $editable;

    #[Validate('required')]
    public $name;

    #[Validate(['schemas.*' => 'image|max:1024'])]
    public $schemas = [];


    public $id;

    public $local_id;

    public $id_editing;

    public array $schemas_sector;
    

    public function save()
    {
      if($this->editable){ 
        $this->validateOnly('name'); 
        $this->sector->name = $this->name;
        $this->sector->slug = Str::slug($this->name, '-');
        $this->sector->save();
        $this->dispatch('action_ok', title: 'Sector saved', message: 'Your modifications has been registered !');
        
        $this->modal_open = false;
        $this->render();
      }

    }

    public function saveSchema()
    {
      if($this->editable){
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
    }

    #[Computed]
    public function lines()
    {
        return Line::whereIn('sector_id', $this->sectors->pluck('id'));
    }

     #[Computed]
     public function Sectors()
    {
        return Sector::where('area_id', $this->area->id)->get();
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

      if(auth()->user()->can('lines-sectors.'.$area->site->id) or auth()->user()->hr() == 0){
        $this->editable = true;
      }else{
        $this->editable = false;
      }


      $this->area = $area;
      if($this->area->sectors->count() == 0){
        return $this->redirectRoute('admin.areas.initialize', ['site'=>$this->area->site->id, 'area' => $this->area->id ], navigate: true);
        
      }
      $this->area_id = $area->id;
      $this->sectors = Sector::where('area_id', $this->area->id)->get();
      $this->lines = Line::whereIn('sector_id', $this->sectors->pluck('id'))->get();
      if(Storage::missing('plans/site-'.$this->area->site->id.'/area-'.$this->area->id.'/edited/admin.svg')){
        ProcessMapOfArea::dispatchSync($this->area);
       
      }
      $this->map = Storage::get('plans/site-'.$this->area->site->id.'/area-'.$this->area->id.'/edited/admin.svg');
      foreach ($this->sectors as $sector) {
        $this->schemas[$sector->id] = null;
        $this->schemas[14] = null;
      }
      $this->sector = Sector::where('area_id', $this->area->id)->first();
      $this->schemas_sector = [];
      $this->schemas_sector = $this->sectors->mapWithKeys(function ($sector) {
        return [
          $sector->id => Storage::exists('plans/site-' . $this->area->site->id . '/area-' . $this->area->id . '/sector-' . $sector->id . '/schema')
        ];
      })->toArray();
    }

    public function open_modal(){
      $this->modal_subtitle = __('Get started by filling in the information below to create a new site.');
      $this->modal_title = __('New site');
      $this->id_editing = 0;
      $this->modal_submit_message = __('Create');
      $this->modal_open = true;
    }
}; ?>

<div class="py-12  mx-auto " >
  <x-grid-pattern-layout>
  <nav class="flex ml-4 my-4" aria-label="Breadcrumb">
    <ol role="list" class="flex items-center space-x-4">
      <li>
        <div>
          <a href="#" class="text-gray-400 hover:text-gray-500">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z" clip-rule="evenodd" />
            </svg>
            <span class="sr-only">{{__('Dashboard')}}</span>
          </a>
        </div>
      </li>
      <li>
        <div class="flex items-center">
          <svg class="h-5 w-5 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
          </svg>
          <a wire:navigate href="{{route('admin.sites.manage')}}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">{{__('Sites')}}</a>
        </div>
      </li>
      <li>
        <div class="flex items-center">
          <svg class="h-5 w-5 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
          </svg>
          <a wire:navigate href="{{route('admin.areas.manage', ['site'=>$this->area->site->id ])}}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700" aria-current="page">{{$this->area->site->name}}</a>
        </div>
      </li>
      <li>
        <div class="flex items-center">
          <svg class="h-5 w-5 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
          </svg>
          <a class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700" aria-current="page">{{$this->area->name}}</a>
        </div>
      </li>
    </ol>
  </nav>
  @if($this->area->type == 'bouldering')
  <x-grid-pattern-title >
    {{ __('Sectors') }}
  </x-grid-pattern-title >
  <div class="relative grid grid-cols-1 items-start gap-4 lg:grid-cols-2" x-data="{currentSector: 0, selectSector(id){ this.currentSector = id; }}">
    
    <div class="">
      <div class="bg-white overflow-hidden /*shadow-xl*/ sm:rounded-lg">
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
    <div class="max-w-7xl lg:col-span-1">
      <div class="bg-white overflow-hidden /*shadow-xl*/ sm:rounded-lg">
        <div class="px-4 sm:px-6 lg:px-8 py-8">
          <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
              <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Sectors')}}</h1>
              <p class="mt-2 text-sm text-gray-700">{{__('Registered sectors in this area')}}</p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
              <x-button  type="button">{{__('Edit')}}</x-button>
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
                      @if($this->editable)
                      <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-3">
                        <span class="sr-only">Edit</span>
                      </th>
                      @endif
                    </tr>
                  </thead>
                  <tbody class="bg-white"> @foreach ($this->sectors() as $sector) <tr x-on:mouseover="selectSector({{$sector->id}})" :class="currentSector == {{$sector->id}} ? 'bg-gray-100' : ''">
                      <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">{{$sector->local_id}}</td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{$sector->name}}</td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{$sector->lines->count()}}</td>
                      @if($this->editable)
                      <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-3">
                        <button wire:click="open_item({{$sector->id}})" class="cursor-pointer text-gray-600 hover:text-gray-900 mr-2">
                          <x-icons.icon-edit />
                        </button>
                      </td>
                      @endif
                    </tr> @endforeach </tbody>
                </table>
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
                    <form wire:submit="save" class="flex h-full flex-col bg-white /*shadow-xl*/">
                      <div class="flex-1">
                        <!-- Header -->
                        <div class="bg-gray-50 px-4 py-6 sm:px-6">
                          <div class="flex items-start justify-between space-x-3">
                            <div class="space-y-1">
                              <h2 class="text-base font-semibold leading-6 text-gray-900" id="slide-over-title">{{$this->modal_title}}</h2>
                              <p class="text-sm text-gray-500">{{$this->modal_subtitle}}</p>
                            </div>
                            <div class="flex h-7 items-center">
                              <button x-on:click="open = ! open" type="button" class="cursor-pointer relative text-gray-400 hover:text-gray-500">
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
                      <div class="shrink-0 border-t border-gray-200 px-4 py-5 sm:px-6">
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
  @else
  <x-grid-pattern-title >
    {{ __('Lines') }}
  </x-grid-pattern-title >

  <div class="relative grid grid-cols-1 items-start lg:grid-cols-2 mb-4" 
  x-data="{
  currentSector: 0, 
  currentLine: 0, 
  selectSector(id){ this.currentSector = id; this.currentLine = 0;},
  selectLine(id){ this.currentLine = id; this.currentSector = 0; }
  }">
  

  <div class=" ">
      <div class="bg-white overflow-hidden /*shadow-xl*/ sm:rounded-lg">
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
    </div>
    <div class=" sm:pl-6 lg:pl-8 lg:col-span-1">
      <div class="bg-white overflow-hidden /*shadow-xl*/ sm:rounded-lg">
        <div class="px-4 sm:px-6 lg:px-8 py-8">
          <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
              <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Sectors and lines')}}</h1>
              <p class="mt-2 text-sm text-gray-700">{{__('Registered sectors and lines in this area')}}</p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
              <x-button  disabled type="button">{{__('Edit')}}</x-button>
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
                      
                        <span class="sr-only">Edit</span>
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white"> 
                    @foreach ($this->sectors() as $sector) 
                    <tr class="border-t border-gray-200" x-on:mouseover="selectSector({{$sector->id}})" :class="currentSector == {{$sector->id}} ? 'bg-gray-100' : ''">
                      <th colspan="1" scope="colgroup" class=" bg-gray-50 py-2 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-3">
                        <div class='items-center flex'>
                        {{$sector->name}} ({{$sector->local_id}})
                        @if(!$this->schemas_sector[$sector->id])
                        <span class="ml-2 inline-flex items-center gap-x-1.5 rounded-md bg-red-100 px-2 py-1 font-medium text-red-700">
                 <svg class='mr-2 w-5 h-5' xmlns="http://www.w3.org/2000/svg"  viewBox="0 -960 960 960" fill="currentColor"><path d="M109-120q-11 0-20-5.5T75-140q-5-9-5.5-19.5T75-180l370-640q6-10 15.5-15t19.5-5q10 0 19.5 5t15.5 15l370 640q6 10 5.5 20.5T885-140q-5 9-14 14.5t-20 5.5H109Zm371-120q17 0 28.5-11.5T520-280q0-17-11.5-28.5T480-320q-17 0-28.5 11.5T440-280q0 17 11.5 28.5T480-240Zm0-120q17 0 28.5-11.5T520-400v-120q0-17-11.5-28.5T480-560q-17 0-28.5 11.5T440-520v120q0 17 11.5 28.5T480-360Z"/></svg>
                  {{('No schema')}}
                </span>
                @endif
              </div>
                      </th>
                      <th scope="colgroup" class="bg-gray-50 relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-3">
                        <button wire:click="open_item({{$sector->id}})" class="cursor-pointer text-gray-600 hover:text-gray-900 mr-2">
                          <x-icons.icon-edit />
                        </button>
                      </th>
                    </tr>
                    @foreach($this->lines->where('sector_id', $sector->id)->all() as $line)
                    <tr x-on:mouseover="selectLine({{$line->id}})" :class="currentLine == {{$line->id}} ? 'bg-gray-100' : ''">
                      <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">{{$line->local_id}}</td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{$line->routes->count()}}</td>
                      
                    </tr> 
                    @endforeach
                    @endforeach 
                  </tbody>
                </table>
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
                              <button x-on:click="open = ! open" type="button" class="cursor-pointer relative text-gray-400 hover:text-gray-500">
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

                          <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                            <x-label for="id" value="{{ __('Schema') }}" />
                            
                            <div class="sm:col-span-2">
                              

                                                        @if ($this->schemas[$this->sector->id])
              <p class="mt-2 mb-4 text-sm text-gray-700">{{__('Your schema :')}}</p>
              <img class="rounded-lg" src="{{$this->schemas[$this->sector->id]->temporaryUrl() }}">
              <div class="mt-4 flex items-center justify-end gap-x-6">
              <x-button wire:click="saveSchema()" class="mt-1">{{__('Validate')}}</x-button>
              </div>
              @elseif($this->schemas_sector[$this->sector->id])
              <div>
                <div>
              <img class="rounded-lg" src="{{Storage::url('plans/site-'.$this->area->site->id.'/area-'.$this->area->id.'/sector-'.$this->sector->id.'/schema')}}">
              <div class="mt-4 flex items-center justify-end gap-x-6">
                @if($this->editable)
              <label for="file-edit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-hidden disabled:opacity-50 transition ease-in-out duration-150">
                <span>{{__('Edit')}}</span>
                <input wire:model="schemas.{{$this->sector->id}}" id="file-edit" name="file-edit" type="file" class="sr-only">
              </label>
              @endif
            </div>
            </div>
            </div>
              @else
              <div class="relative block w-full rounded-lg border-2 border-dashed  p-12 text-center border-gray-400">
                  
                  <h3 class="mt-2 text-sm font-semibold text-gray-900 flex space-x-2 items-center justify-center">
                    <svg class='mr-2' xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M109-120q-11 0-20-5.5T75-140q-5-9-5.5-19.5T75-180l370-640q6-10 15.5-15t19.5-5q10 0 19.5 5t15.5 15l370 640q6 10 5.5 20.5T885-140q-5 9-14 14.5t-20 5.5H109Zm371-120q17 0 28.5-11.5T520-280q0-17-11.5-28.5T480-320q-17 0-28.5 11.5T440-280q0 17 11.5 28.5T480-240Zm0-120q17 0 28.5-11.5T520-400v-120q0-17-11.5-28.5T480-560q-17 0-28.5 11.5T440-520v120q0 17 11.5 28.5T480-360Z"/></svg>
                    {{__('No schema')}}</h3>
                  <p class="mt-1 text-sm text-gray-500">{{('Schemas allow to draw the route over a schema of the sector')}}</p>
                  <div class="mt-6">
                    @if($this->editable)
                    <label for="file-upload" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-hidden disabled:opacity-50 transition ease-in-out duration-150">
                      <span>{{__('Add Schema')}}</span>
                      <input wire:model="schemas.{{$this->sector->id}}" id="file-upload" name="file-upload" type="file" class="sr-only">
                    </label>
                    @endif
                  </div>
              </div>
              @endif
                            </div>
                          </div>

                        </div>
                      </div>
                      <div class="shrink-0 border-t border-gray-200 px-4 py-5 sm:px-6">
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
  @endif
  <x-grid-pattern-title >
  {{ __('Routes') }}
  </x-grid-pattern-title >

  <x-grid-pattern-item >
    <livewire:routes.manager :lines='$this->lines()->get()' :site='$this->area->site' :area='$this->area'>
  </x-grid-pattern-item >
@can('site.'.$this->area->site->id)
<x-grid-pattern-title >
  {{ __('Topo') }}
</x-grid-pattern-title >

  <x-grid-pattern-item class='mt-2'>
    <div class="px-4 sm:px-6 lg:px-8 py-8" x-data="{ open: false}">
      <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
          <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Topo')}}</h1>
          <p class="mt-2 text-sm text-gray-700">{{__('Generate Topo for this area')}}</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
         <a wire:navigate href='{{ route("admin.areas.topo.initialize.lines", ['site'=>$this->area->site, 'area'=> $this->area]) }}'  > <x-button type="button">{{ __('Lines') }}</x-button></a>
         <a wire:navigate href='{{ route("admin.areas.topo.initialize.sectors", ['site'=>$this->area->site, 'area'=> $this->area]) }}'  > <x-button type="button">{{ __('Sectors') }}</x-button></a>
          @if($area->type == 'trad')
         <a x-on:click='open=true'  > <x-button type="button">{{ __('Schema') }}</x-button></a>

          <div>
            <div style='display:none;' class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-show="open">
              <div class="fixed inset-0 bg-gray-500/75 transition-opacity" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
                <div class="fixed inset-0 z-10 overflow-y-auto">
                  <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                      <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                          <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <div class='mt-2 text-left'> 
                              <h2 class="text-base font-semibold leading-6 text-gray-900">{{ __('Choose sector') }}</h2>
                                <p class="mt-1 text-sm text-gray-500">{{ __('Get started by selecting the sector you want to get the schema') }}</p>
                                
                              <div class="mx-auto max-w-lg">
                                

                                <table class="min-w-full divide-y divide-gray-300">
                  <thead>
                    <tr>
                      <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-3">{{__('Local ID')}}</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Name')}}</th>
                      <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-3">
                        <span class="sr-only">Edit</span>
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white"> @foreach ($this->sectors() as $sector) <tr >
                      <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">{{$sector->local_id}}</td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{$sector->name}}</td>
                      <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-3">
                        <a class="cursor-pointer text-gray-600 hover:text-gray-900 mr-2" wire:navigate href='{{ route("admin.areas.topo.initialize.schema", ['site'=>$this->area->site, 'area'=> $this->area, 'sector'=>$sector]) }}' >
                          <x-icons.icon-check />
                        </a>
                      </td>
                    </tr> @endforeach </tbody>
                </table>


                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 sm:gap-x-1">
                        <x-secondary-button x-on:click="open=false" type="button">{{__('Cancel')}}</x-secondary-button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @endif
        </div>
      </div>
      <div class="sm:flex sm:items-center mt-6">
        <div class="sm:flex-auto">
          <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Tags')}}</h1>
          <p class="mt-2 text-sm text-gray-700">{{__('Generate Tags for routes of this area')}}</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
         <a wire:navigate href='{{ route("admin.areas.topo.tags", ['site'=>$this->area->site, 'area'=> $this->area]) }}'  > <x-button type="button">{{ __('Tags') }}</x-button></a>
         </div>
      </div>
    </div>
  </x-grid-pattern-item >
@endcan
</x-grid-pattern-layout>
</div>