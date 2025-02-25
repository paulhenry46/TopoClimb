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
    public $site_id;
    public $modal_open;
    public $modal_title;
    public $modal_subtitle;
    public $modal_submit_message;

    #[Validate('required')]
    public $name;
    #[Validate('required')]
    public $type;

    #[Validate('image')]
    public $picture;

    public $picture_url;

    public $slug;
    public $id_editing;
    

    public function save()
    {
        $this->validate(); 
        $this->slug = Str::slug($this->name, '-');
        if($this->id_editing == 0){
          $area = Area::create(
            $this->pull(['name', 'type', 'slug', 'site_id'])
        );
        if($this->picture !== null){
          $this->picture->storeAs(path: 'pictures/site-'.$this->area->site->id.'/area-'.$area->id.'', name: 'picture');
        $this->picture = null;
        }
        
        }else{
          $this->area->name = $this->name;
          $this->area->type = $this->type;
          $this->area->slug = $this->slug;
          $this->area->save();

          if($this->picture !== null){
        $this->picture->storeAs(path: 'pictures/site-'.$this->area->site->id.'/area-'.$this->area->id.'', name: 'picture');
        $this->picture = null;
        }

          $this->dispatch('action_ok', title: 'Area saved', message: 'Your modifications has been registered !');
        }
        
        $this->modal_open = false;
        $this->site_id = $this->site->id;
        $this->render();
    }

    #[Computed]
    public function areas()
    {
        return Area::where('site_id', $this->site->id)->paginate(10);
    }

    public function open_item($id){
      $item = Area::find($id);
      $this->area = $item;
      $this->name = $item->name;
      $this->type = $item->type;
      $this->id_editing = $id;
      if(Storage::exists('pictures/site-'.$this->area->site->id.'/area-'.$this->area->id.'/picture')){
$this->picture_url = Storage::url('pictures/site-'.$this->area->site->id.'/area-'.$this->area->id.'/picture');
      }else{
        $this->picture_url = null;
      }
      
      $this->modal_title = __('Editing ').$this->name;
      $this->modal_subtitle = __('Check the informations about this area.');
      $this->modal_submit_message = __('Edit');
      $this->modal_open = true;
    }

    public function delete_item($id){
      $item = Area::find($id);
      $item->delete();
      $this->dispatch('action_ok', title: 'Area deleted', message: 'Your modifications has been registered !');
      $this->render();
    }

    public function mount(Site $site){
      $this->modal_subtitle = __('Get started by filling in the information below to create a new area.');
      $this->modal_title = __('New area');
      $this->modal_submit_message = __('Create');
      $this->site = $site;
      $this->site_id = $site->id;
    }

    public function open_modal(){
      $this->modal_subtitle = __('Get started by filling in the information below to create a new area.');
      $this->modal_title = __('New area');
      $this->id_editing = 0;
      $this->modal_submit_message = __('Create');
      $this->modal_open = true;
    }
}; ?>

