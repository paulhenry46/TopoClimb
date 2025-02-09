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
    #[Validate('mimes:svg|required')]
    public $photo;
    public $site_id;
    public $step;

    #[Validate('required')]
    public $name;
    #[Validate('required')]
    public $type;


    public function mount(Site $site){
      $this->modal_subtitle = __('Get started by filling in the information below to create a new area.');
      $this->modal_title = __('New area');
      $this->modal_submit_message = __('Create');
      $this->site = $site;
      $this->site_id = $site->id;
      $this->step = 1;
    }

    public function save_step_1(){
      $this->validateOnly('photo');
      $this->photo->storeAs(path: 'photos', name: 'avatar.svg');
      $this->step++;
    }
}; ?>

<div>
  @if($this->step == 1)
  <nav aria-label="Progress" class="p-4">
    <ol role="list" class="space-y-4 md:flex md:space-x-8 md:space-y-0"> <li class="md:flex-1">
        <!-- Current Step -->
        <a class="flex flex-col border-l-4 border-indigo-600 py-2 pl-4 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4" aria-current="step">
          <span class="text-sm font-medium text-indigo-600">{{__('Step')}} 1</span>
          <span class="text-sm font-medium">{{__('Upload your map')}}</span>
        </a>
      </li>
      <li class="md:flex-1">
        <!-- Upcoming Step -->
        <a class="group flex flex-col border-l-4 border-gray-200 py-2 pl-4 hover:border-gray-300 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4">
          <span class="text-sm font-medium text-gray-500 group-hover:text-gray-700">{{__('Step')}} 2</span>
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
              <h3 class="text-sm font-medium text-indigo-800">{{('It is the map we will use in the site to place sectors, routes and a lot of others things. In order to allow us to process it, your map must respect rules :')}}</h3>
              <div class="mt-2 text-sm text-indigo-700">
                <ul role="list" class="list-disc space-y-1 pl-5">
                  <li>{{__('Your file must in SVG format.')}}</li>
                  <li>{{__('Your map must take all the place in the file (no blank space around map).')}}</li>
                  <li>{{__('Each sector must correspond to a distinct path element.')}}</li>
                  <li>{{__('The file must follow the W3C consortium\'s SVG format standards.')}}</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="space-y-2 px-2 sm:gap-4 sm:space-y-0  sm:py-5">
          <div class="">
            <div class="col-span-full" x-data="{ uploading: false, progress: 0, uploaded: false }" x-on:livewire-upload-start="uploading = true, uploaded = false" x-on:livewire-upload-finish="uploading = false, uploaded = true" x-on:livewire-upload-cancel="uploading = false" x-on:livewire-upload-error="uploading = false" x-on:livewire-upload-progress="progress = 'width: ' + $event.detail.progress + '%'">
              <x-label for="name" value="{{ __('Map file') }}" />
              <div class="mt-2 flex justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10">
                <div class="text-center">
                  <svg class="mx-auto h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                  </svg>
                  <div class="mt-4 flex text-sm leading-6 text-gray-600">
                    <label for="file-upload" class="relative cursor-pointer rounded-md bg-white font-semibold text-gray-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-600 focus-within:ring-offset-2 hover:text-indigo-500"> @if ($this->photo !== null) {{$this->photo->getClientOriginalName()}} @else <span>{{__('Upload a file')}}</span> @endif <input id="file-upload" wire:model="photo" name="file-upload" type="file" class="sr-only">
                    </label>
                  </div>
                  <p class="text-xs leading-5 text-gray-600">{{__('SVG up to 10MB')}}</p>
                </div>
              </div>
              <div x-show="uploading">
                <div class="mt-6" aria-hidden="true">
                  <div class="overflow-hidden rounded-full bg-gray-200">
                    <div class="h-2 rounded-full bg-indigo-600" x-bind:style="progress" style="width: 0%"></div>
                  </div>
                </div>
              </div>
              <x-input-error  for="photo" class="mt-2" />
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="flex-shrink-0 border-t border-gray-200 px-4 py-5 sm:px-6">
      <div class="flex justify-end space-x-3">
        <x-secondary-button type="button">{{__('Cancel')}}</x-secondary-button> 
        <x-button wire:click="save_step_1">{{('Continue')}}</x-button> 
      </div>
    </div>
  </div>
  @endif
</div>