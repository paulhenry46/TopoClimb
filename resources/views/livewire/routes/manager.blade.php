<?php

use Livewire\Volt\Component;
use App\Models\Site;
use App\Models\Route;
use App\Models\Area;
use App\Models\Line;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
new class extends Component {
  use WithPagination;

    public Site $site;
    public Area $area;
    public Route $route;
    public $lines;

    public $modal_open;
    public $modal_title;
    public $modal_subtitle;
    public $modal_submit_message;

    #[Validate('required')]
    public $name;
    #[Validate('string')]
    public $comment;
    #[Validate('required')]
    public $line;
    #[Validate('required')]
    public string $grade;
    #[Validate('required')]
    public string $color;

    #[Validate('required')]
    public $date;

    public $creators;

    public $slug;
    public $id_editing;
    

    public function saveRoute()
    {
      $this->validate(); 
      $this->route->slug = Str::slug($this->name, '-');
      $this->route->name = $this->name;
      $this->route->comment = $this->comment;
      $this->route->line_id = $this->line;
      $this->route->grade = $this->grade;
      $this->route->color = $this->color;
      $this->route->created_at = $this->date;


          $this->route->save();
          $this->dispatch('action_ok', title: 'Route saved', message: 'Your modifications has been registered !');
        
        $this->modal_open = false;
        $this->render();
    }

    #[Computed]
    public function routes()
    {
        return Route::whereIn('line_id', $this->lines->pluck('id'))->paginate(10);
    }

    public function open_item($id){
      $item = Route::find($id);
      $this->route = $item;
      $this->name = $item->name;
      $this->comment = $item->comment;
      $this->line = $item->line_id;
      $this->grade = $item->grade;
      $this->color = $item->color;
      $this->date = $item->created_at->format('Y-m-d');

      $this->modal_open = true;
    }

    public function delete_item($id){
      $item = Route::find($id);
      $item->delete();
      $this->dispatch('action_ok', title: 'Route deleted', message: 'Your modifications has been registered !');
      $this->render();
    }

    public function mount($lines, Site $site, Area $area){
      $this->modal_title = __('Editing route ').$this->name;
      $this->modal_subtitle = __('Check the informations about this route.');
      $this->modal_submit_message = __('Edit');
      
      $this->lines  = $lines;
      $this->line = null;
      $this->site = $site;
      $this->area = $area;
    }
}; ?>

