<?php

use Livewire\Volt\Component;
use App\Models\UserStats;

new class extends Component {
    
    public $userStats;
    public $lastCalculated;
    
    public function mount()
    {
        $this->userStats = auth()->user()->stats;
        $this->lastCalculated = $this->userStats?->last_calculated_at?->diffForHumans();
    }
    
    public function with(): array
    {
        return [
            'hasStats' => $this->userStats !== null,
        ];
    }
}; ?>

<div class="space-y-6">
    @if(!$hasStats)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        {{ __('Statistics not yet calculated. They will be available after the nightly update at 2 AM.') }}
                    </p>
                </div>
            </div>
        </div>
    @else
        <!-- Last Updated -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Last Updated') }}</h3>
                    <span class="text-sm text-gray-500">{{ $lastCalculated }}</span>
                </div>
            </div>
        </div>

        <!-- Technical Analysis -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __('Technical Analysis') }}</h3>
                <p class="text-sm text-gray-600 mb-4">{{ __('Metrics that reveal how you climb, not just what you succeed.') }}</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Consistency Variance -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900">{{ __('Consistency') }}</h4>
                        <p class="text-2xl font-bold text-indigo-600">
                            {{ $userStats->consistency_variance !== null ? number_format($userStats->consistency_variance, 2) : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Variance in difficulty levels. Lower = more consistent.') }}</p>
                    </div>

                    <!-- Flash/Work Ratio -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900">{{ __('Flash/Work Ratio') }}</h4>
                        <p class="text-2xl font-bold text-indigo-600">
                            {{ $userStats->flash_work_ratio !== null ? number_format($userStats->flash_work_ratio, 2) : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Higher = explosive style, Lower = methodical approach.') }}</p>
                    </div>

                    <!-- Abandonment Rate -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900">{{ __('Abandonment Rate') }}</h4>
                        <p class="text-2xl font-bold text-indigo-600">
                            {{ $userStats->risk_profile_abandonment_rate !== null ? number_format($userStats->risk_profile_abandonment_rate, 1) . '%' : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Percentage of attempted routes never completed.') }}</p>
                    </div>

                    <!-- Endurance vs Power -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900">{{ __('Endurance vs Power') }}</h4>
                        <p class="text-2xl font-bold text-indigo-600">
                            {{ $userStats->long_routes_count ?? 0 }} / {{ $userStats->short_routes_count ?? 0 }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Long routes / Short routes completed.') }}</p>
                    </div>

                    <!-- Movement Preferences -->
                    @if($userStats->movement_preferences)
                    <div class="border rounded-lg p-4 md:col-span-2">
                        <h4 class="font-semibold text-gray-900 mb-2">{{ __('Movement Preferences') }}</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach(array_slice($userStats->movement_preferences, 0, 5, true) as $tag => $count)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                                    {{ $tag }}: {{ $count }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Behavioral Analysis -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __('Behavioral Analysis') }}</h3>
                <p class="text-sm text-gray-600 mb-4">{{ __('What you choose to climb reveals your style.') }}</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Preferred Hour -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900">{{ __('Preferred Climbing Hour') }}</h4>
                        <p class="text-2xl font-bold text-emerald-600">
                            {{ $userStats->preferred_climbing_hour ?? 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Most common time of day for climbing.') }}</p>
                    </div>

                    <!-- Session Duration -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900">{{ __('Avg Session Duration') }}</h4>
                        <p class="text-2xl font-bold text-emerald-600">
                            {{ $userStats->avg_session_duration !== null ? number_format($userStats->avg_session_duration, 1) . 'h' : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Average duration of climbing sessions.') }}</p>
                    </div>

                    <!-- Routes Per Session -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900">{{ __('Routes Per Session') }}</h4>
                        <p class="text-2xl font-bold text-emerald-600">
                            {{ $userStats->avg_routes_per_session !== null ? number_format($userStats->avg_routes_per_session, 1) : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Average number of routes per session.') }}</p>
                    </div>

                    <!-- Exploration Ratio -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900">{{ __('Exploration Ratio') }}</h4>
                        <p class="text-2xl font-bold text-emerald-600">
                            {{ $userStats->exploration_ratio !== null ? number_format($userStats->exploration_ratio, 1) . '%' : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Percentage climbing new vs repeated routes.') }}</p>
                    </div>

                    <!-- Project Count -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900">{{ __('Projects') }}</h4>
                        <p class="text-2xl font-bold text-emerald-600">
                            {{ $userStats->project_count ?? 0 }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Routes worked across multiple sessions.') }}</p>
                    </div>

                    <!-- Attempts Before Success -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900">{{ __('Avg Attempts to Send') }}</h4>
                        <p class="text-2xl font-bold text-emerald-600">
                            {{ $userStats->avg_attempts_before_success !== null ? number_format($userStats->avg_attempts_before_success, 1) : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Average attempts before success.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progression Analysis -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __('Progression Analysis') }}</h3>
                <p class="text-sm text-gray-600 mb-4">{{ __('Metrics showing your actual improvement.') }}</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Progression Rate -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900">{{ __('Progression Rate') }}</h4>
                        <p class="text-2xl font-bold {{ $userStats->progression_rate && $userStats->progression_rate > 0 ? 'text-green-600' : 'text-gray-600' }}">
                            {{ $userStats->progression_rate !== null ? ($userStats->progression_rate > 0 ? '+' : '') . number_format($userStats->progression_rate, 1) . ' pts/month' : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Grade progression per month.') }}</p>
                    </div>

                    <!-- Plateau Detection -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900">{{ __('Plateau Status') }}</h4>
                        <p class="text-2xl font-bold {{ $userStats->plateau_detected ? 'text-orange-600' : 'text-green-600' }}">
                            {{ $userStats->plateau_detected ? __('Detected') : __('Progressing') }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $userStats->plateau_detected ? __('Stagnation for ' . $userStats->plateau_weeks . ' weeks') : __('No plateau detected') }}
                        </p>
                    </div>

                    <!-- Progression by Style -->
                    @if($userStats->progression_by_style)
                    <div class="border rounded-lg p-4 md:col-span-2">
                        <h4 class="font-semibold text-gray-900 mb-2">{{ __('Progression by Style') }}</h4>
                        <div class="space-y-2">
                            @foreach($userStats->progression_by_style as $style => $rate)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm capitalize">{{ $style }}</span>
                                    <span class="font-medium {{ $rate > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $rate > 0 ? '+' : '' }}{{ number_format($rate, 1) }} pts/month
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Training Load Analysis -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __('Training Load Analysis') }}</h3>
                <p class="text-sm text-gray-600 mb-4">{{ __('Optimize progression and prevent injuries.') }}</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Weekly Volume -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900">{{ __('Weekly Volume') }}</h4>
                        <p class="text-2xl font-bold text-purple-600">
                            {{ $userStats->weekly_volume !== null ? number_format($userStats->weekly_volume) : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Total climbing load last 7 days.') }}</p>
                    </div>

                    <!-- Weekly Intensity -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900">{{ __('Weekly Intensity') }}</h4>
                        <p class="text-2xl font-bold text-purple-600">
                            {{ $userStats->weekly_intensity !== null ? number_format($userStats->weekly_intensity) : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Average difficulty last 7 days.') }}</p>
                    </div>

                    <!-- Acute/Chronic Ratio -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900">{{ __('Acute/Chronic Ratio') }}</h4>
                        <p class="text-2xl font-bold {{ $userStats->acute_chronic_ratio && $userStats->acute_chronic_ratio > 1.5 ? 'text-red-600' : ($userStats->acute_chronic_ratio && $userStats->acute_chronic_ratio < 0.8 ? 'text-orange-600' : 'text-green-600') }}">
                            {{ $userStats->acute_chronic_ratio !== null ? number_format($userStats->acute_chronic_ratio, 2) : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Sweet spot: 0.8-1.3, >1.5 = injury risk.') }}</p>
                    </div>

                    <!-- Overtraining Alert -->
                    <div class="border rounded-lg p-4 {{ $userStats->overtraining_detected ? 'bg-red-50 border-red-200' : '' }}">
                        <h4 class="font-semibold text-gray-900">{{ __('Overtraining Alert') }}</h4>
                        <p class="text-2xl font-bold {{ $userStats->overtraining_detected ? 'text-red-600' : 'text-green-600' }}">
                            {{ $userStats->overtraining_detected ? __('WARNING') : __('OK') }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $userStats->overtraining_detected ? __('Take rest days!') : __('Training load manageable.') }}
                        </p>
                    </div>

                    <!-- Recovery Time -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900">{{ __('Avg Recovery Time') }}</h4>
                        <p class="text-2xl font-bold text-purple-600">
                            {{ $userStats->avg_recovery_time !== null ? number_format($userStats->avg_recovery_time, 1) . 'h' : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Time between sessions.') }}</p>
                    </div>

                    <!-- Time Between Performances -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900">{{ __('Performance Frequency') }}</h4>
                        <p class="text-2xl font-bold text-purple-600">
                            {{ $userStats->avg_time_between_performances !== null ? number_format($userStats->avg_time_between_performances / 24, 1) . ' days' : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Time between peak performances.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help Section -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        {{ __('For detailed explanations of how each statistic is calculated, see the') }}
                        <a href="https://github.com/paulhenry46/TopoClimb/blob/copilot/add-climber-statistics-table/STATS_CALCULATION_DOCUMENTATION.md" target="_blank" class="font-medium underline">
                            {{ __('Statistics Calculation Documentation') }}
                        </a>
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
