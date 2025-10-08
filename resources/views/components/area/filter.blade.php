
<div class="bg-white overflow-hidden sm:rounded-lg mt-2">
    <div class="px-4 sm:px-6 lg:px-8 py-8">
      <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto stroke-indigo-500" x-data="{ expanded: false }">
          <div class="flex justify-between items-center" >
            <div @click="expanded = ! expanded" >
              <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Filters')}}</h1>
              <p class="mt-2 text-sm text-gray-700">{{__('Choose which routes you want to see.')}}</p>
            </div> 
             <div class="sm:ml-16 sm:mt-0 sm:flex-none">
              <button @click="expanded = ! expanded" type="button" class="cursor-pointer inline-flex items-center px-2 py-2 border border-transparent rounded-md font-semibold text-sm tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-200 focus:outline-hidden transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" x-show="!expanded" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/>
                </svg>
                <svg x-show="expanded" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="m432-480 156 156q11 11 11 28t-11 28q-11 11-28 11t-28-11L348-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 28-11t28 11q11 11 11 28t-11 28L432-480Z"/>
                </svg>
              </button>
            </div>
          </div>
          <div class=" rounded-xl pt-4" x-show="expanded" x-collapse.duration.1000ms >
            <div class="grid grid-cols-2">
              <div class="col-span-1">
                <div class="space-y-2 px-4">
                  <div class="w-full">
                    <x-label for="name" value="{{ __('Search') }}" />
                    <x-input wire:model.live="search" type="text" name="name" id="project-name" class="block w-full mt-2" />
                    <x-input-error for="name" class="mt-2" />
                  </div>
                </div>
              </div>
              <div class="col-span-1">
                <div class="space-y-2 px-4">
                  <div class="w-full">
                    <x-label for="name" value="{{ __('Cotation') }}" />
                    <div class="mt-2 sm:flex sm:items-center sm:gap-2">
                      <div class='flex gap-2 items-center'>
                        <span class=''>
                      {{ __('From') }}
                        </span>
                      <select wire:model.live='cotation_from' id="location" name="location" class=" block w-full sm:w-24 rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6">
                        <option value="0">0</option> @foreach ($this->cotations as $key => $value) <option value="{{ $value }}">{{ $key }}</option> @endforeach
                      </select>
                    </div>
                    <div class='flex gap-2 items-center mt-1 sm:mt-0'>
                      <span class=''>
                      {{ __('to') }}
                      </span>
                      <select wire:model.live='cotation_to' id="location" name="location" class=" block w-full sm:w-24 rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6">
                        <option value="0">+ ∞</option> @foreach ($this->cotations as $key => $value) <option value="{{ $value }}">{{ $key }}</option> @endforeach
                      </select>
                    </div>
                      <x-input-error for="name" class="mt-2" />
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-span-1" x-data="{tags: $wire.tags_available, 
                SelectedID: $wire.entangle('tags_id'), 
                SelectedTags: $wire.entangle('tags_choosen'),
                term : '',
                showListe: false, 
                toogle(id){
                          if (this.SelectedID.includes(id)) {
                              this.SelectedID = this.SelectedID.filter(item => item !== id);
                          } else {
                              this.SelectedID.push(id);
                          }
                          this.SelectedTags = this.tags.filter(obj => {
                              return this.SelectedID.includes(obj.id)
                            })
                          this.term = '';
                          $wire.$refresh();
                      }
                  }">
                <div class="space-y-2 px-4">
                  <div class="flex mt-3">
                    <x-label class="mr-1" for="creators" value="{{ __('Tags') }} : " />
                    <template x-for="tag in SelectedTags">
                      <span x-text="tag['name']" class=" mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                        <svg class="h-1.5 w-1.5 fill-gray-500" viewBox="0 0 6 6" aria-hidden="true">
                          <circle cx="3" cy="3" r="3"></circle>
                        </svg>
                      </span>
                    </template>
                  </div>
                  <div @click.outside="showListe = false" class="sm:col-span-2">
                    <div>
                      <div class="relative mt-2 ">
                        <input placeholder="Add a tag" x-model="term" @click="showListe = true" id="combobox" type="text" class="mt-2 w-full rounded-md border-0 bg-white py-1.5 pl-3 pr-12 text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6" role="combobox" aria-controls="options" aria-expanded="false">
                        <ul x-show="showListe" class=" z-20 mt-1 max-h-20 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-hidden sm:text-sm" id="options" role="listbox">
                          <template x-for="tag in tags">
                            <li x-show="!(term.length > 0 && !tag['name'].toLowerCase().includes(term.toLowerCase()))" :class="SelectedID.includes(tag['id']) ? 'font-semibold' : 'text-gray-900'" @click="toogle(tag['id'])" class="hover:bg-gray-100 relative cursor-default select-none py-2 pl-8 pr-4 text-gray-900" id="option-0" role="option" tabindex="-1">
                              <span class="block truncate" x-text="tag['name']"></span>
                              <span :class="SelectedID.includes(tag['id']) ? 'text-gray-600' : ' hidden'" class="absolute inset-y-0 left-0 flex items-center pl-1.5 text-gray-600">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                  <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                </svg>
                              </span>
                            </li>
                          </template>
                        </ul>
                      </div>
                    </div>
                    <x-input-error for="tags" class="mt-2" />
                  </div>
                </div>
              </div> @if($this->area->sectors->count() > 1) <div class="col-span-1">
                <div class="space-y-2 px-4">
                  <div class="w-full mt-3">
                    <x-label for="name" value="{{ __('Sector') }}" />
                    <div class="mt-4">
                      <select wire:model.live="selected_sector" id="location" name="location" class=" block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6">
                        <option value="0">{{__('All')}}</option> @foreach ($this->area->sectors as $sector) <option value="{{ $sector->id }}">{{ $sector->name }}</option> @endforeach
                      </select>
                      <x-input-error for="name" class="mt-2" />
                    </div>
                  </div>
                </div>
              </div> @endif @if($this->area->type == 'trad') <div class="col-span-1">
                <div class="space-y-2 px-4">
                  <div class="w-full mt-3">
                    <x-label for="name" value="{{ __('Line') }}" />
                    <div class="mt-4">
                      <select wire:model.live="selected_line" id="location" name="location" class=" block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6">
                        <option value="0">{{__('All')}}</option> @foreach ($lines as $line) <option value="{{ $line->id }}">{{ $line->local_id }}</option> @endforeach
                      </select>
                      <x-input-error for="name" class="mt-2" />
                    </div>
                  </div>
                </div>
              </div> @endif 
              @auth
              <div class="col-span-1">
                <div class="space-y-2 px-4">
                  <div class="w-full mt-3">
                    <x-label for="name" value="{{ __('State') }}" />
                    <div class="mt-4">
                      <select wire:model.live="user_state" id="location" name="location" class=" block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6">
                        <option value="all">{{__('All')}}</option>
                        <option value="success">{{__('Success')}}</option>
                        <option value="fail">{{__('Not climbed')}}</option>
                      </select>
                      <x-input-error for="name" class="mt-2" />
                    </div>
                  </div>
                </div>
              </div>
              @endauth
              <div class="col-span-1">
                <div class="space-y-2 px-4">
                  <div class="w-full mt-3">
                    <x-label for="name" value="{{ __('Other properties') }}" />
                    <div class="mt-4">
                      
                      <div class="flex items-center" x-data="{enabled: $wire.entangle('new'), toogle(){this.enabled = !this.enabled;
      $wire.set('new', this.enabled);}}">
  <!-- Enabled: "bg-indigo-600", Not Enabled: "bg-gray-200" -->
  <button x-on:click='toogle()' :class="enabled ? 'bg-gray-600' : 'bg-gray-200'" type="button" class="bg-gray-200 relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-600 focus:ring-offset-2" role="switch" aria-checked="false" aria-labelledby="annual-billing-label">
    <!-- Enabled: "translate-x-5", Not Enabled: "translate-x-0" -->
    <span  :class="enabled ? 'translate-x-5' : 'translate-x-0'" aria-hidden="true" class="translate-x-0 pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
  </button>
  <span class="ml-3 text-sm" id="annual-billing-label">
    <span class="font-medium text-gray-900">{{__('Only new routes') }}</span>
  </span>
