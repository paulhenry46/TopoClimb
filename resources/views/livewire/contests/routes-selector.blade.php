<?php

use Livewire\Volt\Component;
use App\Models\Contest;
use App\Models\Route;
use App\Models\Area;
use Livewire\Attributes\Computed;

new class extends Component {
    public Contest $contest;
    public $selectedRoutes = [];
    public $routePoints = [];

    public function mount()
    {
        $this->selectedRoutes = $this->contest->routes->pluck('id')->toArray();
        
        // Load existing points for selected routes
        foreach ($this->contest->routes as $route) {
            $this->routePoints[$route->id] = $route->pivot->points;
        }
    }

    public function toggleRoute($routeId)
    {
        if (in_array($routeId, $this->selectedRoutes)) {
            $this->selectedRoutes = array_diff($this->selectedRoutes, [$routeId]);
            $this->contest->routes()->detach($routeId);
            unset($this->routePoints[$routeId]);
        } else {
            $this->selectedRoutes[] = $routeId;
            $this->contest->routes()->attach($routeId, ['points' => 100]);
            $this->routePoints[$routeId] = 100;
        }
        $this->dispatch('action_ok', title: 'Routes updated', message: 'Contest routes have been updated successfully!');
    }

    public function updatePoints($routeId, $points)
    {
        $points = max(1, (int)$points); // Ensure positive integer
        $this->routePoints[$routeId] = $points;
        $this->contest->routes()->updateExistingPivot($routeId, ['points' => $points]);
        $this->dispatch('action_ok', title: 'Points updated', message: 'Route points have been updated successfully!');
    }

    #[Computed]
    public function areas()
    {
        return Area::where('site_id', $this->contest->site_id)
            ->with(['sectors.lines.routes'])
            ->get();
    }
}; ?>

<div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center mb-6">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Manage Contest Routes')}}</h1>
            <p class="mt-2 text-sm text-gray-700">{{__('Select which routes are part of this contest')}}</p>
        </div>
    </div>

    @foreach($this->areas as $area)
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ $area->name }}</h2>
            @foreach($area->sectors as $sector)
                <div class="ml-2 mb-6">
                    <h3 class="text-md font-semibold text-gray-700 mb-3">{{ $sector->name }}</h3>
                    @foreach($sector->lines as $line)
                        <div class="ml-2 mb-4">
                            <h4 class="text-sm font-semibold text-gray-600 mb-2"></h4>
                            <div class="">
                                <table class="border-separate border-spacing-y-3 min-w-full divide-y divide-gray-300 table-fixed">
                                            <div class="bg-gray-50 w-full px-4 py-2 text-left text-xs font-semibold text-gray-700">
                                                {{ __('Line') }} {{ $line->local_id }}
                                            </div>
                                    <tbody class="">
                                        <div class="grid grid-cols-2 gap-x-8">
                                        @foreach($line->routes->chunk(ceil($line->routes->count() / 2)) as $chunk)
    
        @foreach($chunk as $route)
            <div class=" p-2 flex items-center">
                <input 
                    type="checkbox" 
                    wire:click="toggleRoute({{ $route->id }})"
                    @if(in_array($route->id, $selectedRoutes)) checked @endif
                    class="w-7 h-7 mr-2 rounded border-gray-300 text-gray-800 shadow-sm focus:border-gray-300 focus:ring focus:ring-gray-200 focus:ring-opacity-50"
                >
                <div class="bg-{{$route->color}}-300 border-2 border-{{$route->color}}-300 rounded-l-md h-16 w-16 relative">
                    <div class="rounded-l h-full w-full bg-cover" style="background-image: url({{ $route->thumbnail() }})"></div>
                </div>
                 <div class=" h-16 mr-2 text-2xl text-center w-16 bg-{{$route->color}}-300 relative whitespace-nowrap py-4 pl-4 pr-3 font-medium text-gray-900 sm:pl-3">{{$route->gradeFormated($route->line->sector->area->site->cotations_reverse())}}</div>
                <div class="flex-1">
                    <div class="font-bold">{{$route->name}}</div>
                    <div class="text-sm opacity-50">
                        @if($route->line->local_id == 0)
                            {{__('Sector')}} {{$route->line->sector->local_id}}
                        @else
                            {{__('Line')}} {{$route->line->local_id}}
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-1 w-16">
                    @if(in_array($route->id, $selectedRoutes))
                        <x-input 
                            wire:model.lazy="routePoints.{{ $route->id }}"
                            wire:change="updatePoints({{ $route->id }}, $event.target.value)"
                            min="1"
                            class="w-16 rounded-md border-gray-300 shadow-sm text-sm"
                            placeholder="Points"
                        />
                        <span class="text-xs text-gray-500">pts</span>
                    @else
                        <input disabled 
                            class="bg-gray-50 w-16 rounded-md border-gray-300 shadow-sm text-sm"
                            placeholder="Points"
                        >
                        <span class="text-xs text-gray-500">pts</span>
                    @endif
                </div>
            </div>
        @endforeach
    
@endforeach
</div>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endforeach

    @if($this->areas->count() === 0)
        <div class="text-center py-8 text-gray-500">
            {{ __('No routes available. Please add some routes to this site first.') }}
        </div>
    @endif
</div>
