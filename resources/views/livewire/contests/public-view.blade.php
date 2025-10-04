<?php

use Livewire\Volt\Component;
use App\Models\Contest;
use App\Models\ContestStep;
use App\Models\Team;
use Livewire\Attributes\Computed;

new class extends Component {
    public Contest $contest;
    public $selectedStepId = null;
    public $selectedCategoryId = null;
    public $viewMode = 'individual'; // 'individual', 'team', 'category'

    public function mount()
    {
        // Determine default view mode
        if ($this->contest->team_mode) {
            $this->viewMode = 'team';
        } elseif ($this->contest->categories->count() > 0) {
            $this->viewMode = 'category';
            $this->selectedCategoryId = $this->contest->categories->first()->id;
        }

        // If contest has steps, select the last one by default (final ranking)
        if ($this->contest->steps->count() > 0) {
            $this->selectedStepId = $this->contest->steps->last()->id;
        }
    }

    public function selectStep($stepId)
    {
        $this->selectedStepId = $stepId;
    }

    public function selectContest()
    {
        $this->selectedStepId = null;
    }

    public function selectCategory($categoryId)
    {
        $this->selectedCategoryId = $categoryId;
        $this->viewMode = 'category';
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
        if ($mode === 'category' && !$this->selectedCategoryId && $this->contest->categories->count() > 0) {
            $this->selectedCategoryId = $this->contest->categories->first()->id;
        }
    }

    public function joinTeam($teamId)
    {
        if (!auth()->check()) {
            $this->dispatch('action_error', title: 'Error', message: 'You must be logged in to join a team.');
            return;
        }

        $team = Team::findOrFail($teamId);
        
        // Check if user is already in another team for this contest
        $existingTeam = $this->contest->teams()
            ->whereHas('users', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->first();
        
        if ($existingTeam) {
            if ($existingTeam->id === $teamId) {
                $this->dispatch('action_error', title: 'Already in team', message: 'You are already a member of this team.');
            } else {
                $this->dispatch('action_error', title: 'Error', message: 'You are already in another team. Leave that team first.');
            }
            return;
        }
        
        $team->users()->attach(auth()->id());
        $this->dispatch('action_ok', title: 'Joined team', message: 'You have successfully joined the team!');
    }

    public function leaveTeam($teamId)
    {
        if (!auth()->check()) {
            return;
        }

        $team = Team::findOrFail($teamId);
        $team->users()->detach(auth()->id());
        $this->dispatch('action_ok', title: 'Left team', message: 'You have left the team.');
    }

    public function joinCategory($categoryId)
    {
        if (!auth()->check()) {
            $this->dispatch('action_error', title: 'Error', message: 'You must be logged in to join a category.');
            return;
        }

        $category = $this->contest->categories()->findOrFail($categoryId);
        $category->users()->syncWithoutDetaching([auth()->id()]);
        $this->dispatch('action_ok', title: 'Joined category', message: 'You have successfully joined the category!');
    }

    public function leaveCategory($categoryId)
    {
        if (!auth()->check()) {
            return;
        }

        $category = $this->contest->categories()->findOrFail($categoryId);
        $category->users()->detach(auth()->id());
        $this->dispatch('action_ok', title: 'Left category', message: 'You have left the category.');
    }

    #[Computed]
    public function rankings()
    {
        if ($this->viewMode === 'team') {
            return $this->contest->getTeamRankingForStep($this->selectedStepId);
        } elseif ($this->viewMode === 'category' && $this->selectedCategoryId) {
            return $this->contest->getCategoryRankings($this->selectedCategoryId, $this->selectedStepId);
        } else {
            return $this->contest->getRankingForStep($this->selectedStepId);
        }
    }

    #[Computed]
    public function userRanking()
    {
        if (!auth()->check() || $this->viewMode === 'team') {
            return null;
        }
        return $this->contest->getUserRankingForStep(auth()->id(), $this->selectedStepId);
    }

    #[Computed]
    public function totalRoutes()
    {
        if ($this->selectedStepId) {
            $step = $this->contest->steps()->find($this->selectedStepId);
            return $step && $step->routes->count() > 0 ? $step->routes->count() : $this->contest->routes->count();
        }
        return $this->contest->routes->count();
    }

    #[Computed]
    public function userTeam()
    {
        if (!auth()->check()) {
            return null;
        }
        return $this->contest->teams()->whereHas('users', function ($query) {
            $query->where('user_id', auth()->id());
        })->first();
    }
}; ?>

<div class=" space-y-8">

    <!-- Contest Info -->
    <section class="bg-white shadow sm:rounded-lg border border-gray-200">
        <div class="px-6 py-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $contest->name }}</h1>
            <p class="text-sm text-gray-700 mb-4">{{ $contest->description }}</p>
            <div class="flex flex-wrap gap-6 text-sm text-gray-600">
                <span>{{ __('Start') }}: <strong class="text-gray-900">{{ $contest->start_date->format('M d, Y H:i') }}</strong></span>
                <span>{{ __('End') }}: <strong class="text-gray-900">{{ $contest->end_date->format('M d, Y H:i') }}</strong></span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @if($contest->isActive()) bg-green-100 text-green-800
                    @elseif($contest->isFuture()) bg-blue-100 text-blue-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    @if($contest->isActive()) {{ __('Active') }}
                    @elseif($contest->isFuture()) {{ __('Upcoming') }}
                    @else {{ __('Ended') }}
                    @endif
                </span>
            </div>

            <!-- View Mode Selector -->
            @if($contest->team_mode || $contest->categories->count() > 0)
                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('View Rankings') }}</h2>
                    <div class="flex flex-wrap gap-2">
                        <button 
                            wire:click="setViewMode('individual')"
                            class="px-4 py-2 rounded-md text-sm font-medium transition-colors
                                @if($viewMode === 'individual') bg-indigo-600 text-white
                                @else bg-white text-gray-700 border border-gray-300 hover:bg-gray-50
                                @endif">
                            {{ __('Individual') }}
                        </button>
                        @if($contest->team_mode)
                            <button 
                                wire:click="setViewMode('team')"
                                class="px-4 py-2 rounded-md text-sm font-medium transition-colors
                                    @if($viewMode === 'team') bg-indigo-600 text-white
                                    @else bg-white text-gray-700 border border-gray-300 hover:bg-gray-50
                                    @endif">
                                {{ __('Team') }}
                            </button>
                        @endif
                        @if($contest->categories->count() > 0)
                            <button 
                                wire:click="setViewMode('category')"
                                class="px-4 py-2 rounded-md text-sm font-medium transition-colors
                                    @if($viewMode === 'category') bg-indigo-600 text-white
                                    @else bg-white text-gray-700 border border-gray-300 hover:bg-gray-50
                                    @endif">
                                {{ __('Categories') }}
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Category Selector -->
            @if($viewMode === 'category' && $contest->categories->count() > 0)
                <div class="mt-4">
                    <div class="flex flex-wrap gap-2">
                        @foreach($contest->categories as $category)
                            <button 
                                wire:click="selectCategory({{ $category->id }})"
                                class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors
                                    @if($selectedCategoryId === $category->id) bg-gray-800 text-white
                                    @else bg-white text-gray-700 border border-gray-300 hover:bg-gray-50
                                    @endif">
                                {{ $category->name }}
                                @auth
                                    @if($category->users->contains(auth()->user()))
                                        <span class="ml-1 text-xs">✓</span>
                                    @endif
                                @endauth
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($contest->steps->count() > 0)
                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('Contest Steps') }}</h2>
                    <div class="flex flex-wrap gap-2">
                        <button 
                            wire:click="selectContest"
                            class="px-4 py-2 rounded-md text-sm font-medium transition-colors
                                @if($selectedStepId === null) bg-gray-800 text-white
                                @else bg-white text-gray-700 border border-gray-300 hover:bg-gray-50
                                @endif">
                            {{ __('Overall') }}
                        </button>
                        @foreach($contest->steps as $step)
                            <button 
                                wire:click="selectStep({{ $step->id }})"
                                class="px-4 py-2 rounded-md text-sm font-medium transition-colors
                                    @if($selectedStepId === $step->id) bg-gray-800 text-white
                                    @else bg-white text-gray-700 border border-gray-300 hover:bg-gray-50
                                    @endif">
                                {{ $step->name }}
                                @if($step->isActive())
                                    <span class="ml-1 inline-block w-2 h-2 bg-green-400 rounded-full"></span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    <!-- Team Management Section (only in team mode) -->
    @if($contest->team_mode && $viewMode === 'team')
        <section class="bg-white shadow sm:rounded-lg border border-gray-200">
            <div class="px-6 py-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('Teams') }}</h2>
                
                @auth
                    @if($this->userTeam)
                        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="font-semibold text-blue-900">{{ __('Your Team') }}: {{ $this->userTeam->name }}</h3>
                                    <p class="text-sm text-blue-700 mt-1">{{ $this->userTeam->users->count() }} {{__('members')}}</p>
                                </div>
                                <button wire:click="leaveTeam({{ $this->userTeam->id }})" 
                                    wire:confirm="{{__('Are you sure you want to leave this team?')}}"
                                    class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700">
                                    {{ __('Leave Team') }}
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-yellow-800">{{ __('You are not in a team yet. Join a team below!') }}</p>
                        </div>
                    @endif
                @endauth

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($contest->teams as $team)
                        <div class="border rounded-lg p-4 @if($this->userTeam && $this->userTeam->id === $team->id) bg-blue-50 border-blue-300 @endif">
                            <h3 class="font-semibold text-gray-900">{{ $team->name }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ $team->users->count() }} {{__('members')}}</p>
                            @auth
                                @if(!$this->userTeam)
                                    <button wire:click="joinTeam({{ $team->id }})" 
                                        class="mt-2 w-full px-3 py-1.5 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                        {{ __('Join Team') }}
                                    </button>
                                @endif
                            @else
                                <p class="mt-2 text-xs text-gray-500">{{ __('Log in to join') }}</p>
                            @endauth
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Category Management Section (only in category mode) -->
    @if($viewMode === 'category' && $contest->categories->count() > 0)
        <section class="bg-white shadow sm:rounded-lg border border-gray-200">
            <div class="px-6 py-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('Join Categories') }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($contest->categories as $category)
                        <div class="border rounded-lg p-4 @if($category->users->contains(auth()->user())) bg-green-50 border-green-300 @endif">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $category->name }}</h3>
                                    @if($category->type)
                                        <span class="inline-block mt-1 px-2 py-0.5 text-xs bg-gray-100 text-gray-700 rounded">{{ ucfirst($category->type) }}</span>
                                    @endif
                                    @if($category->criteria)
                                        <p class="text-sm text-gray-600 mt-1">{{ $category->criteria }}</p>
                                    @endif
                                </div>
                                @auth
                                    @if($category->users->contains(auth()->user()))
                                        <button wire:click="leaveCategory({{ $category->id }})" 
                                            class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">
                                            {{ __('Leave') }}
                                        </button>
                                    @else
                                        <button wire:click="joinCategory({{ $category->id }})" 
                                            class="px-2 py-1 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                            {{ __('Join') }}
                                        </button>
                                    @endif
                                @endauth
                            </div>
                            <p class="text-xs text-gray-500 mt-2">{{ $category->users->count() }} {{__('participants')}}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- My Performance -->
    <section>
        @auth
            @if($viewMode !== 'team' && $this->userRanking)
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200 shadow mb-2">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('Your Performance') }}</h2>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-gray-700">{{ $this->userRanking['rank'] }}</div>
                            <div class="text-sm text-gray-900">{{ __('Rank') }}</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-700">{{ $this->userRanking['routes_count'] }}/{{ $this->totalRoutes }}</div>
                            <div class="text-sm text-gray-900">{{ __('Routes Climbed') }}</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-700">{{ number_format($this->userRanking['total_points'], 2) }}</div>
                            <div class="text-sm text-gray-900">{{ __('Total Points') }}</div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200 shadow text-center">
                    <p class="text-gray-700">{{ __('You haven\'t climbed any routes in this contest yet.') }}</p>
                </div>
            @endif
        @endauth
    </section>

    <!-- Rankings Table -->
    <section>
        <div class="bg-white shadow sm:rounded-lg border border-gray-200">
            <div class="px-6 py-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">
                    @if($selectedStepId)
                        {{ __('Rankings for') }} {{ $contest->steps->find($selectedStepId)->name }}
                    @else
                        @if($contest->steps->count() > 0)
                            {{ __('Final Rankings') }}
                        @else
                            {{ __('Rankings') }}
                        @endif
                    @endif
                </h2>
                
                @if($this->rankings->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Rank') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        @if($viewMode === 'team')
                                            {{ __('Team') }}
                                        @else
                                            {{ __('Climber') }}
                                        @endif
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Routes') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Points') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($this->rankings as $ranking)
                                    <tr class="@if($viewMode !== 'team' && auth()->check() && isset($ranking['user_id']) && $ranking['user_id'] === auth()->id()) bg-gray-50 @endif">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                @if($ranking['rank'] === 1)
                                                    <span class="-mx-3 inline-flex items-center justify-center h-8 w-8 rounded-full bg-amber-400 text-white font-bold text-lg">1</span>
                                                @elseif($ranking['rank'] === 2)
                                                    <span class="-mx-3 inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-400 text-white font-bold text-lg">2</span>
                                                @elseif($ranking['rank'] === 3)
                                                    <span class="-mx-3 inline-flex items-center justify-center h-8 w-8 rounded-full bg-orange-500 text-white font-bold text-lg">3</span>
                                                @else
                                                    <span class="text-sm font-medium text-gray-900">{{ $ranking['rank'] }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                @if($viewMode === 'team')
                                                    {{ $ranking['team']->name }}
                                                    <div class="text-xs text-gray-500">{{ $ranking['team']->users->count() }} members</div>
                                                @else
                                                    {{ $ranking['user']->name }}
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $ranking['routes_count'] }} / {{ $this->totalRoutes }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-gray-900">{{ number_format($ranking['total_points'], 2) }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        {{ __('No rankings available yet. Be the first to climb!') }}
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>