<?php

use Livewire\Volt\Component;
use App\Models\Area;
use App\Models\Site;
use App\Models\Sector;
use App\Models\Line;
use App\Models\Tag;
use App\Models\Route;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
new class extends Component {
  use WithFileUploads;

    public Area $area;
    public Site $site;
    public $sectors;

    #[Validate('required')]
    public $name;
    #[Validate('string')]
    public $comment;
    #[Validate('string')]
    public $line;
    #[Validate('required|regex:/[3-9][abc][+]?/')]
    public string $grade;
    #[Validate('required')]
    public string $color;
    #[Validate('required')]
    public $date;
    #[Validate('required')]
    public $sector_id;

    public array $tags_id;

    public $tags_available;

    
    public $creators;


    public function mount(Site $site, Area $area){
      $this->site = $site;
      $this->area = $area;
      $this->sectors = Sector::where('area_id', $this->area->id)->get();
      $this->sector_id = Sector::where('area_id', $this->area->id)->first()->id;
      $tags_temp = Tag::all()->pluck('id', 'name')->toArray();
    

      $tags = [];
     foreach($tags_temp as $name => $key){
      array_push($tags, ['name' => $name, 'id' => $key]);
     }

     $this->tags_available = $tags;
    }

    public function save(){
      $this->validate();
      $route = new Route;
      $route->name = $this->name;
      $route->comment = $this->comment;
      $route->line_id = $this->line;
      $route->grade = $this->grade;
      //$route->date = $this->date
      $route->color = $this->color;
      //$route->local_id = 1;
      $route->number = 1;//Deprecated
      $route->slug = Str::slug($this->name, '-');
      $route->save();
      $route->tags()->attach($this->tags_id);
      session(['route_creating' => $route->id]);

      $this->redirectRoute('admin.routes.path', ['site' => $this->site->id, 'area' => $this->area->id, 'route' => $route->id], navigate: true);
    }
    public function with(){
        return ['lines' => Line::where('sector_id', $this->sector_id)->get()];
    }
}; ?>

