<?php

use Livewire\Volt\Component;
use App\Models\Contest;
use App\Models\ContestStep;
use App\Models\Area;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;

new class extends Component {
    public Contest $contest;
    
    #[Validate('required|string|max:255')]
    public $name = '';
    
    #[Validate('required|integer|min:0')]
    public $order = 0;
    
    #[Validate('required|date')]
    public $start_time = '';
    
    #[Validate('required|date')]
    public $end_time = '';
    
    public $editingStep = null;
    public $routesModal = false;
    public $selectedRoutes = [];
    public $drawerOpen = false;
    public $routePoints;

    public ContestStep $step;

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->order = $this->contest->steps()->count();
        $this->start_time = '';
        $this->end_time = '';
        $this->editingStep = null;
    }
    public function openDrawerForAdd()
{
    $this->resetForm();
    $this->drawerOpen = true;
}

public function openDrawerForEdit($stepId)
{
    $this->editStep($stepId);
    $this->drawerOpen = true;
}

public function closeDrawer()
{
    $this->drawerOpen = false;
    $this->resetForm();
}


    public function addStep()
    {
        $this->validate();

        ContestStep::create([
            'contest_id' => $this->contest->id,
            'name' => $this->name,
            'order' => $this->order,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
        ]);

        $this->dispatch('action_ok', title: 'Step added', message: 'Contest step has been added successfully!');
        $this->drawerOpen = false;
        $this->resetForm();
    }

    public function editStep($stepId)
    {
        $this->step = ContestStep::findOrFail($stepId);
        $this->editingStep = $stepId;
        $this->name = $this->step->name;
        $this->order = $this->step->order;
        $this->start_time = $this->step->start_time->format('Y-m-d\TH:i');
        $this->end_time = $this->step->end_time->format('Y-m-d\TH:i');
    }

    public function updateStep()
    {
        $this->validate();
        $this->step->update([
            'name' => $this->name,
            'order' => $this->order,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
        ]);

        $this->dispatch('action_ok', title: 'Step updated', message: 'Contest step has been updated successfully!');
        $this->drawerOpen = false;
        $this->resetForm();
    }

    public function deleteStep($stepId)
    {
        $step = ContestStep::findOrFail($stepId);
        $step->delete();
        $this->dispatch('action_ok', title: 'Step deleted', message: 'Contest step has been deleted successfully!');
    }

    public function cancelEdit()
    {
        $this->resetForm();
    }

    public function manageRoutes($stepId)
    {
        $this->step = ContestStep::findOrFail($stepId);
        $this->routesModal = true;
        $this->selectedRoutes = $this->step->routes->pluck('id')->toArray();
         foreach ($this->step->routes as $route) {
            $this->routePoints[$route->id] = $route->pivot->points;
        }
    }

    public function updatePoints($routeId, $points)
    {
        $points = max(1, (int)$points); // Ensure positive integer
        $this->routePoints[$routeId] = $points;
        $this->step->routes()->updateExistingPivot($routeId, ['points' => $points]);
    }

    public function toggleRoute($routeId)
    {
        
        if (in_array($routeId, $this->selectedRoutes)) {
            $this->selectedRoutes = array_diff($this->selectedRoutes, [$routeId]);
            $this->step->routes()->detach($routeId);
        } else {
            $this->selectedRoutes[] = $routeId;
            $this->step->routes()->attach($routeId, ['points' => 100]);
        }
        
        $this->dispatch('action_ok', title: 'Routes updated', message: 'Step routes have been updated successfully!');
    }

    public function closeRouteManager()
    {
        $this->routesModal = false;
        $this->selectedRoutes = [];
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
            <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Manage Contest Steps')}}</h1>
            <p class="mt-2 text-sm text-gray-700">{{__('Define qualification waves and contest steps with specific time periods')}}</p>
        </div>
        <div>
            <x-button wire:click="openDrawerForAdd">{{__('Add Step')}}</x-button>
        </div>
    </div>

    <!-- Add/Edit Form -->
<x-drawer open='drawerOpen' :title="$editingStep ? __('Edit Step') : __('Add New Step')" :subtitle="__('Define the details for this contest step.')" :save_method_name="$editingStep ? 'updateStep' : 'addStep' ">
    <div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <x-label for="name" :value="__('Step Name')" />
                <x-input type="text" wire:model="name" id="name" class="mt-1 block w-full" placeholder="e.g., Pre-qualification Wave 1, Final"/>
                <x-input-error for='name'/>
            </div>
            <div>
                <x-label for="order" :value="__('Order')" />
                <x-input type="number" wire:model="order" id="order" min="0" class="mt-1 block w-full"/>
               <x-input-error for='order'/>
            </div>
            <div>
                <x-label for="start_time" :value="__('Start Time')" />
                <x-input type="datetime-local" wire:model="start_time" id="start_time" class="mt-1 block w-full"/>
                <x-input-error for='start_time'/>
            </div>
            <div>
                <x-label for="end_time" :value="__('End Time')" />
                <x-input type="datetime-local" wire:model="end_time" id="end_time" class="mt-1 block w-full"/>
                <x-input-error for='end_time'/>
               
            </div>
        </div>
    </div>
    <x-slot name="footer">
         <div class="flex justify-end space-x-3">
        @if($editingStep)
            <x-button type="submit" >{{__('Update Step')}}</x-button>
            @else
            <x-button type="submit" >{{__('Add Step')}}</x-button>
        @endif
        <x-secondary-button wire:click="closeDrawer">{{__('Cancel')}}</x-secondary-button>
         </div>
    </x-slot>
</x-drawer>

    <!-- Steps List -->
    <div class="">
        <div class="px-4 py-5 sm:p-6">
            
            @if($contest->steps->count() > 0)
        <ol class="relative border-l-2 border-gray-300 ml-4 space-y-8">
            @foreach($contest->steps->sortBy('order') as $step)
                <li class="mb-10 ml-6">
                    <span class="absolute -left-3.5 flex items-center justify-center w-7 h-7 rounded-full
    @if($step->isActive()) bg-gray-800 text-white
    @else($step->isPast()) bg-gray-100 text-gray-800
   
    @endif
    ring-4 ring-white  font-bold text-base">
    {{ $step->order }}
</span>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="ml-2 text-lg font-semibold text-gray-900">{{ $step->name }}</span>
                            @if($step->isActive())
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-900 text-white">
                                    {{__('Active')}}
                                </span>
                            @elseif($step->isPast())
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                    {{__('Past')}}
                                </span>
                            @else
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                                    {{__('Upcoming')}}
                                </span>
                            @endif
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="manageRoutes({{ $step->id }})" type="button"
                                class=" py-1.5  text-gray-600 hover:text-gray-900">
                                <x-icons.icon-path/>
                            </button>
                            <button wire:click="openDrawerForEdit({{ $step->id }})" type="button"
                                class="py-1.5 text-gray-600 hover:text-gray-900">
                                <x-icons.icon-edit/>
                            </button>
                            <div wire:click="deleteStep({{ $step->id }})" type="button"
                                wire:confirm="Are you sure you want to delete this step?"
                                class="py-1.5   text-red-600  hover:text-red-700 ">
                                <x-icons.icon-delete/>
                            </div>
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        {{ $step->start_time->format('M d, Y H:i') }} - {{ $step->end_time->format('M d, Y H:i') }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ $step->routes->count() }} {{__('routes assigned')}}
                    </p>
                </li>
            @endforeach
        </ol>
    @else
        <div class="text-center py-8 text-gray-500">
            {{__('No steps defined yet. Add steps to organize your contest into qualification waves or rounds.')}}
        </div>
    @endif
        </div>
    </div>

    <!-- Route Management Modal -->
    @if($routesModal)
        <div class="fixed inset-0 bg-gray-500/75  flex items-center justify-center z-50" wire:click="closeRouteManager">
            <div class="bg-white rounded-lg max-w-5xl w-full max-h-[90vh] overflow-hidden" wire:click.stop>
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">{{__('Manage Routes for Step')}}</h3>
                        <button wire:click="closeRouteManager" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="px-6 py-4 overflow-y-auto max-h-[calc(90vh-8rem)]">
                    @foreach($this->areas as $area)
                        <div class="mb-8">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ $area->name }}</h2>
                            @foreach($area->sectors as $sector)
                                <div class="ml-2 mb-6">
                                    <h3 class="text-md font-semibold text-gray-700 mb-3">{{ $sector->name }}</h3>
                                    @foreach($sector->lines as $line)
                                        <div class="ml-2 mb-4">
                                            <div class="bg-gray-50 w-full px-4 py-2 text-left text-xs font-semibold text-gray-700">
                                                {{ __('Line') }} {{ $line->local_id }}
                                            </div>
                                            <div>
                                                <div class="grid grid-cols-2 gap-x-8">
                                                    @foreach($line->routes->chunk(ceil($line->routes->count() / 2)) as $chunk)
                                                        @foreach($chunk as $route)
                                                            <div class="p-2 flex items-center">
                                                                <input 
                                                                    type="checkbox" 
                                                                    wire:click="toggleRoute({{ $route->id }})"
                                                                    @if(in_array($route->id, $selectedRoutes)) checked @endif
                                                                    class="w-7 h-7 mr-2 rounded border-gray-300 text-gray-800 shadow-sm focus:border-gray-300 focus:ring focus:ring-gray-200 focus:ring-opacity-50"
                                                                >
                                                                <div class="bg-{{$route->color}}-300 border-2 border-{{$route->color}}-300 rounded-l-md h-16 w-16 relative">
                                                                    <div class="rounded-l h-full w-full bg-cover" style="background-image: url({{ $route->thumbnail() }})"></div>
                                                                </div>
                                                                <div class="h-16 mr-2 text-2xl text-center w-16 bg-{{$route->color}}-300 relative whitespace-nowrap py-4 pl-4 pr-3 font-medium text-gray-900 sm:pl-3">
                                                                    {{$route->gradeFormated($route->line->sector->area->site->cotations_reverse())}}
                                                                </div>
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
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                <div class="px-6 py-4 border-t border-gray-200">
                    <button wire:click="closeRouteManager" type="button"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        {{__('Close')}}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
