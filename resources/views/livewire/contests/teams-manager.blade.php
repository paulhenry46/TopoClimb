<?php

use Livewire\Volt\Component;
use App\Models\Contest;
use App\Models\Team;
use App\Models\User;
use Livewire\Attributes\Validate;

new class extends Component {
    public Contest $contest;
    public $modal_open = false;
    public $add_user_modal_open = false;
    public $selected_team_id = null;
    
    #[Validate('required|string|max:255')]
    public $name = '';
    
    #[Validate('required|integer|min:1|max:100')]
    public $max_users = 5;
    
    public $id_editing = 0;
    
    public $search_query = '';
    public $search_results = [];

    public function mount()
    {
        // Check if contest has team mode enabled
        if (!$this->contest->hasTeamMode()) {
            abort(403, 'Team mode is not enabled for this contest.');
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->id_editing > 0) {
            $team = Team::findOrFail($this->id_editing);
            $team->update([
                'name' => $this->name,
                'max_users' => $this->max_users,
            ]);
            $this->dispatch('action_ok', title: 'Team updated', message: 'Team has been updated successfully!');
        } else {
            Team::create([
                'name' => $this->name,
                'contest_id' => $this->contest->id,
                'max_users' => $this->max_users,
                'created_by' => auth()->id(),
            ]);
            $this->dispatch('action_ok', title: 'Team created', message: 'Team has been created successfully!');
        }

        $this->modal_open = false;
        $this->reset(['name', 'max_users', 'id_editing']);
        $this->max_users = 5; // Reset to default
    }

    public function edit($id)
    {
        $team = Team::findOrFail($id);
        $this->id_editing = $team->id;
        $this->name = $team->name;
        $this->max_users = $team->max_users;
        $this->modal_open = true;
    }

    public function delete($id)
    {
        $team = Team::findOrFail($id);
        $team->delete();
        $this->dispatch('action_ok', title: 'Team deleted', message: 'Team has been deleted successfully!');
    }

    public function openAddUserModal($teamId)
    {
        $this->selected_team_id = $teamId;
        $this->add_user_modal_open = true;
        $this->search_query = '';
        $this->search_results = [];
    }

    public function searchUsers()
    {
        if (strlen($this->search_query) < 2) {
            $this->search_results = [];
            return;
        }

        // Get users already in teams for this contest
        $usersInTeams = $this->contest->teams()
            ->with('users')
            ->get()
            ->pluck('users')
            ->flatten()
            ->pluck('id')
            ->toArray();

        // Search for users not in any team for this contest
        $this->search_results = User::where(function($query) {
                $query->where('name', 'like', '%' . $this->search_query . '%')
                      ->orWhere('email', 'like', '%' . $this->search_query . '%');
            })
            ->whereNotIn('id', $usersInTeams)
            ->limit(10)
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            })
            ->toArray();
    }

    public function addUserToTeam($userId)
    {
        $team = Team::findOrFail($this->selected_team_id);
        
        // Check if user is already in another team for this contest
        $existingTeam = $this->contest->teams()
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->first();
        
        if ($existingTeam && $existingTeam->id !== $team->id) {
            $this->dispatch('action_error', title: 'Error', message: 'User is already in another team for this contest.');
            return;
        }
        
        // Admins can always add users, even if team is full
        $team->users()->syncWithoutDetaching([$userId]);
        $this->dispatch('action_ok', title: 'Member added', message: 'Team member has been added successfully!');
        
        // Close modal and reset search
        $this->add_user_modal_open = false;
        $this->search_query = '';
        $this->search_results = [];
    }

    public function removeUserFromTeam($teamId, $userId)
    {
        $team = Team::findOrFail($teamId);
        $team->users()->detach($userId);
        $this->dispatch('action_ok', title: 'Member removed', message: 'Team member has been removed successfully!');
    }
}; ?>

