<?php

use Livewire\Volt\Component;
use App\Models\Contest;
use App\Models\Site;
use App\Models\Route;
use App\Models\Log;
use Livewire\Attributes\Computed;

new class extends Component {
    public Site $site;
    public $selectedContest = null;

    public function selectContest($contestId)
    {
        $this->selectedContest = Contest::findOrFail($contestId);
    }

    public function clearSelection()
    {
        $this->selectedContest = null;
    }

    #[Computed]
    public function activeContests()
    {
        return Contest::where('site_id', $this->site->id)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->get();
    }

    #[Computed]
    public function upcomingContests()
    {
        return Contest::where('site_id', $this->site->id)
            ->where('start_date', '>', now())
            ->orderBy('start_date', 'asc')
            ->get();
    }

    #[Computed]
    public function pastContests()
    {
        return Contest::where('site_id', $this->site->id)
            ->where('end_date', '<', now())
            ->orderBy('end_date', 'desc')
            ->limit(5)
            ->get();
    }

    public function getUserProgress($contestId)
    {
        if (!auth()->check()) {
            return ['completed' => 0, 'total' => 0];
        }

        $contest = Contest::find($contestId);
        if (!$contest) {
            return ['completed' => 0, 'total' => 0];
        }

        $totalRoutes = $contest->routes()->count();
        
        if ($contest->mode === 'free') {
            // In free mode, check all logs (verified or not)
            $completedRoutes = $contest->routes()
                ->whereHas('logs', function($query) use ($contest) {
                    $query->where('user_id', auth()->id())
                        ->whereBetween('created_at', [$contest->start_date, $contest->end_date]);
                })
                ->count();
        } else {
            // In official mode, check only verified logs
            $completedRoutes = $contest->routes()
                ->whereHas('logs', function($query) use ($contest) {
                    $query->where('user_id', auth()->id())
                        ->whereNotNull('verified_by')
                        ->whereBetween('created_at', [$contest->start_date, $contest->end_date]);
                })
                ->count();
        }

        return ['completed' => $completedRoutes, 'total' => $totalRoutes];
    }
}; ?>

<div class="bg-white rounded-md mt-3 py-2">
    <div class="py-5 ml-5 font-semibold text-xl text-gray-700">{{ __('Contests') }}</div>
    
    @if($this->activeContests->count() > 0 || $this->upcomingContests->count() > 0 || $this->pastContests->count() > 0)
        <div class="px-5">
            <!-- Active Contests -->
            @if($this->activeContests->count() > 0)
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">{{ __('Active Contests') }}</h3>
                    <div class="space-y-3">
                        @foreach($this->activeContests as $contest)
                            <div class="border-2 border-green-500 rounded-md p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-base font-semibold text-gray-900">{{ $contest->name }}</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ __('Active') }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mb-3">
                                    {{ __('Ends') }}: {{ $contest->end_date->format('M d, Y H:i') }}
                                </p>
                                <div class="flex gap-2">
                                    <a href="{{ route('contest.public', ['site' => $site->slug, 'contest' => $contest->id]) }}" 
                                        class="flex-1 text-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors">
                                        {{ __('View Rankings') }}
                                    </a>
                                    @if($contest->isActive())
                                        <a href="{{ route('contest.live', ['site' => $site->slug, 'contest' => $contest->id]) }}" 
                                            class="flex-1 text-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors">
                                            {{ __('Live') }} 🔴
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Upcoming Contests -->
            @if($this->upcomingContests->count() > 0)
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">{{ __('Upcoming Contests') }}</h3>
                    <div class="space-y-3">
                        @foreach($this->upcomingContests as $contest)
                            <div class="border-2 border-yellow-400 rounded-md p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-base font-semibold text-gray-900">{{ $contest->name }}</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ __('Upcoming') }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mb-3">
                                    {{ __('Starts') }}: {{ $contest->start_date->format('M d, Y H:i') }}
                                </p>
                                <a href="{{ route('contest.public', ['site' => $site->slug, 'contest' => $contest->id]) }}" 
                                    class="block text-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors">
                                    {{ __('View Details') }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Past Contests -->
            @if($this->pastContests->count() > 0)
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">{{ __('Past Contests') }}</h3>
                    <div class="space-y-3">
                        @foreach($this->pastContests as $contest)
                            <div class="border border-gray-300 rounded-md p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-base font-semibold text-gray-900">{{ $contest->name }}</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ __('Ended') }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mb-3">
                                    {{ __('Ended') }}: {{ $contest->end_date->format('M d, Y') }}
                                </p>
                                <a href="{{ route('contest.public', ['site' => $site->slug, 'contest' => $contest->id]) }}" 
                                    class="block text-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition-colors">
                                    {{ __('View Results') }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="px-5">
            <div class="w-full border-gray-900 rounded-md border-2 mb-4">
                <div class="ml-4 mt-4 mb-4">
                    {{ __('No contests available for this site.') }}
                </div>
            </div>
        </div>
    @endif
</div>
