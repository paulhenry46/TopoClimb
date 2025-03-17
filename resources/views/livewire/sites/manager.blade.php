<?php

use Livewire\Volt\Component;
use App\Models\Site;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
new class extends Component {
  use WithPagination, WithFileUploads;

    public Site $site;
    public $modal_open;
    public $modal_title;
    public $modal_subtitle;
    public $modal_submit_message;

    #[Validate('required|string')]
    public $name;
    #[Validate('required|string')]
    public $adress;
    #[Validate('string|nullable')]
    public $state;
    #[Validate('email|nullable')]
    public $mail;
    #[Validate('string|nullable')]
    public $coord;
    #[Validate('string|nullable')]
    public $phone;
    #[Validate('string|nullable')]
    public $website;
    #[Validate('string|nullable')]
    public $description;

    #[Validate('image')]
    public $picture;
    public $picture_url;

    #[Validate('image')]
    public $banner;
    public $banner_url;
    
    public $slug;
    public $id_editing;
    

    public function save()
    {
        $this->validate(); 
        $this->slug = Str::slug($this->name, '-');
        if($this->id_editing == 0){
          $this->site = Site::create(
            $this->pull(['name', 'adress', 'slug'])
        );
        $this->site->state = $this->state;
        $this->site->coord = $this->coord;
        $this->site->description = $this->description;
        $this->site->mail = $this->mail;
        $this->site->phone = $this->phone;
        $this->site->website = $this->website;
        $this->site->save();
        }else{
          $this->site->name = $this->name;
          $this->site->adress = $this->adress;
          $this->site->slug = $this->slug;
          $this->site->state = $this->state;
          $this->site->coord = $this->coord;
          $this->site->description = $this->description;
          $this->site->mail = $this->mail;
          $this->site->phone = $this->phone;
          $this->site->website = $this->website;
          $this->site->save();
          $this->dispatch('action_ok', title: 'Site saved', message: 'Your modifications has been registered !');
        }
        if($this->picture !== null){
          $this->picture->storeAs(path: 'pictures/site-'.$this->site->id.'', name: 'profile');
        $this->picture = null;
        }

        if($this->banner !== null){
          $this->banner->storeAs(path: 'pictures/site-'.$this->site->id.'', name: 'banner');
        $this->banner = null;
        }
        
        $this->modal_open = false;
        $this->render();
    }

    #[Computed]
    public function sites()
    {
        return Site::paginate(10);
    }

    public function open_item($id){
      $item = Site::find($id);
      $this->site = $item;
      $this->name = $item->name;
      $this->adress = $item->adress;
      $this->state = $item->state;
        $this->coord = $item->coord;
        $this->description = $item->description;
        $this->mail = $item->mail;
        $this->phone = $item->phone;
        $this->website = $item->website;
      $this->id_editing = $id;
      $this->modal_title = __('Editing ').$this->name;
      $this->modal_subtitle = __('Check the informations about this site.');
      $this->modal_submit_message = __('Edit');
      $this->modal_open = true;

      if(Storage::exists('pictures/site-'.$this->site->id.'/profile')){
$this->picture_url = Storage::url('pictures/site-'.$this->site->id.'/profile');
      }else{
        $this->picture_url = null;
      }

      if(Storage::exists('pictures/site-'.$this->site->id.'/banner')){
$this->banner_url = Storage::url('pictures/site-'.$this->site->id.'/banner');
      }else{
        $this->banner_url = null;
      }
    }

    public function delete_item($id){
      $item = Site::find($id);
      $item->delete();
      $this->dispatch('action_ok', title: 'Site deleted', message: 'Your modifications has been registered !');
      $this->render();
    }

    public function mount(){
      $this->modal_subtitle = __('Get started by filling in the information below to create a new site.');
      $this->modal_title = __('New site');
      $this->modal_submit_message = __('Create');
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
        <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Sites')}}</h1>
        <p class="mt-2 text-sm text-gray-700">{{__('Registered climbing sites in the website')}}</p>
      </div>
      <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
        <x-button wire:click="open_modal()" type="button">{{__('Add site')}}</x-button>
        
      </div>
    </div>
    <div class="mt-8 flow-root">
      <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
          <table class="min-w-full divide-y divide-gray-300">
            <thead>
              <tr>
                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-3">{{__('Name')}}</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Adress')}}</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Number of areas')}}</th>
                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-3">
                  <span class="sr-only">Edit</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white"> @foreach ($this->sites as $site) <tr class="even:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">{{$site->name}}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{$site->adress}}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{$site->areas->count()}}</td>
                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-3">
                  <a wire:navigate href="{{route('admin.areas.manage', ['site'=>$site->id ])}}" class="text-gray-600 hover:text-gray-900 mr-2"><button><x-icon-see/></button></a>
                  <button wire:click="open_item({{$site->id}})" class="text-gray-600 hover:text-gray-900 mr-2"><x-icon-edit/></button>
                  <button type="button" wire:click="delete_item({{$site->id}})" wire:confirm="{{__('Are you sure you want to delete this site?')}}" class="text-red-600 hover:text-red-900">
                    <x-icon-delete/>
                  </button>
                </td>
              </tr> @endforeach
            </tbody>
          </table>
          {{ $this->sites->links() }}
        </div>
      </div>
    </div>
  </div>
<div x-data="{ open: $wire.entangle('modal_open') }">
  <div class="relative z-10 overflow-y-auto" aria-labelledby="slide-over-title" role="dialog" aria-modal="true" x-show="open" style="display: none;" x-trap.noscroll="open">
    <!-- Background backdrop, show/hide based on slide-over state. -->
    <div class="fixed inset-0"></div>
    <div class="fixed inset-0 overflow-hidden">
      <div class="absolute inset-0 overflow-hidden">
        <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
          <div class="pointer-events-auto w-screen max-w-2xl" x-show="open" x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
            <form wire:submit="save" class="flex h-full flex-col bg-white shadow-xl">
              <div class="flex-1 overflow-y-auto">
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
                <div class="overflow-y-auto space-y-6 py-6 sm:space-y-0 sm:divide-y sm:divide-gray-200 sm:py-0">
                  <!-- Project name -->
                  <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                      <x-label for="name" value="{{ __('Site name') }}" />
                    <div class="sm:col-span-2">
                      <x-input wire:model="name" type="text" name="name" id="project-name" class="block w-full" />
                      <x-input-error for="name" class="mt-2" />
                    </div>
                  </div>
                  <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                    <x-label for="description" value="{{ __('Description') }}" />
                  <div class="sm:col-span-2">
                    <textarea wire:model="description" id="description" name="description" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6"></textarea>
                    <x-input-error for="description" class="mt-2" />
                  </div>
                </div>
                  <!-- Project description -->
                  <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                      <x-label for="adress" value="{{ __('Adress') }}" />
                    <div class="sm:col-span-2">
                      <textarea wire:model="adress" id="adress" name="adress" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6"></textarea>
                      <x-input-error for="adress" class="mt-2" />
                    </div>
                  </div>
                  <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                    <x-label for="name" value="{{ __('Coordinates') }}" />
                  <div class="sm:col-span-2">
                    <x-input wire:model="coord" type="text" name="coord" id="project-coord" class="block w-full" />
                    <x-input-error for="coord" class="mt-2" />
                  </div>
                </div>
                  <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                    <x-label for="name" value="{{ __('Website') }}" />
                    <div class="sm:col-span-2">
                      <x-input wire:model="website" type="url" name="website" id="project-website" class="block w-full" />
                      <x-input-error for="website" class="mt-2" />
                    </div>
                  </div>
                  <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                    <x-label for="mail" value="{{ __('Email') }}" />
                    <div class="sm:col-span-2">
                      <x-input wire:model="mail" type="email" name="mail" id="project-mail" class="block w-full" />
                      <x-input-error for="mail" class="mt-2" />
                    </div>
                  </div>
                  <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                    <x-label for="name" value="{{ __('Phone number') }}" />
                  <div class="sm:col-span-2">
                    <x-input wire:mode="phone" type="tel" name="phone" id="project-phone" class="block w-full" />
                    <x-input-error for="phone" class="mt-2" />
                  </div>
                </div>
                <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                  <x-label for="name" value="{{ __('State') }}" />
                <div class="sm:col-span-2">
                  <x-input wire:model="state" type="text" name="state" id="project-state" class="block w-full" />
                  <x-input-error for="state" class="mt-2" />
                </div>
              </div>
              <div x-data="{banner_url: $wire.entangle('banner_url')}" class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                <x-label for="banner" value="{{ __('Banner image') }}" />
                <div class="sm:col-span-2">
                <x-input wire:model="banner" type="file" name="banner" id="project-name" class="block w-full file:inline-flex file:items-center file:px-4 file:py-2 file:bg-gray-800 file:border file:border-transparent file:rounded-md file:font-semibold file:text-sm file:text-white file:tracking-widest file:hover:bg-gray-700 file:focus:bg-gray-700 file:active:bg-gray-900 file:focus:outline-none file:disabled:opacity-50 file:transition file:ease-in-out file:duration-150" />
                <x-input-error for="banner" class="mt-2" />
                <img class="rounded-lg mt-2" x-bind:src="banner_url" />
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