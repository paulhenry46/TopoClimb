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
                            <div class="w-full border-gray-900 rounded-md border-2 mb-4 flex justify-between">
    <div class="ml-4 mt-4 mb-4 flex items-center gap-x-3">
        {{ $contest->name }}
        @if($contest->mode == 'free')
            <span class="mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-pink-100 px-2 py-1 text-xs font-medium text-pink-700">
                <svg class="h-1.5 w-1.5 fill-pink-500" viewBox="0 0 6 6" aria-hidden="true">
                    <circle cx="3" cy="3" r="3"></circle>
                </svg>
                {{ __('Free Climb') }}
            </span>
        @elseif($contest->mode == 'official')
            <span class="mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700">
                <svg class="h-1.5 w-1.5 fill-blue-500" viewBox="0 0 6 6" aria-hidden="true">
                    <circle cx="3" cy="3" r="3"></circle>
                </svg>
                {{ __('Official') }}
            </span>
        @endif
        
    </div>
    <div class="flex items-center">
        <div class="text-sm text-gray-500 mr-4">
            @if($contest->isActive())
                {{ __('Ends') }}: {{ $contest->end_date->format('M d, Y H:i') }}
            @elseif($contest->start_date > now())
                {{ __('Starts') }}: {{ $contest->start_date->format('M d, Y H:i') }}
            @else
                {{ __('Ended') }}: {{ $contest->end_date->format('M d, Y') }}
            @endif
        </div>
        <a wire:navigate href="{{ route('contest.public', ['site' => $site->slug, 'contest' => $contest->id]) }}" class="cursor-pointer w-32 bg-gray-900 text-white hover:bg-gray-700">
            <p class="flex ml-4 mt-4 mb-4 font-semibold items-center justify-center">
                {{ __('See') }}
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3" class="ml-2">
                    <path d="M504-480 320-664l56-56 240 240-240 240-56-56 184-184Z"/>
                </svg>
            </p>
        </a>
        
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
                            <div class="w-full border-gray-900 rounded-md border-2 mb-4 flex justify-between">
    <div class="ml-4 mt-4 mb-4 flex items-center gap-x-3">
        {{ $contest->name }}
        @if($contest->mode == 'free')
            <span class="mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-pink-100 px-2 py-1 text-xs font-medium text-pink-700">
                <svg class="h-1.5 w-1.5 fill-pink-500" viewBox="0 0 6 6" aria-hidden="true">
                    <circle cx="3" cy="3" r="3"></circle>
                </svg>
                {{ __('Free Climb') }}
            </span>
        @elseif($contest->mode == 'official')
            <span class="mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700">
                <svg class="h-1.5 w-1.5 fill-blue-500" viewBox="0 0 6 6" aria-hidden="true">
                    <circle cx="3" cy="3" r="3"></circle>
                </svg>
                {{ __('Official') }}
            </span>
        @endif
        
    </div>
    <div class="flex items-center">
        <div class="text-sm text-gray-500 mr-4">
            @if($contest->isActive())
                {{ __('Ends') }}: {{ $contest->end_date->format('M d, Y H:i') }}
            @elseif($contest->start_date > now())
                {{ __('Starts') }}: {{ $contest->start_date->format('M d, Y H:i') }}
            @else
                {{ __('Ended') }}: {{ $contest->end_date->format('M d, Y') }}
            @endif
        </div>
        <a wire:navigate href="{{ route('contest.public', ['site' => $site->slug, 'contest' => $contest->id]) }}" class="cursor-pointer w-32 bg-gray-900 text-white hover:bg-gray-700">
            <p class="flex ml-4 mt-4 mb-4 font-semibold items-center justify-center">
                {{ __('See') }}
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3" class="ml-2">
                    <path d="M504-480 320-664l56-56 240 240-240 240-56-56 184-184Z"/>
                </svg>
            </p>
        </a>
        
    </div>
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
                          <div class="w-full border-gray-900 rounded-md border-2 mb-4 flex justify-between">
    <div class="ml-4 mt-4 mb-4 flex items-center gap-x-3">
        {{ $contest->name }}
        @if($contest->mode == 'free')
            <span class="mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                <svg class="h-1.5 w-1.5 fill-gray-500" viewBox="0 0 6 6" aria-hidden="true">
                    <circle cx="3" cy="3" r="3"></circle>
                </svg>
                {{ __('Free Climb') }}
            </span>
        @elseif($contest->mode == 'official')
            <span class="mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                <svg class="h-1.5 w-1.5 fill-gray-500" viewBox="0 0 6 6" aria-hidden="true">
                    <circle cx="3" cy="3" r="3"></circle>
                </svg>
                {{ __('Official') }}
            </span>
        @endif
        
    </div>
    <div class="flex items-center">
        <div class="text-sm text-gray-500 mr-4">
            @if($contest->isActive())
                {{ __('Ends') }}: {{ $contest->end_date->format('M d, Y H:i') }}
            @elseif($contest->start_date > now())
                {{ __('Starts') }}: {{ $contest->start_date->format('M d, Y H:i') }}
            @else
                {{ __('Ended') }}: {{ $contest->end_date->format('M d, Y') }}
            @endif
        </div>
        <a wire:navigate href="{{ route('contest.public', ['site' => $site->slug, 'contest' => $contest->id]) }}" class="cursor-pointer w-32 bg-gray-900 text-white hover:bg-gray-700">
            <p class="flex ml-4 mt-4 mb-4 font-semibold items-center justify-center">
                {{ __('See') }}
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3" class="ml-2">
                    <path d="M504-480 320-664l56-56 240 240-240 240-56-56 184-184Z"/>
                </svg>
            </p>
        </a>
        
    </div>
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
