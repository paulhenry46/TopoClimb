<?php

use Livewire\Volt\Component;
use App\Models\Contest;
use App\Models\ContestStep;
use Livewire\Attributes\Computed;

new class extends Component {
    public Contest $contest;
    public $selectedStepId = null;

    public function mount()
    {
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

    #[Computed]
    public function rankings()
    {
        return $this->contest->getRankingForStep($this->selectedStepId);
    }

    #[Computed]
    public function userRanking()
    {
        if (!auth()->check()) {
            return null;
        }
        return $this->contest->getUserRankingForStep(auth()->id(), $this->selectedStepId);
    }

    #[Computed]
    public function totalRoutes()
    {
        return $this->contest->routes->count();
    }
}; ?>

<div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Contest Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ $contest->name }}</h1>
        <p class="mt-2 text-sm text-gray-700">{{ $contest->description }}</p>
        <div class="mt-2 flex gap-4 text-sm text-gray-600">
            <span>{{ __('Start') }}: {{ $contest->start_date->format('M d, Y H:i') }}</span>
            <span>{{ __('End') }}: {{ $contest->end_date->format('M d, Y H:i') }}</span>
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
    </div>

    <!-- User Stats (if logged in) -->
    @auth
        @if($this->userRanking)
            <div class="mb-6 bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                <h2 class="text-lg font-semibold text-indigo-900 mb-2">{{ __('Your Performance') }}</h2>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-indigo-600">{{ $this->userRanking['rank'] }}</div>
                        <div class="text-sm text-indigo-700">{{ __('Rank') }}</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-indigo-600">{{ $this->userRanking['routes_count'] }}/{{ $this->totalRoutes }}</div>
                        <div class="text-sm text-indigo-700">{{ __('Routes Climbed') }}</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-indigo-600">{{ number_format($this->userRanking['total_points'], 2) }}</div>
                        <div class="text-sm text-indigo-700">{{ __('Total Points') }}</div>
                    </div>
                </div>
            </div>
        @else
            <div class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200 text-center">
                <p class="text-gray-600">{{ __('You haven\'t climbed any routes in this contest yet.') }}</p>
            </div>
        @endif
    @endauth

    <!-- Step Selection (if contest has multiple steps) -->
    @if($contest->steps->count() > 0)
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('Contest Steps') }}</h2>
            <div class="flex flex-wrap gap-2">
                <button 
                    wire:click="selectContest"
                    class="px-4 py-2 rounded-md text-sm font-medium transition-colors
                        @if($selectedStepId === null) bg-indigo-600 text-white
                        @else bg-white text-gray-700 border border-gray-300 hover:bg-gray-50
                        @endif">
                    {{ __('Overall') }}
                </button>
                @foreach($contest->steps as $step)
                    <button 
                        wire:click="selectStep({{ $step->id }})"
                        class="px-4 py-2 rounded-md text-sm font-medium transition-colors
                            @if($selectedStepId === $step->id) bg-indigo-600 text-white
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

    <!-- Rankings Table -->
    <div class="bg-white shadow sm:rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Climber') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Routes') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Points') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($this->rankings as $ranking)
                                <tr class="@if(auth()->check() && $ranking['user_id'] === auth()->id()) bg-indigo-50 @endif">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($ranking['rank'] === 1)
                                                <span class="text-2xl">🥇</span>
                                            @elseif($ranking['rank'] === 2)
                                                <span class="text-2xl">🥈</span>
                                            @elseif($ranking['rank'] === 3)
                                                <span class="text-2xl">🥉</span>
                                            @else
                                                <span class="text-sm font-medium text-gray-900">{{ $ranking['rank'] }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $ranking['user']->name }}</div>
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
</div>
