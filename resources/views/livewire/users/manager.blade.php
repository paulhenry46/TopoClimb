<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Site;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Attributes\Locked;

new class extends Component {
  use WithPagination;

    public User $user;
    public $modal_open;
    public $is_super_admin;
    public $modal_title;
    public $modal_subtitle;
    public $modal_submit_message;
    public $roles;
    public $sites;
    public $roles_sites = [];

    #[Locked] 
    public $authorized_sites_id = [];

    #[Validate('required|string')]
    public $name;
    #[Validate('required|string')]
    public $email;
   
    public $id_editing;
    

    public function save()
    {
        $this->validate(); 
        $errors = false;
        $roles_sites = $this->roles_sites;

            if($this->authorized_sites_id !== ['all']){
                foreach ($roles_sites as $object) {
                    if(!in_array($object['site']['id'], $this->authorized_sites_id)){
                        $errors = true;
                    }
                }
            }else{
                if(($this->is_super_admin) and (!$this->user->hasRole('super-admin'))){
                $this->user->syncRoles('super-admin');
                $errors = true;
                }elseif($this->user->hasRole('super-admin')){
                    $this->user->removeRole('super-admin');
                }
            }

            if(!$errors){
                $new_roles = [];
                foreach($roles_sites as $object){
                    switch ($object['role']['id']) {
                        case 1:
                        $name = "owner";
                        break;
                        case 2:
                        $name = "admin";
                        break;
                        case 3:
                        $name = "opener";
                        break;
                    }
                    $item = $name . '.' . $object['site']['id'];
                    array_push($new_roles, $item);
                }
                $this->user->syncRoles($new_roles);
            }
        

        $this->modal_open = false;
        $this->dispatch('action_ok', title: 'Site saved', message: 'Your modifications has been registered !');

        $this->render();
    }

    #[Computed]
    public function users()
    {
        return User::paginate(10);
    }

    public function open_item($id){
      $this->roles_sites = [];
      $this->user = User::find($id);
      $this->name = $this->user->name;
      $this->email = $this->user->email;

      $this->id_editing = $id;
      $this->modal_title = __('Editing ').$this->name;
      $this->modal_subtitle = __('Check the informations about this site.');
      $this->modal_submit_message = __('Edit');
      $this->modal_open = true;

      $this->is_super_admin = $this->user->hasRole('super-admin');

      $roles = $this->user->getRoleNames();
      //$roles = ['owner.1', 'opener.1'];

      foreach ($roles as $role) {
        if($role !== 'super-admin'){
          list($type, $site_id) = explode('.', $role); 
          $site = Site::find($id);
          switch ($type) {
            case "owner":
             $role_id = 1;
              break;
            case "admin":
             $role_id = 2;
              break;
            case "opener":
              $role_id = 3;
              break;
          }
          $item = ['site' => ['name' => $site->name, 'id' => $site->id], 'role' => ['name' => ucfirst($type), 'id' => $role_id]];
          array_push($this->roles_sites, $item);
        }
      }
    }


    public function mount(){
      $this->roles = [1=>__('Owner'), 2=>__('Admin'), 3=>__('Opener')];
      $this->modal_subtitle = __('Get started by filling in the information below to create a new site.');
      $this->modal_title = __('New site');
      $this->modal_submit_message = __('Create');

      $roles = auth()->user()->getRoleNames();

      if($roles->contains('super-admin')){
        $this->sites = Site::where('id', '!=', 1)->pluck('name', 'id')->toArray();
        $this->authorized_sites_id = ['all'];
      }else{
        $sites_id = [];
        foreach ($roles as $role) {
          list($type, $site_id) = explode('.', $role); 
          if($type == 'owner'){
            array_push($sites_id, $site_id);
          }
        }
        $this->authorized_sites_id = $sites_id;
        $this->sites = Site::whereIn('id', $sites_id)->pluck('name','id')->toArray();
      }
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
              <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Users')}}</h1>
              <p class="mt-2 text-sm text-gray-700">{{__('Registered users sites in the website')}}</p>
          </div>
          <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
              <x-button disabled type="button">{{__('Add user')}}</x-button>
          </div>
      </div>
      <div class="mt-8 flow-root">
          <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
              <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                  <table class="min-w-full divide-y divide-gray-300">
                      <thead>
                          <tr>
                              <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-3">{{__('Name')}}</th>
                              <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Email')}}</th>
                              <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Highter role')}}</th>
                              <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-3"> <span class="sr-only">Edit</span> </th>
                          </tr>
                      </thead>
                      <tbody class="bg-white"> 
                        @foreach ($this->users as $user) <tr class="even:bg-gray-50">
                              <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">{{$user->name}}</td>
                              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{$user->email}}</td>
                              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                @php
                                    $hr = $user->hr();
                                @endphp
                                @if ($hr == 0)
                                <div class='text-amber-500 flex aling-center gap-x-1 font-bold'>
                                    <x-icon-police />
                                    {{ ('Super Admin') }}
                                </div>
                                @elseif($hr == 1)
                                <div class='text-red-500 flex aling-center gap-x-1 font-bold'>
                                    <x-icon-account-manager />
                                    {{ __('Owner') }}
                                </div>
                                @elseif ($hr == 2)
                                <div class='text-blue-500 flex aling-center gap-x-1 font-bold'>
                                    <x-icon-settings />
                                    {{ __('Local admin') }}
                                </div>
                                @elseif($hr == 3)
                                <div class='text-green-500 flex aling-center gap-x-1 font-bold'>
                                    <x-icon-edit />
                                    {{ __('Opener') }}
                                </div>
                                @endif

                              </td>
                              <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-3"> <button class='cursor-pointer' wire:click="open_item({{$user->id}})" class="text-gray-600 hover:text-gray-900 mr-2">
                                      <x-icon-edit />
                                  </button> </td>
                          </tr> 
                        @endforeach 
                      </tbody>
                  </table> {{ $this->users->links() }}
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
                          <form wire:submit="save" class="flex h-full flex-col bg-white shadow-xl" x-data="{
            roles_sites: $wire.$entangle('roles_sites'), 
            roles: $wire.roles, 
            sites: $wire.sites, 
            super_admin: $wire.$entangle('is_super_admin'),
            current_role_id: 0,
            current_site_id: 0,
            remove(id){this.roles_sites = this.roles_sites.filter(function(el) { return el.site.id != id; });},
            add_role(){this.roles_sites.push({site: {name : this.sites[this.current_site_id], id:Number(this.current_site_id) }, role:{name : this.roles[this.current_role_id], id:Number(this.current_role_id)}})}
            }
            ">
                              <div class="flex-1 overflow-y-auto">
                                  <!-- Header -->
                                  <div class="bg-gray-50 px-4 py-6 sm:px-6">
                                      <div class="flex items-start justify-between space-x-3">
                                          <div class="space-y-1">
                                              <h2 class="text-base font-semibold leading-6 text-gray-900" id="slide-over-title">{{$this->modal_title}}</h2>
                                              <p class="text-sm text-gray-500">{{$this->modal_subtitle}}</p>
                                          </div>
                                          <div class="flex h-7 items-center"> <button x-on:click="open = ! open" type="button" class="cursor-pointer relative text-gray-400 hover:text-gray-500"> <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                  </svg> </button> </div>
                                      </div>
                                  </div> <!-- Divider container -->
                                  <div class="overflow-y-auto space-y-6 py-6 sm:space-y-0 sm:divide-y sm:divide-gray-200 sm:py-0">
                                      <!-- Project name -->
                                      <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                                          <x-label for="name" value="{{ __('Name') }}" />
                                          <div class="sm:col-span-2">
                                              <x-input wire:model="name" type="text" name="name" id="project-name" class="block w-full" />
                                              <x-input-error for="name" class="mt-2" />
                                          </div>
                                      </div>
                                      <div class=" px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                                          <x-label for="name" value="{{ __('Email') }}" />
                                          <div class="sm:col-span-2">
                                              <x-input wire:model="email" type="email" name="name" id="project-name" class="block w-full" />
                                              <x-input-error for="name" class="mt-2" />
                                          </div>
                                      </div>
                                      <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                                          <x-label for="name" value="{{ __('Roles') }}" />
                                          <div class="sm:col-span-2"> 


                                            <div >
                                                <div class='flex justify-between mt-2 items-center'>
                                                    <div class='flex items-center'>
                                                      
                                                      <span class="text-amber-700 bg-amber-100 inline-flex items-center gap-x-1.5 rounded-md  px-2 py-1  font-medium mr-1"  >
                                                        <svg class="h-1.5 w-1.5 fill-amber-500 " viewBox="0 0 6 6" aria-hidden="true" >
                                                          <circle cx="3" cy="3" r="3"></circle>
                                                        </svg>
                                                        <span>{{__('Super Admin')}}</span>
                                                      
                                                    </div>
                                                    @can('super-admin')
                                                    <x-checkbox x-model="super_admin" />
                                                    @endcan
                                                </div>
                                            </div>
                                            <div x-show='!super_admin' x-collapse>
                                              <template x-for="object in roles_sites">
                                                  <div class='flex justify-between mt-2 items-center'>
                                                      <div class='flex items-center'>
                                                        
                                                        <span class="inline-flex items-center gap-x-1.5 rounded-md  px-2 py-1  font-medium mr-1"  x-bind:class="{ 'text-red-700 bg-red-100' : object.role.id == 1, 'text-blue-700 bg-blue-100' : object.role.id == 2, 'text-green-700 bg-green-100' : object.role.id == 3 }">
                                                          <svg class="h-1.5 w-1.5 " viewBox="0 0 6 6" aria-hidden="true" x-bind:class="{ 'fill-red-500' : object.role.id == 1, 'fill-blue-500' : object.role.id == 2, 'fill-green-500' : object.role.id == 3 }">
                                                            <circle cx="3" cy="3" r="3"></circle>
                                                          </svg>
                                                          <span x-text='object.role.name'></span>
                                                        </span> {{ __('at') }}
                                                          <p class='ml-1' x-text='object.site.name'></p>
                                                      </div>
                                                      <x-button type='button' @click='remove(object.site.id)'> {{ __('Remove') }} </x-button>
                                                  </div>
                                              </template>
                                              <div class=" mt-4">
                                                  <x-label for="site" value="{{ __('Select Site') }}" /> <select x-model="current_site_id" class=" block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6">
                                                      <option value="">{{ __('Select a site') }}</option> <template x-for="(name, key) in sites" :key="key">
                                                          <option :value="key" x-text="name"></option>
                                                      </template>
                                                  </select>
                                              </div> <!-- Role Selection -->
                                              <div class=" mt-4">
                                                  <x-label for="role" value="{{ __('Assign Role') }}" /> <select x-model="current_role_id" class=" block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6">
                                                      <option value="">{{ __('Select a role') }}</option> <template x-for="(name, key) in roles" :key="key">
                                                          <option :value="key" x-text="name"></option>
                                                      </template>
                                                  </select>
                                              </div>
                                              <x-button class='mt-2' type='button' @click='add_role()'>{{__('Add role')}}</x-button>
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
                              </div>
                          </form>
                      </div>
                  </div>
              </div>
          </div>
      </div>
  </div>
</div>