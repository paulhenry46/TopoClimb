<?php

use Livewire\Volt\Component;
use App\Models\Contest;
use App\Models\Team;
use Livewire\Attributes\Validate;

new class extends Component {
    public Contest $contest;
    public $modal_open = false;
    
    #[Validate('required|string|max:255')]
    public $name = '';
    
    public $id_editing = 0;

    public function mount()
    {
        // Check if contest has team mode enabled
        if (!$this->contest->team_mode) {
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
            ]);
            $this->dispatch('action_ok', title: 'Team updated', message: 'Team has been updated successfully!');
        } else {
            Team::create([
                'name' => $this->name,
                'contest_id' => $this->contest->id,
            ]);
            $this->dispatch('action_ok', title: 'Team created', message: 'Team has been created successfully!');
        }

        $this->modal_open = false;
        $this->reset(['name', 'id_editing']);
    }

    public function edit($id)
    {
        $team = Team::findOrFail($id);
        $this->id_editing = $team->id;
        $this->name = $team->name;
        $this->modal_open = true;
    }

    public function delete($id)
    {
        $team = Team::findOrFail($id);
        $team->delete();
        $this->dispatch('action_ok', title: 'Team deleted', message: 'Team has been deleted successfully!');
    }

    public function addUserToTeam($teamId, $userId)
    {
        $team = Team::findOrFail($teamId);
        
        // Check if user is already in another team for this contest
        $existingTeam = $this->contest->teams()
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->first();
        
        if ($existingTeam && $existingTeam->id !== $teamId) {
            $this->dispatch('action_error', title: 'Error', message: 'User is already in another team for this contest.');
            return;
        }
        
        $team->users()->syncWithoutDetaching([$userId]);
        $this->dispatch('action_ok', title: 'Member added', message: 'Team member has been added successfully!');
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
                                <h3 class="text-lg font-semibold text-gray-900">{{ $team->name }}</h3>
                                <div class="flex gap-2">
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
                                <h4 class="text-sm font-medium text-gray-700 mb-2">{{__('Team Members')}} ({{ $team->users->count() }})</h4>
                                @if($team->users->count() > 0)
                                    <ul class="space-y-1">
                                        @foreach($team->users as $user)
                                            <li class="flex items-center justify-between text-sm text-gray-600">
                                                <span>{{ $user->name }}</span>
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
</div>