<div>
  <nav aria-label="Progress" class="p-4">
    <ol role="list" class="space-y-4 md:flex md:space-x-8 md:space-y-0"> 
      <li class="md:flex-1">
        <!-- Current Step -->
        <a class="flex flex-col border-l-4 border-indigo-600 py-2 pl-4 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4" aria-current="step">
          <span class="text-sm font-medium text-indigo-600">{{__('Step')}} 1</span>
          <span class="text-sm font-medium">{{__('Add informations')}}</span>
        </a>
      </li>
      <li class="md:flex-1">
        <!-- Upcoming Step -->
        <a class="group flex flex-col border-l-4 border-gray-200 py-2 pl-4 hover:border-gray-300 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4">
          <span class="text-sm font-medium text-gray-500 group-hover:text-gray-700">{{__('Step')}} 2</span>
          <span class="text-sm font-medium">{{__('Draw path')}}</span>
        </a>
      </li>
      @if($this->area->type == 'voie')
      <li class="md:flex-1">
        <!-- Upcoming Step -->
        <a class="group flex flex-col border-l-4 border-gray-200 py-2 pl-4 hover:border-gray-300 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4">
          <span class="text-sm font-medium text-gray-500 group-hover:text-gray-700">{{__('Step')}} 3</span>
          <span class="text-sm font-medium">{{__('Upload photo')}}</span>
        </a>
      </li>
      @endif
    </ol>
  </nav>
  <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="px-4 sm:px-6 lg:px-8 py-8">
      <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
          <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Add basic infos of your new route')}}</h1>
          <p class="mt-2 text-sm text-gray-700">{{$this->site->adress}}</p>
        </div>
      </div>
      <div class="mt-4 flow-root">
        <div class="overflow-y-auto space-y-6 py-6 sm:space-y-0 sm:divide-y sm:divide-gray-200 sm:py-0">
            <!-- Project name -->
            <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
              <x-label for="name" value="{{ __('Name') }}" />
              <div class="sm:col-span-2">
                <x-input wire:model="name" type="text" name="name" id="project-name" class="block w-full" />
                <x-input-error for="name" class="mt-2" />
              </div>
            </div>
            <!-- Project description -->
            <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
              <x-label for="comment" value="{{ __('Comment') }}" />
              <div class="sm:col-span-2">
                <textarea wire:model="comment" id="comment" name="comment" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6"></textarea>
                <x-input-error for="comment" class="mt-2" />
              </div>
            </div>
            <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
              <x-label for="line" value="{{ __('Line') }}" />
              <div class="sm:col-span-2">
                <select wire:model.live="line" id="line" name="line" class="block w-full rounded-md border-0 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6"> @foreach ($lines as $line) <option value="{{$line->id}}">{{__('Line ')}}{{$line->local_id}}</option> @endforeach </select>
                <x-input-error for="adress" class="mt-2" />
              </div>
            </div>
            <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
              <x-label for="grade" value="{{ __('Grade') }}" />
              <div class="sm:col-span-2">
                <x-input placeholder="7a+" wire:model="grade" type="text" name="grade" id="project-name" class="block w-full" />
                <x-input-error for="grade" class="mt-2" />
              </div>
            </div>
            <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
              <x-label for="color" value="{{ __('Color') }}" />
              <div class="sm:col-span-2" x-data="{colorChosen : $wire.entangle('color')}">
                <fieldset>
                  <div class="mt-4 flex items-center space-x-3">
                    <label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-none ring-lime-500" x-on:click="colorChosen = 'lime'" :class="colorChosen == 'lime' ? 'ring-2' : ''">
                      <input type="radio" name="color-choice" value="Yellow" class="sr-only" aria-labelledby="color-choice-4-label">
                      <span id="color-choice-4-label" class="sr-only">Yellow</span>
                      <span aria-hidden="true" class="h-8 w-8 bg-lime-500 rounded-full border border-black border-opacity-10"></span>
                    </label>
                    <label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-none ring-green-500" x-on:click="colorChosen = 'green'" :class="colorChosen == 'green' ? 'ring-2' : ''">
                        <input type="radio" name="color-choice" value="Green" class="sr-only" aria-labelledby="color-choice-3-label">
                        <span id="color-choice-3-label" class="sr-only">Green</span>
                        <span aria-hidden="true" class="h-8 w-8 bg-green-500 rounded-full border border-black border-opacity-10"></span>
                      </label>
                    <label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-none ring-emerald-500" x-on:click="colorChosen = 'emerald'" :class="colorChosen == 'emerald' ? 'ring-2' : ''">
                      <input type="radio" name="color-choice" value="Yellow" class="sr-only" aria-labelledby="color-choice-4-label">
                      <span id="color-choice-4-label" class="sr-only">Yellow</span>
                      <span aria-hidden="true" class="h-8 w-8 bg-emerald-500 rounded-full border border-black border-opacity-10"></span>
                    </label>
                    <label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-none ring-yellow-500" x-on:click="colorChosen = 'yellow'" :class="colorChosen == 'yellow' ? 'ring-2' : ''">
                      <input type="radio" name="color-choice" value="Yellow" class="sr-only" aria-labelledby="color-choice-4-label">
                      <span id="color-choice-4-label" class="sr-only">Yellow</span>
                      <span aria-hidden="true" class="h-8 w-8 bg-yellow-500 rounded-full border border-black border-opacity-10"></span>
                    </label>
                    <label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-none ring-orange-500" x-on:click="colorChosen = 'orange'" :class="colorChosen == 'orange' ? 'ring-2' : ''">
                      <input type="radio" name="color-choice" value="Yellow" class="sr-only" aria-labelledby="color-choice-4-label">
                      <span id="color-choice-4-label" class="sr-only">Yellow</span>
                      <span aria-hidden="true" class="h-8 w-8 bg-orange-500 rounded-full border border-black border-opacity-10"></span>
                    </label>
                    <label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-none ring-amber-500" x-on:click="colorChosen = 'amber'" :class="colorChosen == 'amber' ? 'ring-2' : ''">
                      <input type="radio" name="color-choice" value="Yellow" class="sr-only" aria-labelledby="color-choice-4-label">
                      <span id="color-choice-4-label" class="sr-only">Yellow</span>
                      <span aria-hidden="true" class="h-8 w-8 bg-amber-500 rounded-full border border-black border-opacity-10"></span>
                    </label>
                    <label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-none ring-red-500" x-on:click="colorChosen = 'red'" :class="colorChosen == 'red' ? 'ring-2' : ''">
                        <input type="radio" name="color-choice" value="pink" class="sr-only" aria-labelledby="color-choice-0-label">
                        <span id="color-choice-0-label" class="sr-only">Pink</span>
                        <span aria-hidden="true" class="h-8 w-8 bg-red-500 rounded-full border border-black border-opacity-10"></span>
                      </label>
                      <label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-none ring-pink-500" x-on:click="colorChosen = 'pink'" :class="colorChosen == 'pink' ? 'ring-2' : ''">
                        <input type="radio" name="color-choice" value="pink" class="sr-only" aria-labelledby="color-choice-0-label">
                        <span id="color-choice-0-label" class="sr-only">Pink</span>
                        <span aria-hidden="true" class="h-8 w-8 bg-pink-500 rounded-full border border-black border-opacity-10"></span>
                      </label>
                      <label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-none ring-purple-500" x-on:click="colorChosen = 'purple'" :class="colorChosen == 'purple' ? 'ring-2' : ''">
                        <input type="radio" name="color-choice" value="Purple" class="sr-only" aria-labelledby="color-choice-1-label">
                        <span id="color-choice-1-label" class="sr-only">Purple</span>
                        <span aria-hidden="true" class="h-8 w-8 bg-purple-500 rounded-full border border-black border-opacity-10"></span>
                      </label>
                      <label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-none ring-blue-500" x-on:click="colorChosen = 'blue'" :class="colorChosen == 'blue' ? 'ring-2' : ''">
                        <input type="radio" name="color-choice" value="Blue" class="sr-only" aria-labelledby="color-choice-2-label">
                        <span id="color-choice-2-label" class="sr-only">Blue</span>
                        <span aria-hidden="true" class="h-8 w-8 bg-blue-500 rounded-full border border-black border-opacity-10"></span>
                      </label>
                     
                  </div>
                </fieldset>
                <x-input-error for="color" class="mt-2" />
              </div>
            </div>
            <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
              <x-label for="name" value="{{ __('Date') }}" />
              <div class="sm:col-span-2">
                <x-input wire:model="date" type="date" name="date" id="project-name" class="block w-full" />
                <x-input-error for="date" class="mt-2" />
              </div>
            </div>
            <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
              <x-label for="creators" value="{{ __('Others opener') }}" />
              <div class="sm:col-span-2">
                <x-input wire:model="creators" type="text" name="creators" id="project-name" class="block w-full" />
                <x-input-error for="date" class="mt-2" />
              </div>
            </div>
            <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
              <x-label for="creators" value="{{ __('Tags') }}" />
              <div @click.outside="showListe = false" class="sm:col-span-2" x-data="{tags: $wire.tags_available, 
                  SelectedID: $wire.entangle('tags_id'), 
                  SelectedTags: [],
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
                        }
                    }">
                <div>
                  <div class="relative mt-2 h-40">
                    {{__('Choosen tags :')}}
                    <template x-for="tag in SelectedTags">
                      <span x-text="tag['name']" class=" mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                        <svg class="h-1.5 w-1.5 fill-gray-500" viewBox="0 0 6 6" aria-hidden="true">
                          <circle cx="3" cy="3" r="3"></circle>
                        </svg>
                      </span>
                    </template>
                    <input placeholder="Add a tag" x-model="term" @click="showListe = true" id="combobox" type="text" class="mt-2 w-full rounded-md border-0 bg-white py-1.5 pl-3 pr-12 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6" role="combobox" aria-controls="options" aria-expanded="false">
                    <ul x-show="showListe" class="absolute z-20 mt-1 max-h-20 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm" id="options" role="listbox">
                      <template x-for="tag in tags">
                        <li x-show="!(term.length > 0 && !tag['name'].includes(term))" :class="SelectedID.includes(tag['id']) ? 'font-semibold' : 'text-gray-900'" @click="toogle(tag['id'])" class="hover:bg-gray-100 relative cursor-default select-none py-2 pl-8 pr-4 text-gray-900" id="option-0" role="option" tabindex="-1">
                          <span class="block truncate" x-text="tag['name']"></span>
                          <span :class="SelectedID.includes(tag['id']) ? 'text-gray-600' : ' hidden'" class="absolute inset-y-0 left-0 flex items-center pl-1.5 text-gray-600">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                              <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                            </svg>
                          </span>
                        </li>
                      </template>
                      <!-- More items... -->
                    </ul>
                  </div>
                </div>
                <x-input-error for="tags" class="mt-2" />
              </div>
            </div>
            
              
              <!-- Tags pour le style de voie, combobox pour les ouvreurs, dessin sur schema selon le secteur-->
            
          </div>
        
      </div>
    </div>
    <div class="flex-shrink-0 border-t border-gray-200 px-4 py-5 sm:px-6">
      <div class="flex justify-end space-x-3">
        <x-secondary-button type="button">{{__('Cancel')}}</x-secondary-button> 
        <x-button wire:click="save">{{('Continue')}}</x-button> 
      </div>
    </div>
  </div>
</div>