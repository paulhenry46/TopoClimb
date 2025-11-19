<?php

use Livewire\Volt\Component;
use App\Models\Contest;
use App\Models\ContestStep;
use Livewire\Attributes\Computed;

new class extends Component {
    public Contest $contest;
    public ContestStep $step;
    public $selectedStepId = null;

    public function mount()
    {
        // If contest has steps beyond the main step, select the last one by default
        $additionalSteps = $this->contest->steps->where('order', '>', 0);
        if ($additionalSteps->count() > 0) {
            $this->selectedStepId = $additionalSteps->last()->id;
            $this->step = $additionalSteps->last();
        }else{
            $this->selectedStepId = $this->contest->steps->first()->id;
            $this->step = $this->contest->steps->first();
        }
    }

    public function selectStep($stepId)
    {
        $this->selectedStepId = $stepId;
        $this->step = ContestStep::findOrFail($stepId);
    }

    public function selectContest()
    {
        $this->selectedStepId = null;
    }

    #[Computed]
    public function rankings()
    {
        if ($this->contest->team_mode) {
            return $this->contest->getTeamRankingForStep($this->selectedStepId);
        }
        return $this->contest->getRankingForStep($this->selectedStepId);
    }

    #[Computed]
    public function totalRoutes()
    {
        if($this->selectedStepId !== null){
            return $this->step->routes->count();
        }else{
            
            return  $this->contest->steps()->with('routes')->get()
                ->flatMap(function ($s) {
                    return $s->routes->pluck('id');
                })->unique()->values()->count();
        }
    }
}; ?>

