<?php

use Livewire\Volt\Component;
use App\Models\Contest;
use App\Models\Team;
use Livewire\Attributes\Validate;
use Illuminate\Support\Str;

new class extends Component {
    public Contest $contest;
    public $modal_open = false;
    public $join_team_modal_open = false;
    
    #[Validate('required|string|max:255')]
    public $name = '';
    
    public $user_team = null;
    public $available_teams = [];

    public function mount()
    {
        $this->loadUserTeam();
        $this->loadAvailableTeams();
    }

    public function loadUserTeam()
    {
        // Get the team the current user is part of for this contest
        $this->user_team = $this->contest->teams()
            ->whereHas('users', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->with('users', 'creator')
            ->first();
    }

    public function loadAvailableTeams()
    {
        // Only load available teams if user is not in a team and contest allows joining
        if (!$this->user_team && $this->contest->canUserJoinTeam(auth()->user())) {
            $this->available_teams = $this->contest->teams()
                ->with('users')
                ->get()
                ->filter(function($team) {
                    return !$team->isFull();
                })
                ->values()
                ->toArray();
        } else {
            $this->available_teams = [];
        }
    }

    public function createTeam()
    {
        // Check if user can create team
        if (!$this->contest->canUserCreateTeam(auth()->user())) {
            $this->dispatch('action_error', title: 'Error', message: 'You cannot create a team in this contest.');
            return;
        }

        // Check if user is already in a team
        if ($this->user_team) {
            $this->dispatch('action_error', title: 'Error', message: 'You are already in a team for this contest.');
            return;
        }

        $this->validate();

        // Create team
        $team = Team::create([
            'name' => $this->name,
            'contest_id' => $this->contest->id,
            'max_users' => 5, // Default max users (users cannot change this)
            'created_by' => auth()->id(),
        ]);

        // Generate invitation token for free mode
        if ($this->contest->isTeamModeFree()) {
            $team->generateInvitationToken();
        }

        // Add creator to team
        $team->users()->attach(auth()->id());

        $this->dispatch('action_ok', title: 'Team created', message: 'Your team has been created successfully!');
        $this->modal_open = false;
        $this->reset('name');
        $this->loadUserTeam();
        $this->loadAvailableTeams();
    }

    public function joinTeam($teamId)
    {
        // Check if user can join team
        if (!$this->contest->canUserJoinTeam(auth()->user())) {
            $this->dispatch('action_error', title: 'Error', message: 'You cannot join a team in this contest.');
            return;
        }

        // Check if user is already in a team
        if ($this->user_team) {
            $this->dispatch('action_error', title: 'Error', message: 'You are already in a team for this contest. Leave your current team first.');
            return;
        }

        $team = Team::findOrFail($teamId);

        // Check if team is full
        if ($team->isFull()) {
            $this->dispatch('action_error', title: 'Error', message: 'This team is full.');
            return;
        }

        // Add user to team
        $team->users()->attach(auth()->id());

        $this->dispatch('action_ok', title: 'Joined team', message: 'You have joined the team successfully!');
        $this->join_team_modal_open = false;
        $this->loadUserTeam();
        $this->loadAvailableTeams();
    }

    public function leaveTeam()
    {
        if (!$this->user_team) {
            return;
        }

        // Remove user from team
        $this->user_team->users()->detach(auth()->id());

        // If team is empty and was user-created, delete it
        if ($this->user_team->users()->count() === 0 && $this->user_team->created_by) {
            $this->user_team->delete();
        }

        $this->dispatch('action_ok', title: 'Left team', message: 'You have left the team successfully!');
        $this->loadUserTeam();
        $this->loadAvailableTeams();
    }

    public function deleteTeam()
    {
        if (!$this->user_team) {
            return;
        }

        // In free mode, any team member can delete the team
        // Check if user is a member
        $isMember = $this->user_team->users->contains(auth()->id());
        
        if (!$isMember) {
            $this->dispatch('action_error', title: 'Error', message: 'You are not a member of this team.');
            return;
        }

        // Only allow deletion in free mode
        if (!$this->contest->isTeamModeFree()) {
            $this->dispatch('action_error', title: 'Error', message: 'Only team members can delete teams in free mode.');
            return;
        }

        $this->user_team->delete();
        $this->dispatch('action_ok', title: 'Team deleted', message: 'The team has been deleted successfully!');
        $this->loadUserTeam();
        $this->loadAvailableTeams();
    }

    public function copyInvitationLink()
    {
        if (!$this->user_team || !$this->contest->isTeamModeFree()) {
            return;
        }

        // Generate token if not exists
        if (!$this->user_team->invitation_token) {
            $this->user_team->generateInvitationToken();
            $this->loadUserTeam();
        }

        $this->dispatch('copy_to_clipboard', text: route('contests.team.join', ['contest' => $this->contest->id, 'token' => $this->user_team->invitation_token]));
        $this->dispatch('action_ok', title: 'Link copied', message: 'Invitation link copied to clipboard!');
    }
}; ?>

<div class="px-4 sm:px-6 lg:px-8 py-8">
    @if($user_team)
        <!-- User's Team -->
        <div class="bg-white shadow sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ $user_team->name }}</h2>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $user_team->users->count() }} / {{ $user_team->max_users }} {{__('members')}}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        @if($contest->isTeamModeFree())
                            <button 
                                wire:click="copyInvitationLink" 
                                class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
                                    <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z" />
                                </svg>
                                {{__('Copy Invite Link')}}
                            </button>
                            <button 
                                wire:click="deleteTeam" 
                                wire:confirm="{{__('Are you sure you want to delete this team? This action cannot be undone.')}}"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                {{__('Delete Team')}}
                            </button>
                        @endif
                        <button 
                            wire:click="leaveTeam" 
                            wire:confirm="{{__('Are you sure you want to leave this team?')}}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            {{__('Leave Team')}}
                        </button>
                    </div>
                </div>

                <div class="mt-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">{{__('Team Members')}}</h3>
                    <ul class="divide-y divide-gray-200 border rounded-md">
                        @foreach($user_team->users as $user)
                            <li class="px-4 py-3 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                </div>
                                @if($user_team->created_by === $user->id)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        {{__('Creator')}}
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @else
        <!-- No Team - Show Options -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{__('Team Management')}}</h2>
                
                @if($contest->canUserCreateTeam(auth()->user()))
                    <div class="mb-6">
                        <button 
                            wire:click="$set('modal_open', true)" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                            {{__('Create New Team')}}
                        </button>
                        <p class="mt-2 text-sm text-gray-500">{{__('Create your own team and invite others to join.')}}</p>
                    </div>
                @endif

                @if($contest->canUserJoinTeam(auth()->user()) && count($available_teams) > 0)
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-3">{{__('Available Teams')}}</h3>
                        <div class="space-y-2">
                            @foreach($available_teams as $team)
                                <div class="border rounded-lg p-4 flex items-center justify-between hover:bg-gray-50">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $team['name'] }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ count($team['users']) }} / {{ $team['max_users'] }} {{__('members')}}
                                        </p>
                                    </div>
                                    @if($contest->isTeamModeRegister() || $contest->isTeamModeFree())
                                        <button 
                                            wire:click="joinTeam({{ $team['id'] }})" 
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                            {{__('Join Team')}}
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif($contest->canUserJoinTeam(auth()->user()))
                    <p class="text-sm text-gray-500">{{__('No available teams at the moment. You can create your own team or wait for others to create one.')}}</p>
                @else
                    <p class="text-sm text-gray-500">{{__('Team registration is managed by administrators for this contest.')}}</p>
                @endif
            </div>
        </div>
    @endif

    <!-- Create Team Modal -->
    <x-drawer 
        open="modal_open" 
        :title="__('Create Team')" 
        :subtitle="__('Create a new team for this contest')"
        save_method_name="createTeam">
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
                {{ __('Create Team') }}
            </x-button>
        </x-slot>
    </x-drawer>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('copy_to_clipboard', (event) => {
            navigator.clipboard.writeText(event.text);
        });
    });
</script>
