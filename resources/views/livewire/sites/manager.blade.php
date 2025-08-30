<?php

use Livewire\Volt\Component;
use App\Models\Site;
use App\Models\User;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

new class extends Component {
  use WithPagination, WithFileUploads;

    public Site $site;
    public User $user;
    #[Locked] 
    public $is_super_admin;
    #[Locked] 
    public $authorized_sites = [];
    #[Locked]
    public $viewable_sites =[];
    public $modal_open;
    public $modal_title;
    public $modal_subtitle;
    public $modal_submit_message;

    #[Validate('required|string')]
    public $name;
    #[Validate('required|string')]
    public $address;
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

    #[Validate('image|nullable')]
    public $picture;
    public $picture_url;

    #[Validate('image|nullable')]
    public $banner;
    public $banner_url;
    
    public $slug;
    public $id_editing;
    

    public function save()
    {
        $this->validate(); 
        $this->slug = Str::slug($this->name, '-');

        $this->site = Site::create(
            $this->pull(['name', 'address', 'slug'])
        );
        $this->site->state = $this->state;
        $this->site->coord = $this->coord;
        $this->site->description = $this->description;
        $this->site->email = $this->mail;
        $this->site->phone = $this->phone;
        $this->site->website = $this->website;
        $this->site->save();
        
        if($this->picture !== null){
          $this->picture->storeAs(path: 'pictures/site-'.$this->site->id.'', name: 'profile');
        $this->picture = null;
        }

        if($this->banner !== null){
          $this->banner->storeAs(path: 'pictures/site-'.$this->site->id.'', name: 'banner');
        $this->banner = null;
        }
        
        $this->modal_open = false;
        $this->dispatch('action_ok', title: 'Site saved', message: 'Your modifications has been registered !');

        $id = $this->site->id;

        $owner = Role::create(['name' => 'owner.'.$id.'']);
        $admin = Role::create(['name' => 'admin.'.$id.'']);
        $opener = Role::create(['name' => 'opener.'.$id.'']);

        $p_1 = Permission::create(['name' => 'routes.'.$id.'']);

        $p_2 = Permission::create(['name' => 'areas.'.$id.'']);
        $p_3 = Permission::create(['name' => 'lines-sectors.'.$id.'']);
        
        $p_4 = Permission::create(['name' => 'site.'.$id.'']);
        $p_5 = Permission::create(['name' => 'users.'.$id.'']); 

        $owner->givePermissionTo([$p_1, $p_2, $p_3, $p_4, $p_5]);
        $admin->givePermissionTo([$p_1, $p_2, $p_3]);
        $opener->givePermissionTo($p_1);

        $this->render();
    }

    #[Computed]
    public function sites()
    {
        if($this->is_super_admin){
          return Site::where('id', '!=', 1)->paginate(10);
        }else{
          return Site::whereIn('id', $this->viewable_sites)(10);
        }
    }


    public function delete_item($id){
      $item = Site::find($id);
      $item->delete();
      $this->dispatch('action_ok', title: 'Site deleted', message: 'Your modifications has been registered !');
      $this->render();
    }

    public function mount(){

      
      $this->user = auth()->user();
      $this->is_super_admin = $this->user->hasRole('super-admin');

      $roles = $this->user->getRoleNames();
      //$roles = ['owner.1', 'opener.1'];

      foreach ($roles as $role) {
        if($role !== 'super-admin'){
          list($type, $site_id) = explode('.', $role);

          array_push($this->viewable_sites, $site_id);
        }
      }

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
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('address')}}</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Number of areas')}}</th>
                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-3">
                  <span class="sr-only">Edit</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white"> @foreach ($this->sites as $site) <tr class="even:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">{{$site->name}}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{$site->address}}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{$site->areas->count()}}</td>
                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-3">
                  <a wire:navigate href="{{route('admin.areas.manage', ['site'=>$site->id ])}}" class="text-gray-600 hover:text-gray-900 mr-2"><button class='cursor-pointer'><x-icons.icon-see/></button></a>
                  
                  
                </td>
              </tr> @endforeach
            </tbody>
          </table>
          {{ $this->sites->links() }}
        </div>
      </div>
    </div>
  </div>
<x-drawer open='modal_open' save_method_name='save' :title="$this->modal_title" :subtitle="$this->modal_subtitle">
<div>
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
                    <textarea wire:model="description" id="description" name="description" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6"></textarea>
                    <x-input-error for="description" class="mt-2" />
                  </div>
                </div>
                  <!-- Project description -->
                  <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                      <x-label for="address" value="{{ __('address') }}" />
                    <div class="sm:col-span-2">
                      <textarea wire:model="address" id="address" name="address" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6"></textarea>
                      <x-input-error for="address" class="mt-2" />
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
                <x-input wire:model="banner" type="file" name="banner" id="project-name" class="block w-full file:inline-flex file:items-center file:px-4 file:py-2 file:bg-gray-800 file:border file:border-transparent file:rounded-md file:font-semibold file:text-sm file:text-white file:tracking-widest file:hover:bg-gray-700 file:focus:bg-gray-700 file:active:bg-gray-900 file:focus:outline-hidden file:disabled:opacity-50 file:transition file:ease-in-out file:duration-150" />
                <x-input-error for="banner" class="mt-2" />
                <img class="rounded-lg mt-2" x-bind:src="banner_url" />
              </div>
            </div>
            <div x-data="{picture_url: $wire.entangle('picture_url')}" class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
              <x-label for="name" value="{{ __('Picture') }}" />
              <div class="sm:col-span-2">
              <x-input wire:model="picture" type="file" name="picture" id="project-name" class="block w-full file:inline-flex file:items-center file:px-4 file:py-2 file:bg-gray-800 file:border file:border-transparent file:rounded-md file:font-semibold file:text-sm file:text-white file:tracking-widest file:hover:bg-gray-700 file:focus:bg-gray-700 file:active:bg-gray-900 file:focus:outline-hidden file:disabled:opacity-50 file:transition file:ease-in-out file:duration-150" />
              <x-input-error for="picture" class="mt-2" />
              <img class="rounded-lg mt-2" x-bind:src="picture_url" />
            </div>
          </div>
              </div>
              <x-slot name="footer">
                <div class="flex justify-end space-x-3">
                  <x-secondary-button x-on:click="open = ! open" type="button">{{__('Cancel')}}</x-secondary-button>
                  <x-button type="submit">{{$this->modal_submit_message}}</x-button>
                </div>
              </x-slot>
              </x-drawer>

</div>