<div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white p-8" wire:poll.30s>
    <!-- Contest Header -->
    <div class="text-center mb-12">
        <h1 class="text-6xl font-bold mb-4 text-white bg-clip-text text-transparent">
            {{ $contest->name }}
        </h1>
        <p class="text-2xl text-gray-300">{{ __('Live Rankings') }}</p>
        <div class="mt-4 flex justify-center gap-8 text-lg text-gray-400">
            <span>{{ __('Total Routes') }}: <strong class="text-white">{{ $this->totalRoutes }}</strong></span>
            <span>{{ __('Participants') }}: <strong class="text-white">{{ $this->rankings->count() }}</strong></span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                @if($contest->isActive()) bg-green-500/20 text-green-300 border border-green-500/30
                @elseif($contest->isFuture()) bg-blue-500/20 text-blue-300 border border-blue-500/30
                @else bg-gray-500/20 text-gray-300 border border-gray-500/30
                @endif">
                @if($contest->isActive()) 
                    <span class="relative flex h-3 w-3 mr-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                    {{ __('LIVE') }}
                @elseif($contest->isFuture()) {{ __('Upcoming') }}
                @else {{ __('Ended') }}
                @endif
            </span>
        </div>
    </div>

    <!-- Step Selection (if contest has multiple steps beyond Main) -->
    @if($contest->steps->where('order', '>', 0)->count() > 0)
        <div class="max-w-4xl mx-auto mb-8">
            <div class="flex flex-wrap gap-3 justify-center">
                <button 
                    wire:click="selectContest"
                    class="px-6 py-3 rounded-lg text-lg font-medium
                        @if($selectedStepId === null) bg-gray-600 
                        @else bg-gray-700/50 border border-gray-600 hover:bg-gray-700
                        @endif">
                    {{ __('Overall') }}
                </button>
                @foreach($contest->steps->where('order', '>=', 0) as $step)
                    <button 
                        wire:click="selectStep({{ $step->id }})"
                        class="px-6 py-3 rounded-lg text-lg font-medium
                            @if($selectedStepId === $step->id) bg-gray-600 
                            @else bg-gray-700/50 border border-gray-600 hover:bg-gray-700
                            @endif">
                        {{ $step->name }}
                        
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Rankings Display -->
    <div class="max-w-7xl mx-auto">
        @if($this->rankings->count() > 0)
            <!-- Top 3 Podium -->
            @if($this->rankings->count() >= 3)
                <div class="grid grid-cols-3 gap-8 mb-12 max-w-4xl mx-auto pt-5">
                    <!-- 2nd Place -->
                    <div class="flex flex-col items-center justify-end">
                        <div class="bg-gray-700/50 backdrop-blur-sm rounded-lg p-6 w-full border-2 border-gray-500 text-center">
                            <div class="text-3xl font-bold text-gray-300 mb-1">2nd</div>
                            <div class="text-xl font-semibold mb-2">
                                @if($contest->team_mode)
                                    {{ $this->rankings[1]['team']->name }}
                                @else
                                    {{ $this->rankings[1]['user']->name }}
                                @endif
                            </div>
                            <div class="text-2xl font-bold text-purple-400">{{ number_format($this->rankings[1]['total_points'], 0) }}</div>
                            <div class="text-sm text-gray-400">{{ $this->rankings[1]['routes_count'] }} routes</div>
                        </div>
                    </div>

                    <!-- 1st Place -->
                    <div class="flex flex-col items-center justify-end -mt-8">
                        <div class="bg-gradient-to-br from-yellow-500/20 to-orange-500/20 backdrop-blur-sm rounded-lg p-8 w-full border-2 border-yellow-500 shadow-2xl text-center">
                            <div class="text-4xl font-bold text-yellow-400 mb-1">1st</div>
                            <div class="text-2xl font-bold mb-3">
                                @if($contest->team_mode)
                                    {{ $this->rankings[0]['team']->name }}
                                @else
                                    {{ $this->rankings[0]['user']->name }}
                                @endif
                            </div>
                            <div class="text-4xl font-bold text-yellow-400">{{ number_format($this->rankings[0]['total_points'], 0) }}</div>
                            <div class="text-sm text-gray-300">{{ $this->rankings[0]['routes_count'] }} routes</div>
                        </div>
                    </div>

                    <!-- 3rd Place -->
                    <div class="flex flex-col items-center justify-end">
                        <div class="bg-gray-700/50 backdrop-blur-sm rounded-lg p-6 w-full border-2 border-orange-700 text-center">
                            <div class="text-3xl font-bold text-gray-300 mb-1">3rd</div>
                            <div class="text-xl font-semibold mb-2">
                                @if($contest->team_mode)
                                    {{ $this->rankings[2]['team']->name }}
                                @else
                                    {{ $this->rankings[2]['user']->name }}
                                @endif
                            </div>
                            <div class="text-2xl font-bold text-orange-400">{{ number_format($this->rankings[2]['total_points'], 0) }}</div>
                            <div class="text-sm text-gray-400">{{ $this->rankings[2]['routes_count'] }} routes</div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Rest of Rankings -->
            @if($this->rankings->count() > 3)
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-lg border border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-900/50 border-b border-gray-700">
                        <h2 class="text-2xl font-bold">{{ __('Full Leaderboard') }}</h2>
                    </div>
                    <div class="divide-y divide-gray-700">
                        @foreach($this->rankings->skip(3) as $ranking)
                            <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-700/30 transition-colors">
                                <div class="flex items-center gap-6 flex-1">
                                    <div class="text-2xl font-bold text-gray-400 w-12">{{ $ranking['rank'] }}</div>
                                    <div class="flex-1">
                                        <div class="text-xl font-semibold">
                                            @if($contest->team_mode)
                                                {{ $ranking['team']->name }}
                                            @else
                                                {{ $ranking['user']->name }}
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-400">{{ $ranking['routes_count'] }} / {{ $this->totalRoutes }} routes</div>
                                    </div>
                                </div>
                                <div class="text-3xl font-bold text-white">{{ number_format($ranking['total_points'], 0) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            <div class="text-center py-20">
                <div class="text-6xl mb-4">🏔️</div>
                <p class="text-2xl text-gray-400">{{ __('No rankings yet. Be the first to climb!') }}</p>
            </div>
        @endif
    </div>

    <!-- Auto-refresh indicator -->
    <div class="fixed bottom-4 right-4 bg-gray-800/90 backdrop-blur-sm px-4 py-2 rounded-full border border-gray-600 flex items-center gap-2">
        <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
        </span>
        <span class="text-sm text-gray-300">{{ __('Auto-refresh: 30s') }}</span>
    </div>
</div>
