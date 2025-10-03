<?php

use Livewire\Volt\Component;
use App\Models\Site;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
new class extends Component {
  use WithFileUploads;

    public Site $site;

    #[Validate('required|string')]
    public $name;
    #[Validate('required|string')]
    public $address;
    #[Validate('string|nullable')]
    public $state;
    #[Validate('email|nullable')]
    public $email;
    #[Validate('string|nullable')]
    public $coord;
    #[Validate('string|nullable')]
    public $phone;
    #[Validate('string|nullable')]
    public $website;
    #[Validate('string|nullable')]
    public $description;

    #[Validate('image|nullable')]
    public $picture;
    public $picture_url;

    #[Validate('image|nullable')]
    public $banner;
    public $banner_url;
    
    public $slug;
    

    public function save()
    {
        $this->validate(); 
        $this->slug = Str::slug($this->name, '-');

          $this->site->name = $this->name;
          $this->site->address = $this->address;
          $this->site->slug = $this->slug;
          $this->site->state = $this->state;
          $this->site->coord = $this->coord;
          $this->site->description = $this->description;
          $this->site->email = $this->email;
          $this->site->phone = $this->phone;
          $this->site->website = $this->website;
          $this->site->save();
          $this->dispatch('action_ok', title: 'Site saved', message: 'Your modifications has been registered !');
    
        if($this->picture !== null){
          $this->picture->storeAs(path: 'pictures/site-'.$this->site->id.'', name: 'profile');
        $this->picture = null;
        }

        if($this->banner !== null){
          $this->banner->storeAs(path: 'pictures/site-'.$this->site->id.'', name: 'banner');
        $this->banner = null;
        }
        
        $this->render();
    }

    public function delete_item($id){
      $item = Site::find($id);
      $item->delete();
      $this->dispatch('action_ok', title: 'Site deleted', message: 'Your modifications has been registered !');
      $this->render();
    }

    public function mount(Site $site){
      $this->site = $site;

      $this->name = $site->name;
    $this->address = $site->address;
    $this->state = $site->state;
    $this->email = $site->email;
    $this->coord = $site->coord;
    $this->phone = $site->phone;
    $this->website = $site->website;
    $this->description = $site->description;

    // Set URLs for existing images if available
    $this->picture_url = Storage::exists('pictures/site-' . $site->id . '/profile') ? Storage::url('pictures/site-' . $site->id . '/profile') : null;
    $this->banner_url = Storage::exists('pictures/site-' . $site->id . '/banner') ? Storage::url('pictures/site-' . $site->id . '/banner') : null;
    }
}; ?>

<div>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center mb-3">
      <div class="sm:flex-auto">
        <h1 class="text-base font-semibold leading-6 text-gray-900">{{$this->name}}</h1>
        <p class="mt-2 text-sm text-gray-700">{{__('Édit contact and other other data of this website')}}</p>
      </div>
      <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
        <x-button href="{{ route('admin.site.stats', ['site'=> $site]) }}" wire:navigate><x-icons.icon-graph/>  <p class='ml-2'>{{ __('Stats ') }}</p></x-button>
        <x-button wire:navigate href="{{ route('site.view', $this->site->slug) }}" ><x-icons.icon-see class='mr-2'/> <p class='ml-2'>{{__('Site page')}}</p> </x-button>
        <livewire:sites.grade :site="$this->site" />
        <livewire:contests.button-indicator :site="$this->site" />
      </div>
    </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <!-- Site Name -->
        <div>
            <x-label for="name" value="{{ __('Site Name') }}" />
            <x-input wire:model="name" type="text" id="name" class="block w-full mt-1" />
            <x-input-error for="name" class="mt-2" />
        </div>
    
        <div>
          <x-label for="state" value="{{ __('State') }}" />
          <x-input wire:model="state" type="text" id="state" class="block w-full mt-1" />
          <x-input-error for="state" class="mt-2" />
      </div>

        <!-- Address -->
        <div>
            <x-label for="address" value="{{ __('Address') }}" />
            <textarea wire:model="address" id="address" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6"></textarea>
            <x-input-error for="address" class="mt-2" />
        </div>
    
        <!-- Description -->
        <div>
            <x-label for="description" value="{{ __('Description') }}" />
            <textarea wire:model="description" id="description" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6"></textarea>
            <x-input-error for="description" class="mt-2" />
        </div>
    
        <!-- Coordinates -->
        <div>
            <x-label for="coord" value="{{ __('Coordinates') }}" />
            <x-input wire:model="coord" type="text" id="coord" class="block w-full mt-1" />
            <x-input-error for="coord" class="mt-2" />
        </div>
    
        <!-- Website -->
        <div>
            <x-label for="website" value="{{ __('Website') }}" />
            <x-input wire:model="website" type="url" id="website" class="block w-full mt-1" />
            <x-input-error for="website" class="mt-2" />
        </div>
    
        <!-- Email -->
        <div>
            <x-label for="email" value="{{ __('Email') }}" />
            <x-input wire:model="email" type="email" id="email" class="block w-full mt-1" />
            <x-input-error for="email" class="mt-2" />
        </div>
    
        <!-- Phone -->
        <div>
            <x-label for="phone" value="{{ __('Phone Number') }}" />
            <x-input wire:model="phone" type="tel" id="phone" class="block w-full mt-1" />
            <x-input-error for="phone" class="mt-2" />
        </div>
    
        <!-- State -->
        
    
        <!-- Banner Image -->
        <div>
            <x-label for="banner" value="{{ __('Banner Image') }}" />
            <x-input wire:model="banner" type="file" name="banner" id="project-name" class="mt-1 block w-full file:inline-flex file:items-center file:px-4 file:py-2 file:bg-gray-800 file:border file:border-transparent file:rounded-md file:font-semibold file:text-sm file:text-white file:tracking-widest file:hover:bg-gray-700 file:focus:bg-gray-700 file:active:bg-gray-900 file:focus:outline-hidden file:disabled:opacity-50 file:transition file:ease-in-out file:duration-150" />
            <x-input-error for="banner" class="mt-2" />
        </div>
    
        <!-- Picture -->
        <div>
            <x-label for="picture" value="{{ __('Profile picture') }}" />
            <x-input wire:model="picture" type="file" name="picture" id="project-name" class="mt-1 block w-full file:inline-flex file:items-center file:px-4 file:py-2 file:bg-gray-800 file:border file:border-transparent file:rounded-md file:font-semibold file:text-sm file:text-white file:tracking-widest file:hover:bg-gray-700 file:focus:bg-gray-700 file:active:bg-gray-900 file:focus:outline-hidden file:disabled:opacity-50 file:transition file:ease-in-out file:duration-150" />
            <x-input-error for="picture" class="mt-2" />
        </div>
    </div>
    
    <!-- Submit Button -->
  </div>
  <div class="flex items-center justify-end px-4 py-3 bg-gray-50 text-end sm:px-6 shadow-sm sm:rounded-bl-md sm:rounded-br-md">
    <x-button wire:click="save" type="button">{{ __('Save') }}</x-button>
</div>
</div>