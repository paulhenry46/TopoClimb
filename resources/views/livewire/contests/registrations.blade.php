<?php

use Livewire\Volt\Component;
use App\Models\Contest;
use App\Models\User;
use App\Models\Route;
use App\Models\Log;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;

new class extends Component {
    public Contest $contest;
    
    #[Validate('required|exists:users,id')]
    public $user_id = '';
    
    #[Validate('required|exists:routes,id')]
    public $route_id = '';

    #[Validate('nullable|in:work,flash,view')]
    public $type = 'flash';

    #[Validate('nullable|in:top-rope,lead,bouldering')]
    public $way = 'bouldering';

    #[Validate('nullable|string')]
    public $comment = '';

    public $search_user = '';

    public function registerClimb()
    {
        $this->validate();

        // Get the route to determine the grade
        $route = Route::findOrFail($this->route_id);

        // Create a log with verified_by set to current staff member
        Log::create([
            'route_id' => $this->route_id,
            'user_id' => $this->user_id,
            'grade' => $route->grade,
            'type' => $this->type,
            'way' => $this->way,
            'comment' => $this->comment,
            'verified_by' => auth()->id(),
        ]);

        $this->dispatch('action_ok', title: 'Climb registered', message: 'The climb has been registered successfully!');
        $this->reset(['user_id', 'route_id', 'search_user', 'comment', 'type', 'way']);
        $this->type = 'flash';
        $this->way = 'bouldering';
    }

    #[Computed]
    public function contestRoutes()
    {
        return $this->contest->routes;
    }

    #[Computed]
    public function recentRegistrations()
    {
        return Log::whereIn('route_id', $this->contest->routes->pluck('id'))
            ->whereNotNull('verified_by')
            ->whereBetween('created_at', [$this->contest->start_date, $this->contest->end_date])
            ->with(['user', 'route', 'verifiedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function searchUsers()
    {
        if (strlen($this->search_user) < 2) {
            return collect();
        }

        return User::where('name', 'like', '%' . $this->search_user . '%')
            ->orWhere('email', 'like', '%' . $this->search_user . '%')
            ->limit(10)
            ->get();
    }

    public function selectUser($userId)
    {
        $this->user_id = $userId;
        $user = User::find($userId);
        $this->search_user = $user->name;
    }

    public function deleteRegistration($id)
    {
        $log = Log::findOrFail($id);
        $log->delete();
        
        $this->dispatch('action_ok', title: 'Registration deleted', message: 'The registration has been deleted successfully!');
    }
}; ?>

<div class="px-4 sm:px-6 lg:px-8 py-8">
    @if($contest->mode !== 'official')
        <div class="rounded-md bg-yellow-50 p-4 mb-6">
            <div class="flex">
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">{{ __('This contest is in Free Climb mode') }}</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>{{ __('In Free Climb mode, users log their own climbs. This registration interface is only for Official contests.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="sm:flex sm:items-center mb-6">
            <div class="sm:flex-auto">
                <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Register Climbs')}}</h1>
                <p class="mt-2 text-sm text-gray-700">{{__('Register successful climbs for participants in this official contest. Logs will be marked as verified.')}}</p>
            </div>
        </div>

        <!-- Registration Form -->
        <div class="bg-white shadow sm:rounded-lg mb-8">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">{{__('New Registration')}}</h3>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="search_user" class="block text-sm font-medium leading-6 text-gray-900">{{__('Climber')}}</label>
                        <input 
                            type="text" 
                            id="search_user"
                            wire:model.live="search_user"
                            placeholder="{{__('Search by name or email...')}}"
                            class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        />
                        
                        @if($this->searchUsers->count() > 0 && $search_user && !$user_id)
                            <div class="mt-2 border rounded-md shadow-sm max-h-48 overflow-y-auto">
                                @foreach($this->searchUsers as $user)
                                    <button 
                                        type="button"
                                        wire:click="selectUser({{ $user->id }})"
                                        class="block w-full text-left px-4 py-2 hover:bg-gray-100"
                                    >
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                        
                        @error('user_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="route_id" class="block text-sm font-medium leading-6 text-gray-900">{{__('Route')}}</label>
                        <select 
                            id="route_id"
                            wire:model="route_id"
                            class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        >
                            <option value="">{{__('Select a route')}}</option>
                            @foreach($this->contestRoutes as $route)
                                <option value="{{ $route->id }}">
                                    {{ $route->name }} - {{ $route->gradeFormated($route->line->sector->area->site->cotations_reverse()) ?? $route->defaultGradeFormated() }}
                                </option>
                            @endforeach
                        </select>
                        @error('route_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium leading-6 text-gray-900">{{__('Type')}}</label>
                        <select 
                            id="type"
                            wire:model="type"
                            class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        >
                            <option value="flash">{{__('Flash')}}</option>
                            <option value="work">{{__('Work')}}</option>
                            <option value="view">{{__('View')}}</option>
                        </select>
                    </div>

                    <div>
                        <label for="way" class="block text-sm font-medium leading-6 text-gray-900">{{__('Way')}}</label>
                        <select 
                            id="way"
                            wire:model="way"
                            class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        >
                            <option value="bouldering">{{__('Bouldering')}}</option>
                            <option value="top-rope">{{__('Top-rope')}}</option>
                            <option value="lead">{{__('Lead')}}</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="comment" class="block text-sm font-medium leading-6 text-gray-900">{{__('Comment (optional)')}}</label>
                        <textarea 
                            id="comment"
                            wire:model="comment"
                            rows="2"
                            class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        ></textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <x-button wire:click="registerClimb">
                        {{__('Register Climb')}}
                    </x-button>
                </div>
            </div>
        </div>

        <!-- Recent Registrations -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">{{__('Recent Verified Registrations')}}</h3>
                
                @if($this->recentRegistrations->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead>
                                <tr>
                                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">{{__('Climber')}}</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Route')}}</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Type')}}</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Verified By')}}</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Date')}}</th>
                                    <th class="relative py-3.5 pl-3 pr-4">
                                        <span class="sr-only">{{__('Actions')}}</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($this->recentRegistrations as $log)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900">
                                            {{ $log->user->name }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ $log->route->name }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ ucfirst($log->type) }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ $log->verifiedBy?->name ?? __('Unknown') }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ $log->created_at->format('Y-m-d H:i') }}
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium">
                                            <button 
                                                wire:click="deleteRegistration({{ $log->id }})"
                                                wire:confirm="{{__('Are you sure you want to delete this registration?')}}"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                {{__('Delete')}}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500">{{__('No verified registrations yet.')}}</p>
                @endif
            </div>
        </div>
    @endif
</div>