<div>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
        <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Routes')}}</h1>
        <p class="mt-2 text-sm text-gray-700">{{__('Registered routes')}}</p>
      </div>
      <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
       <a href="{{route('admin.routes.new', ['site' => $this->site->id, 'area' => $this->area->id])}}" wire:navigate> <x-button type="button">{{__('Add route')}}</x-button></a>
      </div>
    </div>
    <div class=" flow-root">
      <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
          <table class="border-separate border-spacing-y-3 min-w-full divide-y divide-gray-300 table-fixed">
            <thead>
              <tr>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"></th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"></th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"></th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"></th>
                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-3">
                  <span class="sr-only">Edit</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white"> @foreach ($this->routes as $route) <tr class="">
                
                <td class="rounded-l-md text-xl text-center w-4 bg-{{$route->color}}-500 relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                 
                  {{$route->grade}}
                </td>
                <td class="whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                  <div class="flex items-center">
                    <div>
                      <div class="font-bold pb-1">{{$route->name}}</div>
                      <div class="text-sm opacity-50">{{__('Line')}} {{$route->line->id}}</div>
                    </div>
                  </div>
                </td>
                <td class=" relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                 
                  Paulhenry
                </td>
                <td class=" relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                  <span class=" mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">prises de merde</span>
                  <span class=" mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">devers</span>
                  <span class=" mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">archées</span>
                </td>
                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-3">
                  <button wire:click="open_item({{$route->id}})" class="text-gray-600 hover:text-gray-900 mr-2">
                    <x-icon-edit />
                  </button>
                  <a wire:navigate href="{{Route('admin.routes.path', ['site' => $this->site->id, 'area' => $this->area->id, 'route' => $route->id])}}" class="mr-2 text-gray-600 hover:text-gray-900" >
                    <button>
                    <x-icon-path />
                    </button>
                  </a>
                  <a wire:navigate href="{{Route('admin.routes.photo', ['site' => $this->site->id, 'area' => $this->area->id, 'route' => $route->id])}}" class="text-gray-600 hover:text-gray-900" >
                    <button>
                    <x-icon-picture />
                    </button>
                  </a>
                </td>
              </tr> @endforeach </tbody>
          </table>
          {{ $this->routes->links() }}
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
              <form wire:submit="saveRoute" class="flex h-full flex-col bg-white shadow-xl">
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
                      <x-label for="name" value="{{ __('Name') }}" />
                      <div class="sm:col-span-2">
                        <x-input wire:model="name" type="text" name="name" id="project-name" class="block w-full" />
                        <x-input-error for="name" class="mt-2" />
                      </div>
                    </div>
                    <!-- Project description -->
                    <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                      <x-label for="comment" value="{{ __('Comments') }}" />
                      <div class="sm:col-span-2">
                        <textarea wire:model="comment" id="comment" name="comment" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6"></textarea>
                        <x-input-error for="comment" class="mt-2" />
                      </div>
                    </div>
                    <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                      <x-label for="line" value="{{ __('Line') }}" />
                      <div class="sm:col-span-2">
                        <select wire:model.live="line" id="line" name="line" class="block w-full rounded-md border-0 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6"> @foreach ($this->lines as $line) <option value="{{$line->id}}">{{__('Line ')}}{{$line->local_id}}</option> @endforeach </select>
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
                          </div>
                          <div class="mt-4 flex items-center space-x-3">
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
                            <label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-none ring-green-500" x-on:click="colorChosen = 'green'" :class="colorChosen == 'green' ? 'ring-2' : ''">
                              <input type="radio" name="color-choice" value="Green" class="sr-only" aria-labelledby="color-choice-3-label">
                              <span id="color-choice-3-label" class="sr-only">Green</span>
                              <span aria-hidden="true" class="h-8 w-8 bg-green-500 rounded-full border border-black border-opacity-10"></span>
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
                      <div @click.outside="showListe = false" class="sm:col-span-2" x-data="{tags: [{ id: 1, tag: 'archées' },
                          { id: 2, tag: 'force' }, { id: 3, tag: 'devers' }], 
                          SelectedID: [], 
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
                                }
                            }">
                        <div>
                          <div class="relative mt-2 h-40">
                            <input x-model="term" @click="showListe = true" id="combobox" type="text" class="w-full rounded-md border-0 bg-white py-1.5 pl-3 pr-12 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6" role="combobox" aria-controls="options" aria-expanded="false">
                            <ul x-show="showListe" class="absolute z-20 mt-1 max-h-40 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm" id="options" role="listbox">
                              <template x-for="tag in tags">
                                <li x-show="!(term.length > 0 && !tag['tag'].includes(term))" :class="SelectedID.includes(tag['id']) ? 'font-semibold' : 'text-gray-900'" @click="toogle(tag['id'])" class="hover:bg-gray-100 relative cursor-default select-none py-2 pl-8 pr-4 text-gray-900" id="option-0" role="option" tabindex="-1">
                                  <!-- Selected: "font-semibold" -->
                                  <span class="block truncate" x-text="tag['tag']"></span>
                                  <span :class="SelectedID.includes(tag['id']) ? 'text-gray-600' : ' hidden'" class="absolute inset-y-0 left-0 flex items-center pl-1.5 text-gray-600">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                      <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                    </svg>
                                  </span>
                                </li>
                              </template>
                              <!-- More items... -->
                            </ul>
                            <div class="mt-2">
                              {{__('Choosen tags :')}}
                              <template x-for="tag in SelectedTags">
                                <span x-text="tag['tag']" class=" mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                  <svg class="h-1.5 w-1.5 fill-gray-500" viewBox="0 0 6 6" aria-hidden="true">
                                    <circle cx="3" cy="3" r="3"></circle>
                                  </svg>
                                </span>
                              </template>
                            </div>
                          </div>
                        </div>
                        <x-input-error for="tags" class="mt-2" />
                      </div>
                    </div>
                      <!-- Tags pour le style de voie, combobox pour les ouvreurs, dessin sur schema selon le secteur-->
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