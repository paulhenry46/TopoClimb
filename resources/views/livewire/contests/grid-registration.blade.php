<?php

use Livewire\Volt\Component;
use App\Models\Contest;
use App\Models\User;
use App\Models\Route;
use App\Models\Log;
use Livewire\Attributes\Computed;

new class extends Component {
    public Contest $contest;
    
    // Array to track checked climbs [route_id][user_id] => true/false
    public $checked = [];
    
    public function mount()
    {
        $this->loadExistingClimbs();
    }
    
    public function loadExistingClimbs()
    {
        // Load existing verified logs for this contest
        $logs = Log::whereIn('route_id', $this->contestRoutes->pluck('id'))
            ->whereNotNull('verified_by')
            ->whereBetween('created_at', [$this->contest->start_date, $this->contest->end_date])
            ->get();
        
        foreach ($logs as $log) {
            $this->checked[$log->route_id][$log->user_id] = true;
        }
    }
    
    public function toggleClimb($routeId, $userId)
    {
        $route = Route::findOrFail($routeId);
        $user = User::findOrFail($userId);
        
        // Check current state
        $isChecked = isset($this->checked[$routeId][$userId]) && $this->checked[$routeId][$userId];
        
        if ($isChecked) {
            // Remove the log
            Log::where('route_id', $routeId)
                ->where('user_id', $userId)
                ->whereNotNull('verified_by')
                ->whereBetween('created_at', [$this->contest->start_date, $this->contest->end_date])
                ->delete();
            
            unset($this->checked[$routeId][$userId]);
        } else {
            // Create a log
           $log =  Log::create([
                'route_id' => $routeId,
                'user_id' => $userId,
                'grade' => $route->grade,
                'type' => 'flash',
                'way' => 'bouldering',
                'verified_by' => auth()->id(),
            ]);
            
            $this->checked[$routeId][$userId] = true;
        }
    }

    #[Computed]
    public function contestRoutes()
    {
        return $this->contest->steps
            ->flatMap(fn($step) => $step->routes)
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    #[Computed]
    public function authorizedUsers()
    {
        // Get authorized users, or all users if no restrictions
        $users = $this->contest->authorizedUsers;
        
        if ($users->isEmpty()) {
            // If no authorized users, show all users who have logged climbs in this contest
            $userIds = Log::whereIn('route_id', $this->contestRoutes->pluck('id'))
                ->whereBetween('created_at', [$this->contest->start_date, $this->contest->end_date])
                ->distinct('user_id')
                ->pluck('user_id');
            
            $users = User::whereIn('id', $userIds)->orderBy('name')->get();
        } else {
            $users = $users->sortBy('name')->values();
        }
        
        return $users;
    }
    
    public function isClimbChecked($routeId, $userId)
    {
        return isset($this->checked[$routeId][$userId]) && $this->checked[$routeId][$userId];
    }
}; ?>

<div class="px-4 sm:px-6 lg:px-8 py-8 bg-white">
    <div class="sm:flex sm:items-center mb-6">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Grid Registration')}}</h1>
            <p class="mt-2 text-sm text-gray-700">{{__('Quick registration interface. Click on cells to register/unregister climbs. Only authorized users are shown.')}}</p>
        </div>
    </div>

    @if($contest->mode !== 'official')
        <div class="rounded-md bg-yellow-50 p-4 mb-6">
            <div class="flex">
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">{{__('This contest is in Free Climb mode')}}</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>{{__('Grid registration is only available for Official contests.')}}</p>
                    </div>
                </div>
            </div>
        </div>
    @elseif($this->authorizedUsers->isEmpty())
        <div class="rounded-md bg-blue-50 p-4 mb-6">
            <div class="flex">
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-gray-800">{{__('No users to display')}}</h3>
                    <div class="mt-2 text-sm text-gray-700">
                        <p>{{__('Either no authorized users are set, or no users have climbs yet. Add authorized users first.')}}</p>
                    </div>
                </div>
            </div>
        </div>
    @elseif($this->contestRoutes->isEmpty())
        <div class="rounded-md bg-blue-50 p-4 mb-6">
            <div class="flex">
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-gray-800">{{__('No routes in contest')}}</h3>
                    <div class="mt-2 text-sm text-gray-700">
                        <p>{{__('Add routes to contest steps first.')}}</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class=" sm:rounded-lg overflow-x-auto">
            <div class="px-4 py-5 sm:p-6">
                <div class="overflow-x-auto border-l border-gray-300">
                    <table class="min-w-full border-collapse border border-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="sticky left-0 z-10 bg-gray-50 border border-gray-300 px-3 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    {{__('User')}}
                                </th>
                                @foreach($this->contestRoutes as $route)
                                    <th class="border border-gray-300 px-2 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider min-w-[100px]">
                                        <div class="flex flex-col items-center">
                                            <span class="font-semibold">{{ $route->name }}</span>
                                            <span class="text-gray-500 font-normal mt-1">
                                                {{ $route->gradeFormated($route->line->sector->area->site->cotations_reverse()) ?? $route->defaultGradeFormated() }}
                                            </span>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($this->authorizedUsers as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="sticky left-0 z-10 bg-gray-100 border-r border-gray-300 px-3 py-2 text-sm font-medium text-gray-900 whitespace-nowrap">
                                        {{ $user->name }}
                                    </td>
                                    @foreach($this->contestRoutes as $route)
                                        <td class="border border-gray-300 px-2 py-2 text-center">
                                            <button
                                                type="button"
                                                wire:click="toggleClimb({{ $route->id }}, {{ $user->id }})"
                                                class="w-full h-full min-h-[40px] flex items-center justify-center transition-colors duration-150 rounded
                                                    {{ $this->isClimbChecked($route->id, $user->id) ? 'bg-green-100 hover:bg-green-200' : 'bg-white hover:bg-gray-100' }}"
                                            >
                                                @if($this->isClimbChecked($route->id, $user->id))
                                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                @else
                                                    <span class="text-gray-300">—</span>
                                                @endif
                                            </button>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mt-4 text-sm text-gray-600">
            <p>{{__('Click on a cell to toggle climb registration. Green checkmarks indicate completed climbs.')}}</p>
        </div>
    @endif
</div>