<div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center mb-6">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Manage Teams')}}</h1>
            <p class="mt-2 text-sm text-gray-700">{{__('Create and manage teams for this contest')}}</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <button wire:click="$set('modal_open', true)" type="button"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{__('Create Team')}}
            </button>
        </div>
    </div>

    <!-- Teams List -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            @if($contest->teams->count() > 0)
                <div class="space-y-4">
                    @foreach($contest->teams as $team)
                        <div class="border rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $team->name }}</h3>
                                    <p class="text-sm text-gray-500">
                                        {{ $team->users->count() }} / {{ $team->max_users }} {{__('members')}}
                                        @if($team->created_by)
                                            • {{__('Created by')}} {{ $team->creator->name }}
                                        @endif
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <button wire:click="openAddUserModal({{ $team->id }})" 
                                        class="text-green-600 hover:text-green-900"
                                        title="{{__('Add member')}}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
                                        </svg>
                                    </button>
                                    <button wire:click="edit({{ $team->id }})" class="text-gray-600 hover:text-gray-900">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                    </button>
                                    <button wire:click="delete({{ $team->id }})" 
                                        wire:confirm="{{__('Are you sure you want to delete this team?')}}"
                                        class="text-red-600 hover:text-red-900">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mt-2">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">{{__('Team Members')}}</h4>
                                @if($team->users->count() > 0)
                                    <ul class="space-y-1">
                                        @foreach($team->users as $user)
                                            <li class="flex items-center justify-between text-sm text-gray-600">
                                                <span>{{ $user->name }} ({{ $user->email }})</span>
                                                <button wire:click="removeUserFromTeam({{ $team->id }}, {{ $user->id }})"
                                                    wire:confirm="{{__('Remove this member from the team?')}}"
                                                    class="text-red-500 hover:text-red-700">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-sm text-gray-500">{{__('No members yet')}}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    {{__('No teams created yet. Click "Create Team" to get started.')}}
                </div>
            @endif
        </div>
    </div>

    <!-- Create/Edit Team Modal -->
    <x-drawer 
        open="modal_open" 
        :title="$id_editing > 0 ? __('Edit Team') : __('Create Team')" 
        :subtitle="$id_editing > 0 ? __('Update team information') : __('Create a new team for this contest')"
        save_method_name="save">
        <div>
            <div class="mt-4">
                <x-label for="name" value="{{ __('Team Name') }}" />
                <x-input id="name" type="text" class="mt-1 block w-full" wire:model="name" />
                <x-input-error for="name" class="mt-2" />
            </div>
            
            <div class="mt-4">
                <x-label for="max_users" value="{{ __('Maximum number of users') }}" />
                <x-input id="max_users" type="number" min="1" max="100" class="mt-1 block w-full" wire:model="max_users" />
                <x-input-error for="max_users" class="mt-2" />
                <p class="mt-1 text-sm text-gray-500">{{__('The maximum number of users that can join this team.')}}</p>
            </div>
        </div>
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('modal_open', false)">
                {{ __('Cancel') }}
            </x-secondary-button>
            <x-button class="ml-2">
                {{ $id_editing > 0 ? __('Update') : __('Create') }}
            </x-button>
        </x-slot>
    </x-drawer>

    <!-- Add User Modal -->
    <x-drawer 
        open="add_user_modal_open" 
        :title="__('Add Team Member')" 
        :subtitle="__('Search and add a user to the team')">
        <div>
            <div class="mt-4">
                <x-label for="search_query" value="{{ __('Search Users') }}" />
                <x-input 
                    id="search_query" 
                    type="text" 
                    class="mt-1 block w-full" 
                    wire:model.live.debounce.300ms="search_query"
                    placeholder="{{ __('Type name or email...') }}" />
                <p class="mt-1 text-sm text-gray-500">{{__('Search for users by name or email address.')}}</p>
            </div>

            @if(count($search_results) > 0)
                <div class="mt-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">{{__('Search Results')}}</h4>
                    <ul class="divide-y divide-gray-200 border rounded-md">
                        @foreach($search_results as $user)
                            <li class="flex items-center justify-between p-3 hover:bg-gray-50">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $user['name'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $user['email'] }}</p>
                                </div>
                                <button 
                                    wire:click="addUserToTeam({{ $user['id'] }})"
                                    class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    {{__('Add')}}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @elseif(strlen($search_query) >= 2)
                <div class="mt-4 text-center text-sm text-gray-500">
                    {{__('No users found matching your search.')}}
                </div>
            @endif
        </div>
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('add_user_modal_open', false)">
                {{ __('Close') }}
            </x-secondary-button>
        </x-slot>
    </x-drawer>
</div>
