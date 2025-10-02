<?php

use Livewire\Volt\Component;
use App\Models\Contest;
use App\Models\Site;
use App\Models\Route;
use App\Models\ContestRegistration;
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
            // In free mode, check logs
            $completedRoutes = $contest->routes()
                ->whereHas('logs', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->count();
        } else {
            // In official mode, check registrations
            $completedRoutes = ContestRegistration::where('contest_id', $contestId)
                ->where('user_id', auth()->id())
                ->distinct('route_id')
                ->count();
        }

        return ['completed' => $completedRoutes, 'total' => $totalRoutes];
    }
}; ?>

<div class="bg-white rounded-md mt-3 py-2 h-full">
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
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900">{{ $contest->name }}</h4>
                                        @if($contest->description)
                                            <p class="text-sm text-gray-600 mt-1">{{ $contest->description }}</p>
                                        @endif
                                        <div class="mt-2 text-xs text-gray-500">
                                            {{ __('Ends') }}: {{ $contest->end_date->format('Y-m-d H:i') }}
                                        </div>
                                        <div class="mt-1">
                                            @if($contest->mode === 'free')
                                                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">{{__('Free Climb')}}</span>
                                            @else
                                                <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">{{__('Official')}}</span>
                                            @endif
                                        </div>
                                        @auth
                                            @php
                                                $progress = $this->getUserProgress($contest->id);
                                            @endphp
                                            @if($progress['total'] > 0)
                                                <div class="mt-2">
                                                    <div class="flex items-center">
                                                        <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ ($progress['completed'] / $progress['total']) * 100 }}%"></div>
                                                        </div>
                                                        <span class="text-xs text-gray-600">{{ $progress['completed'] }}/{{ $progress['total'] }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                        @endauth
                                    </div>
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
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900">{{ $contest->name }}</h4>
                                        @if($contest->description)
                                            <p class="text-sm text-gray-600 mt-1">{{ $contest->description }}</p>
                                        @endif
                                        <div class="mt-2 text-xs text-gray-500">
                                            {{ __('Starts') }}: {{ $contest->start_date->format('Y-m-d H:i') }}
                                        </div>
                                        <div class="mt-1">
                                            @if($contest->mode === 'free')
                                                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">{{__('Free Climb')}}</span>
                                            @else
                                                <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">{{__('Official')}}</span>
                                            @endif
                                        </div>
                                    </div>
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
                            <div class="border border-gray-300 rounded-md p-4">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900">{{ $contest->name }}</h4>
                                        @if($contest->description)
                                            <p class="text-sm text-gray-600 mt-1">{{ $contest->description }}</p>
                                        @endif
                                        <div class="mt-2 text-xs text-gray-500">
                                            {{ __('Ended') }}: {{ $contest->end_date->format('Y-m-d H:i') }}
                                        </div>
                                        @auth
                                            @php
                                                $progress = $this->getUserProgress($contest->id);
                                            @endphp
                                            @if($progress['total'] > 0)
                                                <div class="mt-2">
                                                    <div class="flex items-center">
                                                        <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                                            <div class="bg-gray-600 h-2 rounded-full" style="width: {{ ($progress['completed'] / $progress['total']) * 100 }}%"></div>
                                                        </div>
                                                        <span class="text-xs text-gray-600">{{ $progress['completed'] }}/{{ $progress['total'] }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                        @endauth
                                    </div>
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