<div>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
        <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Areas of')}} {{$this->site->name}}</h1>
        <p class="mt-2 text-sm text-gray-700">{{$this->site->adress}}</p>
      </div>
      <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
        <x-button wire:click="open_modal()" type="button">{{__('New area')}}</x-button>
        
      </div>
    </div>
    <div class="mt-8 flow-root">
      <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
          <table class="min-w-full divide-y divide-gray-300">
            <thead>
              <tr>
                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-3">{{__('Name')}}</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Type')}}</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Number of sectors')}}</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Number of routes')}}</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Statut')}}</th>
                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-3">
                  <span class="sr-only">Edit</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white"> @foreach ($this->areas as $area) <tr class="hover:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">{{$area->name}}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{$area->type}}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{$area->sectors()->count()}}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">0</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                  @if($area->sectors()->count() == 0) 
                  <span class="inline-flex items-center gap-x-1.5 rounded-md bg-red-100 px-2 py-1 text-xs font-medium text-red-700">
                    <svg class="h-1.5 w-1.5 fill-red-500" viewBox="0 0 6 6" aria-hidden="true">
                      <circle cx="3" cy="3" r="3"></circle>
                    </svg>
                    {{('Uninitialized')}}
                  </span>
                   
                  @else
                  <span class="inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                    <svg class="h-1.5 w-1.5 fill-gray-500" viewBox="0 0 6 6" aria-hidden="true">
                      <circle cx="3" cy="3" r="3"></circle>
                    </svg>
                    {{('OK')}}
                  </span>
                  @endif
                </td>
                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-3">
                  
                  <a wire:navigate href="{{route('admin.sectors.manage', ['site' => $this->site->id, 'area' => $area->id])}}" class="text-gray-600 hover:text-gray-900 mr-2"><button><x-icon-see/></button></a>
                  <button wire:click="open_item({{$area->id}})" class="text-gray-600 hover:text-gray-900 mr-2"><x-icon-edit/></button>
                  <button type="button" wire:click="delete_item({{$area->id}})" wire:confirm="{{__('Are you sure you want to delete this area?')}}" class="text-red-600 hover:text-red-900">
                    <x-icon-delete/>
                  </button>
                </td>
              </tr> @endforeach
            </tbody>
          </table>
          {{ $this->areas->links() }}
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
                      <x-label for="name" value="{{ __('Area name') }}" />
                    <div class="sm:col-span-2">
                      <x-input wire:model="name" type="text" name="name" id="project-name" class="block w-full" />
                      <x-input-error for="name" class="mt-2" />
                    </div>
                  </div>
                  <!-- Project description -->
                  <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                      <x-label for="adress" value="{{ __('Area Type') }}" />
                    <div class="sm:col-span-2">
                      <fieldset wire:model="type" x-data="{type: $wire.entangle('type')}">
                        <legend class="sr-only">{{__('Area Type')}}</legend>
                        <div class="-space-y-px bg-white">
                          <label :class="type == 'voie' ? 'z-10 border-indigo-200 bg-indigo-50' : 'border-gray-200'" class=" rounded-t-md relative flex cursor-pointer border p-4 focus:outline-none">
                            <input x-model="type" type="radio" name="area-type" value="voie" class="mt-0.5 h-4 w-4 shrink-0 cursor-pointer text-indigo-600 border-gray-300 focus:ring-0 focus:ring-offset-0 active:ring-0  active:ring-indigo-600" aria-labelledby="privacy-setting-0-label" aria-describedby="privacy-setting-0-description">
                            <span class="ml-3 flex flex-col">
                              <span :class="type == 'voie' ? 'text-indigo-900' : 'text-gray-900'" id="privacy-setting-0-label" class="block text-sm font-medium">{{__('Type Voie')}}</span>
                              <span :class="type == 'voie' ? 'text-indigo-700' : 'text-gray-500'" id="privacy-setting-0-description" class="block text-sm"> {{__('Area for climbing with distinct lines')}}</span>
                            </span>
                          </label>
                          <label :class="type == 'bloc' ? 'z-10 border-indigo-200 bg-indigo-50' : 'border-gray-200'" class="rounded-b-md relative flex cursor-pointer border p-4 focus:outline-none">
                            <input x-model="type" type="radio" name="area-type" value="bloc" class="mt-0.5 h-4 w-4 shrink-0 cursor-pointer text-indigo-600 border-gray-300 focus:ring-0 focus:ring-offset-0 active:ring-0  active:ring-indigo-600" aria-labelledby="privacy-setting-1-label" aria-describedby="privacy-setting-1-description">
                            <span class="ml-3 flex flex-col">
                              <span :class="type == 'bloc' ? 'text-indigo-900' : 'text-gray-900'" id="privacy-setting-1-label" class="block text-sm font-medium">{{__('Type Bloc')}}</span>
                              <span :class="type == 'bloc' ? 'text-indigo-700' : 'text-gray-500'" id="privacy-setting-1-description" class="block text-sm">{{__('Area for bouldering without line')}}</span>
                            </span>
                          </label>
                        </div>
                      </fieldset>
                    </div>
                  </div>
                  <div x-data="{picture_url: $wire.entangle('picture_url')}" class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                    <x-label for="name" value="{{ __('Picture') }}" />
                    <div class="sm:col-span-2">
                    <x-input wire:model="picture" type="file" name="picture" id="project-name" class="block w-full file:inline-flex file:items-center file:px-4 file:py-2 file:bg-gray-800 file:border file:border-transparent file:rounded-md file:font-semibold file:text-sm file:text-white file:tracking-widest file:hover:bg-gray-700 file:focus:bg-gray-700 file:active:bg-gray-900 file:focus:outline-none file:disabled:opacity-50 file:transition file:ease-in-out file:duration-150" />
                    <x-input-error for="picture" class="mt-2" />
                    <img class="rounded-lg mt-2" x-bind:src="picture_url" />
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