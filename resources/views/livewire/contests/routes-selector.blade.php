<?php

use Livewire\Volt\Component;
use App\Models\Contest;
use App\Models\Route;
use App\Models\Area;
use Livewire\Attributes\Computed;

new class extends Component {
    public Contest $contest;
    public $selectedRoutes = [];

    public function mount()
    {
        $this->selectedRoutes = $this->contest->routes->pluck('id')->toArray();
    }

    public function toggleRoute($routeId)
    {
        if (in_array($routeId, $this->selectedRoutes)) {
            $this->selectedRoutes = array_diff($this->selectedRoutes, [$routeId]);
            $this->contest->routes()->detach($routeId);
        } else {
            $this->selectedRoutes[] = $routeId;
            $this->contest->routes()->attach($routeId);
        }
        $this->dispatch('action_ok', title: 'Routes updated', message: 'Contest routes have been updated successfully!');
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
                <div class="ml-4 mb-6">
                    <h3 class="text-md font-semibold text-gray-700 mb-3">{{ $sector->name }}</h3>
                    
                    @foreach($sector->lines as $line)
                        <div class="ml-4 mb-4">
                            <h4 class="text-sm font-semibold text-gray-600 mb-2">{{ $line->name }}</h4>
                            
                            <div class="ml-4 space-y-2">
                                @foreach($line->routes as $route)
                                    <label class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            wire:click="toggleRoute({{ $route->id }})"
                                            @if(in_array($route->id, $selectedRoutes)) checked @endif
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        >
                                        <span class="ml-2 text-sm text-gray-700">
                                            {{ $route->name }} 
                                            <span class="text-gray-500">({{ $route->gradeFormated($route->line->sector->area->site->cotations_reverse()) ?? $route->defaultGradeFormated() }})</span>
                                            <span class="inline-block w-4 h-4 rounded-full ml-2" style="background-color: {{ $route->colorToHex() }}"></span>
                                        </span>
                                    </label>
                                @endforeach
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
