<?php

use Livewire\Volt\Component;
use App\Models\Site;
use App\Models\Route;
use App\Models\Area;
use App\Models\Line;
use App\Models\Tag;
use App\Models\Sector;
use App\Models\User;
use Livewire\Attributes\Validate; 
use Livewire\Attributes\Locked; 
use Illuminate\Support\Str;
use Livewire\WithPagination;
use App\Jobs\DeleteRoute;
use Livewire\Attributes\Computed;
new class extends Component {
  use WithPagination;

    public Site $site;
    public Area $area;
    public Route $route;
    #[Locked]
    public $all_routes;
    public $lines;
    public $lines_available;

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
    #[Validate('required|regex:/[3-9][abc][+]?/')]
    public string $grade;
    #[Validate('required')]
    public string $color;
    #[Validate('required')]
    public $date;

    public $sector;
    public $sectors;

    public $opener_search;
    public $opener_selected  = [];
    public $error_user;

    public $slug;

    public array $tags_id;

    public array $tags_choosen;

    public $tags_available;

    public $id_editing;
    
    public function updated($property, $value)
    {
        if ($property === 'sector') {
            $this->line = Line::where('sector_id', $this->sector)->first()->id;
            $this->lines_available = Line::where('sector_id', $this->sector)->get();
        }
    }

    public function saveRoute()
    {
      if($this->all_routes or $this->route->users()->where('user_id', auth()->id())->exists()){
        $this->validate(); 
      $this->route->slug = Str::slug($this->name, '-');
      $this->route->name = $this->name;
      $this->route->comment = $this->comment;
      $this->route->line_id = $this->line;
      $this->route->grade = $this->gradeToInt($this->grade);
      $this->route->color = $this->color;
      $this->route->created_at = $this->date;
      $this->route->save();
      $this->route->tags()->sync($this->tags_id);
      $temp_openers_id = [];

      foreach ($this->opener_selected as $key => $opener) {
        array_push($temp_openers_id, $opener['id']);
      }
      $this->route->users()->sync($temp_openers_id);

      $this->dispatch('action_ok', title: 'Route saved', message: 'Your modifications has been registered !');
        
        $this->modal_open = false;
        $this->render();
      }
      
    }

    #[Computed]
    public function routes()
    {
      if($this->all_routes){
        return Route::whereIn('line_id', $this->lines->pluck('id'))->paginate(10);
      }else{
        return auth()->user()->routes()->whereIn('line_id', $this->lines->pluck('id'))->paginate(10);
      }
    }

    public function open_item($id){
      $item = Route::find($id);
      $this->route = $item;
      $this->name = $item->name;
      $this->comment = $item->comment;
      $this->line = $item->line_id;
      $this->sector = $item->line->sector_id;
      $this->lines_available = Line::where('sector_id', $this->sector)->get();
      $this->grade = $this->IntToGrade($item->grade);
      $this->color = $item->color;
      $this->date = $item->created_at->format('Y-m-d');

      $this->modal_open = true;

      $tags_temp = $this->route->tags()->pluck('tags.id', 'name')->toArray();
    

      $tags = [];
      $tags_id = [];
     foreach($tags_temp as $name => $key){
      array_push($tags, ['name' => $name, 'id' => $key]);
      array_push($tags_id, $key);
     }

     $this->tags_id = $tags_id;
     $this->tags_choosen = $tags;
     //dd($this->tags_id);
     $this->opener_selected = [];
     $users_temp = $this->route->users;
     foreach($users_temp as $user){
      array_push($this->opener_selected, ['name' => $user->name, 'id' => $user->id, 'url' => $user->profile_photo_url]);
      
    }
  }

    public function remove_item($id){
      $item = Route::find($id);
      DeteteRoute::dispatchSync($item);
      $this->dispatch('action_ok', title: 'Route deleted', message: 'Your modifications has been registered !');
      $this->render();
    }

    public function mount($lines, Site $site, Area $area){

      if(auth()->user()->can('lines-sectors.'.$area->site->id)){
        $this->all_routes == true;
      }else{
        $this->all_routes == false;
      }

      $this->modal_title = __('Editing route ').$this->name;
      $this->modal_subtitle = __('Check the informations about this route.');
      $this->modal_submit_message = __('Edit');
      
      $this->lines = $lines;
      $this->lines_available = $lines;
      $this->line = null;
      $this->site = $site;
      $this->area = $area;

      $tags_temp = Tag::all()->pluck('id', 'name')->toArray();
    

      $tags = [];
     foreach($tags_temp as $name => $key){
      array_push($tags, ['name' => $name, 'id' => $key]);
     }
      $this->sectors = Sector::where('area_id', $this->area->id)->get();
      $this->sector = Sector::where('area_id', $this->area->id)->first()->id;
     

     $this->tags_available = $tags;
    }

    public function add_opener(){
      $user = User::where('name', $this->opener_search)->first();
      if($user != null){
        array_push($this->opener_selected, ['name' => $user->name, 'id' => $user->id, 'url' => $user->profile_photo_url]);
        $this->opener_search = null;
        $this->error_user = false;
      }else{
        $this->error_user = true;
      }

    }

    public function remove_opener($id)
    {
      foreach ($this->opener_selected as $subKey => $opener){
        if($opener['id'] == $id){
          unset($this->opener_selected[$subKey]);
        }
      }
    }

    protected function gradeToInt($grade){
        $array = [
        '3a' => 300, '3a+' => 310, '3b' => 320, '3b+' => 330, '3c' => 340, '3c+' => 350, 
        '4a' => 400, '4a+' => 410, '4b' => 420, '4b+' => 430, '4c' => 440, '4c+' => 450, 
        '5a' => 500, '5a+' => 510, '5b' => 520, '5b+' => 530, '5c' => 540, '5c+' => 550, 
        '6a' => 600, '6a+' => 610, '6b' => 620, '6b+' => 630, '6c' => 640, '6c+' => 650, 
        '7a' => 700, '7a+' => 710, '7b' => 720, '7b+' => 730, '7c' => 740, '7c+' => 750, 
        '8a' => 800, '8a+' => 810, '8b' => 820, '8b+' => 830, '8c' => 840, '8c+' => 850, 
        '9a' => 900, '9a+' => 910, '9b' => 920, '9b+' => 930, '9c' => 940, '9c+' => 950,];
        return $array[$grade];
       /* if (preg_match('/^([3-9][abc])(\+?)$/', $grade, $matches)) {
            $base = (int)$matches[1][0] * 100 + (ord($matches[1][1]) - ord('a')) * 20;
            $modifier = $matches[2] === '+' ? 10 : 0;
            return $base + $modifier;
        }*/
    }

    protected function intToGrade($int){
        $grades = [
            300 => '3a', 310 => '3a+', 320 => '3b', 330 => '3b+', 340 => '3c', 350 => '3c+',
            400 => '4a', 410 => '4a+', 420 => '4b', 430 => '4b+', 440 => '4c', 450 => '4c+',
            500 => '5a', 510 => '5a+', 520 => '5b', 530 => '5b+', 540 => '5c', 550 => '5c+',
            600 => '6a', 610 => '6a+', 620 => '6b', 630 => '6b+', 640 => '6c', 650 => '6c+',
            700 => '7a', 710 => '7a+', 720 => '7b', 730 => '7b+', 740 => '7c', 750 => '7c+',
            800 => '8a', 810 => '8a+', 820 => '8b', 830 => '8b+', 840 => '8c', 850 => '8c+',
            900 => '9a', 910 => '9a+', 920 => '9b', 930 => '9b+', 940 => '9c', 950 => '9c+',
        ];

        return $grades[$int] ?? null;
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
            <tbody class="bg-white"> @foreach ($this->routes as $route) <tr class="hover:bg-gray-50">
                
                <td class="rounded-l-md text-xl text-center w-4 bg-{{$route->color}}-300 relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                 
                  {{$route->gradeFormated()}}
                </td>
                <td class="  whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                  <div class="flex items-center">
                    <div>
                      <div class="font-bold pb-1">{{$route->name}}</div>
                      @if($route->line->local_id == 0)
                      <div class="text-sm opacity-50">{{__('Sector')}} {{$route->line->sector->local_id}}</div>
                      @else
                      <div class="text-sm opacity-50">{{__('Line')}} {{$route->line->local_id}}</div>
                      @endif
                    </div>
                  </div>
                </td>
                <td class=" relative whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                 
                  @forelse ( $route->users as $opener)
                            <span class=" flex-none mr-2 inline-flex items-center gap-x-1.5 rounded-md  px-2 text-sm font-medium text-gray-700">
                              <img
                                alt="{{ $opener->name }}"
                                src="{{ $opener->profile_photo_url }}"
                                class=" h-8 w-8  rounded-md object-cover object-center"
                              />
                              {{ $opener->name }}
                            </span>
                            @empty
                        @endforelse
                </td>
                <td class=" relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                  @forelse ($route->tags as $tag)
                  
                  <span class=" mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">{{$tag->name}}</span>
                  @empty
                  @endforelse
                </td>
                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-3">
                  <button wire:click="open_item({{$route->id}})" class="cursor-pointer text-gray-600 hover:text-gray-900 mr-2">
                    <x-icon-edit />
                  </button>
                  <button wire:click="remove_item({{$route->id}})" class="cursor-pointer text-gray-600 hover:text-gray-900 mr-2" wire:confirm="{{ __('Are you sure you want to delete this project?') }}">
                    <x-icon-delete />
                  </button>
                  <a wire:navigate href="{{Route('admin.routes.path', ['site' => $this->site->id, 'area' => $this->area->id, 'route' => $route->id])}}" class="cursor-pointer mr-2 text-gray-600 hover:text-gray-900" >
                    <button>
                    <x-icon-path />
                    </button>
                  </a>
                  <a wire:navigate href="{{Route('admin.routes.photo', ['site' => $this->site->id, 'area' => $this->area->id, 'route' => $route->id])}}" class="cursor-pointer text-gray-600 hover:text-gray-900" >
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
              <form x-on:keydown.prevent.enter="" wire:submit="saveRoute" class="flex h-full flex-col bg-white shadow-xl">
                <div class="flex-1 overflow-y-auto">
                  <!-- Header -->
                  <div class="bg-gray-50 px-4 py-6 sm:px-6">
                    <div class="flex items-start justify-between space-x-3">
                      <div class="space-y-1">
                        <h2 class="text-base font-semibold leading-6 text-gray-900" id="slide-over-title">{{$this->modal_title}}</h2>
                        <p class="text-sm text-gray-500">{{$this->modal_subtitle}}</p>
                      </div>
                      <div class="flex h-7 items-center">
                        <button x-on:click="open = ! open" type="button" class="cursor-pointer relative text-gray-400 hover:text-gray-500">
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
                        <textarea wire:model="comment" id="comment" name="comment" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6"></textarea>
                        <x-input-error for="comment" class="mt-2" />
                      </div>
                    </div>
                    @if($this->sectors->count() >1)
                    <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                      <x-label for="line" value="{{ __('Sector') }}" />
                      <div class="sm:col-span-2">
                        <select wire:model.live="sector" id="sector" name="sector" class="block w-full rounded-md border-0 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6"> @foreach ($this->sectors as $line) <option value="{{$line->id}}">{{__('Sector ')}}{{$line->local_id}}</option> @endforeach </select>
                        <x-input-error for="address" class="mt-2" />
                      </div>
                    </div>
                    @endif
                    @if($this->lines_available->count() > 1)
                    <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                      <x-label for="line" value="{{ __('Line') }}" />
                      <div class="sm:col-span-2">
                        <select wire:model.live="line" id="line" name="line" class="block w-full rounded-md border-0 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6"> @foreach ($this->lines_available as $line) <option value="{{$line->id}}">{{__('Line ')}}{{$line->local_id}}</option> @endforeach </select>
                        <x-input-error for="address" class="mt-2" />
                      </div>
                    </div>
                    @endif
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
                          <div class="mt-4 flex items-center gap-2">
<label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-hidden ring-red-300" x-on:click="colorChosen = 'red'" :class="colorChosen == 'red' ? 'ring-2' : ''"> <input type="radio" name="color-choice" value=" red " class="sr-only" aria-labelledby="color-choice-4-label"> <span id="color-choice-4-label" class="sr-only bg-red-50"> red </span> <span aria-hidden="true" class="h-8 w-8 bg-red-300 rounded-full border border-black/10"></span> </label>
<label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-hidden ring-orange-300" x-on:click="colorChosen ='orange'" :class="colorChosen ==' orange' ? 'ring-2' : ''"> <input type="radio" name="color-choice" value=" orange " class="sr-only" aria-labelledby="color-choice-4-label"> <span id="color-choice-4-label" class="sr-only bg-orange-50"> orange </span> <span aria-hidden="true" class="h-8 w-8 bg-orange-300 rounded-full border border-black/10"></span> </label>
<label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-hidden ring-amber-300" x-on:click="colorChosen = 'amber'" :class="colorChosen == ' amber' ? 'ring-2' : ''"> <input type="radio" name="color-choice" value=" amber " class="sr-only" aria-labelledby="color-choice-4-label"> <span id="color-choice-4-label" class="sr-only bg-amber-50"> amber </span> <span aria-hidden="true" class="h-8 w-8 bg-amber-300 rounded-full border border-black/10"></span> </label>
<label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-hidden ring-yellow-300" x-on:click="colorChosen = 'yellow'" :class="colorChosen == 'yellow' ? 'ring-2': ''"> <input type="radio" name="color-choice" value=" yellow " class="sr-only" aria-labelledby="color-choice-4-label"> <span id="color-choice-4-label" class="sr-only bg-yellow-50"> yellow </span> <span aria-hidden="true" class="h-8 w-8 bg-yellow-300 rounded-full border border-black/10"></span> </label>
<label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-hidden ring-lime-300" x-on:click="colorChosen = 'lime'" :class="colorChosen == 'lime' ? 'ring-2': ''"> <input type="radio" name="color-choice" value=" lime " class="sr-only" aria-labelledby="color-choice-4-label"> <span id="color-choice-4-label" class="sr-only bg-lime-50"> lime </span> <span aria-hidden="true" class="h-8 w-8 bg-lime-300 rounded-full border border-black/10"></span> </label>
<label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-hidden ring-green-300" x-on:click="colorChosen = 'green'" :class="colorChosen == 'green' ? 'ring-2': ''"> <input type="radio" name="color-choice" value=" green " class="sr-only" aria-labelledby="color-choice-4-label"> <span id="color-choice-4-label" class="sr-only bg-green-50"> green </span> <span aria-hidden="true" class="h-8 w-8 bg-green-300 rounded-full border border-black/10"></span> </label>
<label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-hidden ring-emerald-300" x-on:click="colorChosen = 'emerald '" :class="colorChosen == 'emerald '? 'ring-2': ''"> <input type="radio" name="color-choice" value=" emerald " class="sr-only" aria-labelledby="color-choice-4-label"> <span id="color-choice-4-label" class="sr-only bg-emerald-50"> emerald </span> <span aria-hidden="true" class="h-8 w-8 bg-emerald-300 rounded-full border border-black/10"></span> </label>
<label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-hidden ring-teal-300" x-on:click="colorChosen = 'teal'" :class="colorChosen == 'teal' ? 'ring-2': ''"> <input type="radio" name="color-choice" value=" teal " class="sr-only" aria-labelledby="color-choice-4-label"> <span id="color-choice-4-label" class="sr-only bg-teal-50"> teal </span> <span aria-hidden="true" class="h-8 w-8 bg-teal-300 rounded-full border border-black/10"></span> </label>
<label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-hidden ring-cyan-300" x-on:click="colorChosen = 'cyan'" :class="colorChosen == 'cyan' ? 'ring-2': ''"> <input type="radio" name="color-choice" value=" cyan " class="sr-only" aria-labelledby="color-choice-4-label"> <span id="color-choice-4-label" class="sr-only bg-cyan-50"> cyan </span> <span aria-hidden="true" class="h-8 w-8 bg-cyan-300 rounded-full border border-black/10"></span> </label>
<label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-hidden ring-sky-300" x-on:click="colorChosen = 'sky'" :class="colorChosen == 'sky' ? 'ring-2': ''"> <input type="radio" name="color-choice" value=" sky " class="sr-only" aria-labelledby="color-choice-4-label"> <span id="color-choice-4-label" class="sr-only bg-sky-50"> sky </span> <span aria-hidden="true" class="h-8 w-8 bg-sky-300 rounded-full border border-black/10"></span> </label>

                            </div>
                          <div class="mt-4 flex items-center gap-2">
<label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-hidden ring-blue-300" x-on:click="colorChosen = 'blue'" :class="colorChosen == 'blue' ? 'ring-2': ''"> <input type="radio" name="color-choice" value=" blue " class="sr-only" aria-labelledby="color-choice-4-label"> <span id="color-choice-4-label" class="sr-only bg-blue-50"> blue </span> <span aria-hidden="true" class="h-8 w-8 bg-blue-300 rounded-full border border-black/10"></span> </label>
<label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-hidden ring-indigo-300" x-on:click="colorChosen = 'indigo'" :class="colorChosen == 'indigo' ? 'ring-2': ''"> <input type="radio" name="color-choice" value=" indigo " class="sr-only" aria-labelledby="color-choice-4-label"> <span id="color-choice-4-label" class="sr-only bg-indigo-50"> indigo </span> <span aria-hidden="true" class="h-8 w-8 bg-indigo-300 rounded-full border border-black/10"></span> </label>
<label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-hidden ring-violet-300" x-on:click="colorChosen = 'violet'" :class="colorChosen == 'violet' ? 'ring-2': ''"> <input type="radio" name="color-choice" value=" violet " class="sr-only" aria-labelledby="color-choice-4-label"> <span id="color-choice-4-label" class="sr-only bg-violet-50"> violet </span> <span aria-hidden="true" class="h-8 w-8 bg-violet-300 rounded-full border border-black/10"></span> </label>
<label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-hidden ring-purple-300" x-on:click="colorChosen = 'purple'" :class="colorChosen == 'purple' ? 'ring-2': ''"> <input type="radio" name="color-choice" value=" purple " class="sr-only" aria-labelledby="color-choice-4-label"> <span id="color-choice-4-label" class="sr-only bg-purple-50"> purple </span> <span aria-hidden="true" class="h-8 w-8 bg-purple-300 rounded-full border border-black/10"></span> </label>
<label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-hidden ring-fuchsia-300" x-on:click="colorChosen = 'fuchsia'" :class="colorChosen == 'fuchsia' ? 'ring-2': ''"> <input type="radio" name="color-choice" value=" fuchsia " class="sr-only" aria-labelledby="color-choice-4-label"> <span id="color-choice-4-label" class="sr-only bg-fuchsia-50"> fuchsia </span> <span aria-hidden="true" class="h-8 w-8 bg-fuchsia-300 rounded-full border border-black/10"></span> </label>
<label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-hidden ring-pink-300" x-on:click="colorChosen = 'pink'" :class="colorChosen == 'pink' ? 'ring-2': ''"> <input type="radio" name="color-choice" value=" pink " class="sr-only" aria-labelledby="color-choice-4-label"> <span id="color-choice-4-label" class="sr-only bg-pink-50"> pink </span> <span aria-hidden="true" class="h-8 w-8 bg-pink-300 rounded-full border border-black/10"></span> </label>
<label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-hidden ring-rose-300" x-on:click="colorChosen = 'rose'" :class="colorChosen == 'rose' ? 'ring-2': ''"> <input type="radio" name="color-choice" value=" rose " class="sr-only" aria-labelledby="color-choice-4-label"> <span id="color-choice-4-label" class="sr-only bg-rose-50"> rose </span> <span aria-hidden="true" class="h-8 w-8 bg-rose-300 rounded-full border border-black/10"></span> </label>
<label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-hidden ring-slate-300" x-on:click="colorChosen = 'slate'" :class="colorChosen == 'slate' ? 'ring-2': ''"> <input type="radio" name="color-choice" value=" slate " class="sr-only" aria-labelledby="color-choice-4-label"> <span id="color-choice-4-label" class="sr-only bg-slate-50"> slate </span> <span aria-hidden="true" class="h-8 w-8 bg-slate-300 rounded-full border border-black/10"></span> </label>
<label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-hidden ring-gray-300" x-on:click="colorChosen = 'gray'" :class="colorChosen == 'gray' ? 'ring-2': ''"> <input type="radio" name="color-choice" value=" gray " class="sr-only" aria-labelledby="color-choice-4-label"> <span id="color-choice-4-label" class="sr-only bg-gray-50"> gray </span> <span aria-hidden="true" class="h-8 w-8 bg-gray-300 rounded-full border border-black/10"></span> </label>
<label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-hidden ring-zinc-300" x-on:click="colorChosen = 'zinc'" :class="colorChosen == 'zinc' ? 'ring-2': ''"> <input type="radio" name="color-choice" value=" zinc " class="sr-only" aria-labelledby="color-choice-4-label"> <span id="color-choice-4-label" class="sr-only bg-zinc-50"> zinc </span> <span aria-hidden="true" class="h-8 w-8 bg-zinc-300 rounded-full border border-black/10"></span> </label>

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
                      <x-label for="creators" value="{{ __('Tags') }}" />
                      <div @click.outside="showListe = false" class="sm:col-span-2" x-data="{tags: $wire.tags_available, 
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
                            <input placeholder="Add a tag" x-model="term" @click="showListe = true" id="combobox" type="text" class="mt-2 w-full rounded-md border-0 bg-white py-1.5 pl-3 pr-12 text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6" role="combobox" aria-controls="options" aria-expanded="false">
                            <ul x-show="showListe" class="absolute z-20 mt-1 max-h-20 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-hidden sm:text-sm" id="options" role="listbox">
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
                              <!-- More items... -->
                            </ul>
                          </div>
                        </div>
                        <x-input-error for="tags" class="mt-2" />
                      </div>
                    </div>
                    <div class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                      <x-label for="opener_search" value="{{ __('Openers') }}" />
                      <div class=" sm:col-span-2">
                        <div class=" flex rounded-md">
                          <div class="flex-auto relative flex grow items-stretch focus-within:z-10">
                            @forelse ( $this->opener_selected as $opener)
                            <span wire:click="remove_opener({{$opener['id']}})" class="group cursor-pointer flex-none mr-2 inline-flex items-center gap-x-1.5 rounded-md ring-1 ring-gray-300 px-2 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200">
                              <img
                                alt="{{ $opener['name'] }}"
                                src="{{ $opener['url'] }}"
                                class="group-hover:hidden h-6 w-6  rounded-md object-cover object-center"
                              />
                              <svg class="h-6 w-6 hidden group-hover:block fill-gray-800 stroke-gray-800" fill="currentColor" xmlns="http://www.w3.org/2000/svg"  viewBox="0 -960 960 960" width="24px" fill="#e3e3e3">
                                <path d="m336-280 144-144 144 144 56-56-144-144 144-144-56-56-144 144-144-144-56 56 144 144-144 144 56 56ZM480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/>
                              </svg>
                              {{ $opener['name'] }}
                            </span>
                            @empty
                        @endforelse
                            <input @keyup.enter.prevent="$wire.add_opener()" wire:model="opener_search" type="text" name="opener_search" id="opener_search" class="block w-full rounded-none rounded-l-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6" placeholder="John Smith">
                          </div>
                          <button type="button" @click="$wire.add_opener()" class="cursor-pointer flex-none relative -ml-px inline-flex items-center gap-x-1.5 rounded-r-md px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            
                            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-0.5 h-5 w-5 text-gray-400" viewBox="0 -960 960 960" fill="currentColor">
                              <path d="M720-520h-80q-17 0-28.5-11.5T600-560q0-17 11.5-28.5T640-600h80v-80q0-17 11.5-28.5T760-720q17 0 28.5 11.5T800-680v80h80q17 0 28.5 11.5T920-560q0 17-11.5 28.5T880-520h-80v80q0 17-11.5 28.5T760-400q-17 0-28.5-11.5T720-440v-80Zm-360 40q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM40-240v-32q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q66 0 130 15.5T616-378q29 15 46.5 43.5T680-272v32q0 33-23.5 56.5T600-160H120q-33 0-56.5-23.5T40-240Zm80 0h480v-32q0-11-5.5-20T580-306q-54-27-109-40.5T360-360q-56 0-111 13.5T140-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T440-640q0-33-23.5-56.5T360-720q-33 0-56.5 23.5T280-640q0 33 23.5 56.5T360-560Zm0-80Zm0 400Z"/>
                            </svg>
                            {{__('Add')}}
                          </button>
                          
                        </div>
                        @if($this->error_user)
                            <p class="text-sm text-red-600 mt-2">{{__('No users with this name were found.')}}</p>
                        @endif
                      </div>
                    </div>
                      <!-- Tags pour le style de voie, combobox pour les ouvreurs, dessin sur schema selon le secteur-->
                  </div>
                  <div class="shrink-0 border-t border-gray-200 px-4 py-5 sm:px-6">
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
</div>