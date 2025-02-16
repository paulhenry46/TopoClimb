<?php

use Livewire\Volt\Component;
use App\Models\Area;
use App\Models\Sector;
use App\Models\Site;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
new class extends Component {
  use WithPagination;

    public Area $area;
    public $modal_open;
    public $modal_title;
    public $modal_subtitle;
    public $modal_submit_message;

    #[Validate('required')]
    public $name;

    public $id;

    public $id_editing;
    

    public function save()
    {
        $this->validate(); 
        $this->slug = Str::slug($this->name, '-');
        if($this->id_editing == 0){
          Site::create(
            $this->pull(['name', 'adress', 'slug'])
        );
        }else{
          $this->site->name = $this->name;
          $this->site->adress = $this->adress;
          $this->site->slug = $this->slug;
          $this->site->save();
          $this->dispatch('action_ok', title: 'Site saved', message: 'Your modifications has been registered !');
        }
        
        $this->modal_open = false;
        $this->render();
    }

    #[Computed]
    public function sectors()
    {
        return Sector::where('area_id', $this->area->id)->paginate(10);
    }

    public function open_item($id){
      $item = Site::find($id);
      $this->site = $item;
      $this->name = $item->name;
      $this->adress = $item->adress;
      $this->id_editing = $id;
      $this->modal_title = __('Editing ').$this->name;
      $this->modal_subtitle = __('Check the informations about this site.');
      $this->modal_submit_message = __('Edit');
      $this->modal_open = true;
    }

    public function delete_item($id){
      $item = Site::find($id);
      $item->delete();
      $this->dispatch('action_ok', title: 'Sector deleted', message: 'Your modifications has been registered !');
      $this->render();
    }

    public function mount(Area $area){
      $this->modal_subtitle = __('Get started by filling in the information below to create a new site.');
      $this->modal_title = __('New site');
      $this->modal_submit_message = __('Create');
      $this->area = $area;
      $this->area_id = $area->id;
    }

    public function open_modal(){
      $this->modal_subtitle = __('Get started by filling in the information below to create a new site.');
      $this->modal_title = __('New site');
      $this->id_editing = 0;
      $this->modal_submit_message = __('Create');
      $this->modal_open = true;
    }
}; ?>

<div>
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
            <tbody class="bg-white"> @foreach ($this->sectors as $sector) <tr class="even:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">{{$sector->local_id}}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{$sector->name}}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{$sector->lines->count()}}</td>
                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-3">
                  <button wire:click="open_item({{$sector->id}})" class="text-gray-600 hover:text-gray-900 mr-2"><x-icon-edit/></button>
                  <button type="button" wire:click="delete_item({{$sector->id}})" wire:confirm="{{__('Are you sure you want to delete this sector?')}}" class="text-red-600 hover:text-red-900">
                    <x-icon-delete/>
                  </button>
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
                    <x-input disabled wire:model="id" type="text" name="id" id="project-name" class="block w-full" />
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