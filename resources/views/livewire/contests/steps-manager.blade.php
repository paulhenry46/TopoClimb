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
    public $managingRoutesForStep = null;
    public $selectedRoutes = [];

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
        $this->resetForm();
    }

    public function editStep($stepId)
    {
        $step = ContestStep::findOrFail($stepId);
        $this->editingStep = $stepId;
        $this->name = $step->name;
        $this->order = $step->order;
        $this->start_time = $step->start_time->format('Y-m-d\TH:i');
        $this->end_time = $step->end_time->format('Y-m-d\TH:i');
    }

    public function updateStep()
    {
        $this->validate();

        $step = ContestStep::findOrFail($this->editingStep);
        $step->update([
            'name' => $this->name,
            'order' => $this->order,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
        ]);

        $this->dispatch('action_ok', title: 'Step updated', message: 'Contest step has been updated successfully!');
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
        $step = ContestStep::findOrFail($stepId);
        $this->managingRoutesForStep = $stepId;
        $this->selectedRoutes = $step->routes->pluck('id')->toArray();
    }

    public function toggleRoute($routeId)
    {
        if (!$this->managingRoutesForStep) {
            return;
        }

        $step = ContestStep::findOrFail($this->managingRoutesForStep);
        
        if (in_array($routeId, $this->selectedRoutes)) {
            $this->selectedRoutes = array_diff($this->selectedRoutes, [$routeId]);
            $step->routes()->detach($routeId);
        } else {
            $this->selectedRoutes[] = $routeId;
            $step->routes()->attach($routeId);
        }
        
        $this->dispatch('action_ok', title: 'Routes updated', message: 'Step routes have been updated successfully!');
    }

    public function closeRouteManager()
    {
        $this->managingRoutesForStep = null;
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
    </div>

    <!-- Add/Edit Form -->
    <div class="bg-white shadow sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">
                {{ $editingStep ? __('Edit Step') : __('Add New Step') }}
            </h3>
            
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">{{__('Step Name')}}</label>
                    <input type="text" wire:model="name" id="name" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="e.g., Pre-qualification Wave 1, Final">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="order" class="block text-sm font-medium text-gray-700">{{__('Order')}}</label>
                    <input type="number" wire:model="order" id="order" min="0"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('order') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700">{{__('Start Time')}}</label>
                    <input type="datetime-local" wire:model="start_time" id="start_time"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('start_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700">{{__('End Time')}}</label>
                    <input type="datetime-local" wire:model="end_time" id="end_time"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('end_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-4 flex gap-2">
                @if($editingStep)
                    <button wire:click="updateStep" type="button"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{__('Update Step')}}
                    </button>
                    <button wire:click="cancelEdit" type="button"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{__('Cancel')}}
                    </button>
                @else
                    <button wire:click="addStep" type="button"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{__('Add Step')}}
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Steps List -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">{{__('Contest Steps')}}</h3>
            
            @if($contest->steps->count() > 0)
                <div class="space-y-3">
                    @foreach($contest->steps as $step)
                        <div class="flex items-center justify-between p-4 border rounded-lg">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        #{{ $step->order }}
                                    </span>
                                    <h4 class="text-sm font-semibold text-gray-900">{{ $step->name }}</h4>
                                    @if($step->isActive())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{__('Active')}}
                                        </span>
                                    @elseif($step->isPast())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{__('Past')}}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{__('Upcoming')}}
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $step->start_time->format('M d, Y H:i') }} - {{ $step->end_time->format('M d, Y H:i') }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $step->routes->count() }} {{__('routes assigned')}}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="manageRoutes({{ $step->id }})" type="button"
                                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    {{__('Manage Routes')}}
                                </button>
                                <button wire:click="editStep({{ $step->id }})" type="button"
                                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    {{__('Edit')}}
                                </button>
                                <button wire:click="deleteStep({{ $step->id }})" type="button"
                                    wire:confirm="Are you sure you want to delete this step?"
                                    class="inline-flex items-center px-3 py-1.5 border border-red-300 text-xs font-medium rounded text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    {{__('Delete')}}
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    {{__('No steps defined yet. Add steps to organize your contest into qualification waves or rounds.')}}
                </div>
            @endif
        </div>
    </div>

    <!-- Route Management Modal -->
    @if($managingRoutesForStep)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50" wire:click="closeRouteManager">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden" wire:click.stop>
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
                        <div class="mb-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-3">{{ $area->name }}</h4>
                            
                            @foreach($area->sectors as $sector)
                                <div class="ml-4 mb-4">
                                    <h5 class="text-sm font-semibold text-gray-700 mb-2">{{ $sector->name }}</h5>
                                    
                                    @foreach($sector->lines as $line)
                                        <div class="ml-4 mb-3">
                                            <h6 class="text-xs font-semibold text-gray-600 mb-1">{{ $line->name }}</h6>
                                            
                                            <div class="ml-4 space-y-1">
                                                @foreach($line->routes as $route)
                                                    <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                                        <input type="checkbox" 
                                                            wire:click="toggleRoute({{ $route->id }})"
                                                            @if(in_array($route->id, $selectedRoutes)) checked @endif
                                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                        <span class="ml-2 text-sm text-gray-700">{{ $route->name }}</span>
                                                        <span class="ml-2 text-xs text-gray-500">({{ $route->color }})</span>
                                                    </label>
                                                @endforeach
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
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{__('Close')}}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