</div>

@if($admin == 'true')
<div class="flex items-center mt-2" x-data="{enabled: $wire.entangle('own'), toogle(){this.enabled = !this.enabled;
      $wire.set('own', this.enabled);}}">
  <!-- Enabled: "bg-indigo-600", Not Enabled: "bg-gray-200" -->
  <button x-on:click='toogle()' :class="enabled ? 'bg-gray-600' : 'bg-gray-200'" type="button" class="bg-gray-200 relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-600 focus:ring-offset-2" role="switch" aria-checked="false" aria-labelledby="annual-billing-label">
    <!-- Enabled: "translate-x-5", Not Enabled: "translate-x-0" -->
    <span  :class="enabled ? 'translate-x-5' : 'translate-x-0'" aria-hidden="true" class="translate-x-0 pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
  </button>
  <span class="ml-3 text-sm" id="annual-billing-label">
    <span class="font-medium text-gray-900">{{__('Only my routes') }}</span>
  </span>
</div>

<div class="flex items-center mt-2" x-data="{enabled: $wire.entangle('deleted'), toogle(){this.enabled = !this.enabled;
      $wire.set('deleted', this.enabled);}}">
  <!-- Enabled: "bg-indigo-600", Not Enabled: "bg-gray-200" -->
  <button x-on:click='toogle()' :class="enabled ? 'bg-gray-600' : 'bg-gray-200'" type="button" class="bg-gray-200 relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-600 focus:ring-offset-2" role="switch" aria-checked="false" aria-labelledby="annual-billing-label">
    <!-- Enabled: "translate-x-5", Not Enabled: "translate-x-0" -->
    <span  :class="enabled ? 'translate-x-5' : 'translate-x-0'" aria-hidden="true" class="translate-x-0 pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
  </button>
  <span class="ml-3 text-sm" id="annual-billing-label">
    <span class="font-medium text-gray-900">{{__('Only deleted Routes') }}</span>
  </span>
</div>

@endif
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>