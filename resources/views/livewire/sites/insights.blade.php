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
use App\Models\Tag;
use App\Models\Route;
use Spatie\Permission\Models\Permission;

new class extends Component {
    public $leastUsedTags;
    public $routesByLogsPerMonth;
    public $OldRoutes;
    public $averageLogsPerMonth;
    public $routesByRemovalScore;
    public Site $site;
    public $shown_routes;
    public $type;

    public function updated(){
        $this->edit_shown_routes($this->type);
    }
  # - Tags les moins utilisés, niveaux les moins représentés, voies les moins grimpées, 
    public function mount(Site $site){
        $this->site = $site;

        // Tags used by the fewest routes of this site
        $this->leastUsedTags = Tag::withCount(['routes' => function($query) use ($site) {
                $query->whereHas('line.sector.area.site', function($q) use ($site) {
                    $q->where('id', $site->id);
                });
            }])
            ->orderBy('routes_count', 'asc')
            ->take(3)
            ->get();

            


        
        $routes = Route::whereHas('line.sector.area.site', function($query){
            $query->where('id', $this->site->id);
            })
            ->withCount('logs')
            ->get();


        $routes_logs = (clone $routes)->map(function($route) {
                $months = max(1, now()->diffInMonths($route->created_at));
                $route->logs_per_month = round($route->logs_count / $months, 2);
                return $route;
            });

        $this->averageLogsPerMonth = round($routes_logs->avg('logs_per_month'), 2);
        $this->routesByLogsPerMonth = $routes_logs->sortBy('logs_per_month')->values()->take(10);

        $this->OldRoutes = Route::whereHas('line.sector.area.site', function($query){
                $query->where('id', $this->site->id);
            })
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get();
    
        $routes = $routes->map(function($route) {
            $months = max(1, now()->diffInMonths($route->created_at));
            $route->logs_per_month = round($route->logs_count / $months, 2);

            // Calculate time since last log (in months)
            $lastLog = $route->logs()->latest('created_at')->first();
            $monthsSinceLastLog = $lastLog ? max(1, now()->diffInMonths($lastLog->created_at)) : $months;

            // Score: higher means more likely to be removed

            $score = 0;
            $score += $months * 1.25; // older routes: +1.5 per month
            $score += $monthsSinceLastLog * 2; // not climbed recently: +2 per month
            $score += (1/( max(0.1, $route->logs_per_month))*10);
            $score += (1/( max(1, $route->grade))*5);
            $score += ($route->logs_per_month < 0.5 ? 10 : 0); // bonus if rarely climbed
            $score += ($route->logs_count < 5 ? 10 : 0); // bonus if very few total logs

            $route->removal_score = round($score, 2);
            return $route;
            });

        $this->routesByRemovalScore = $routes->sortByDesc('removal_score')->values()->take(10);
        $this->shown_routes = $this->routesByRemovalScore;
    
        }
        public function edit_shown_routes($type){
            if($type = 'removal'){
                $this->shown_routes = $this->routesByRemovalScore;
            }elseif($type == 'logs'){
                $this->shown_routes = $this->averageLogsPerMonth;
            }else{
                $this->shown_routes = $this->OldRoutes;
            }
        }


}; ?>

<div class='gap-2' x-data='{type: "removal"}'>
        <div class="bg-white overflow-hidden  sm:rounded-lg col-span-6 flex justify-between items-center mb-2">
            <h2 class="px-4 py-4 text-xl font-semibold text-gray-900">
                {{ __('Routes Insights') }}
            </h2>
            <div class='flex items-center gap-2 ml-2'> {{ __('Order by') }}
            <select wire:model.live='type' class='h-10 block  rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6'>
                <option value="removal">{{ __('Removeal Score') }}</option>
                <option value="old">{{ __('Old') }}</option>
                <option value="logs">{{ __('Logs by mounths') }}</option>
            </select>
            </div>
        </div>

        <div class="bg-white inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8 mb-4">
          <table class="border-separate border-spacing-y-3 min-w-full divide-y divide-gray-300 table-fixed">
            <thead>
              <tr>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"></th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"></th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"></th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"></th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"></th>
                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-3">
                  <span class="sr-only">Edit</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white"> @foreach ($this->shown_routes as $route) <tr class="hover:bg-gray-50">
                
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
                <td class="items-center relative whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3 flex">
                 @if($route->removing_at !=  null)
                  <x-icons.icon-schedule/>
                  {{ $route->removing_at }}
                  @else
                  <x-icons.icon-infinity/>
                  @endif
                </td>
                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-3">
                  <div class='flex items-center justify-end' >
                
                  </div>
                </td>
              </tr> @endforeach </tbody>
          </table>
        </div>


</div>