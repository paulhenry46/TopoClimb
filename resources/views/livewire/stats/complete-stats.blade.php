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
    
    // Helper methods for UI logic
    public function getConsistencyStatus()
    {
        if (!$this->userStats || $this->userStats->consistency_variance === null) return null;
        $variance = $this->userStats->consistency_variance;
        if ($variance < 100) return 'excellent';
        if ($variance < 300) return 'good';
        return 'needs-improvement';
    }
    
    public function getFlashWorkRatioStatus()
    {
        if (!$this->userStats || $this->userStats->flash_work_ratio === null) return null;
        $ratio = $this->userStats->flash_work_ratio;
        if ($ratio > 1.5) return 'explosive';
        if ($ratio > 0.5) return 'balanced';
        return 'methodical';
    }
}; ?>

<div class="space-y-8">
    @if(!$hasStats)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">{{ __('Statistics Not Available') }}</h3>
                    <p class="text-sm text-yellow-700 mt-1">
                        {{ __('Statistics will be calculated after the nightly update at 2 AM. Start logging your climbs to see your progress!') }}
                    </p>
                </div>
            </div>
        </div>
    @else
        <!-- Header with Last Updated -->
        <div class="bg-white border border-gray-200 shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold flex items-center">
                            
                                                            <svg class="w-8 h-8 mr-3" xmlns="http://www.w3.org/2000/svg"  viewBox="0 -960 960 960" fill="currentColor"><path d="M680-160q-17 0-28.5-11.5T640-200v-200q0-17 11.5-28.5T680-440h80q17 0 28.5 11.5T800-400v200q0 17-11.5 28.5T760-160h-80Zm-240 0q-17 0-28.5-11.5T400-200v-560q0-17 11.5-28.5T440-800h80q17 0 28.5 11.5T560-760v560q0 17-11.5 28.5T520-160h-80Zm-240 0q-17 0-28.5-11.5T160-200v-360q0-17 11.5-28.5T200-600h80q17 0 28.5 11.5T320-560v360q0 17-11.5 28.5T280-160h-80Z"/></svg>

                            {{ __('Your Climbing Statistics') }}
                        </h2>
                        <p class="text-gray-500 mt-1">{{ __('Updated') }} {{ $lastCalculated }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Critical Alerts -->
        @if($userStats->overtraining_detected || $userStats->plateau_detected)
        <div class="bg-white border border-red-200 shadow-sm sm:rounded-lg border-l-4 border-red-500">
            <div class="p-6">
                <h3 class="text-lg font-bold text-red-600 flex items-center mb-4">
                    <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    {{ __('Important Alerts') }}
                </h3>
                <div class="space-y-3">
                    @if($userStats->overtraining_detected)
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <h4 class="font-semibold text-red-800">{{ __('Overtraining Risk Detected') }}</h4>
                                <p class="text-sm text-red-700 mt-1">{{ __('Your acute/chronic ratio is >1.5. Take rest days to prevent injury!') }}</p>
                                <p class="text-sm text-red-600 mt-2 font-medium">{{ __('Ratio:') }} {{ number_format($userStats->acute_chronic_ratio, 2) }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($userStats->plateau_detected)
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-orange-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <h4 class="font-semibold text-orange-800">{{ __('Plateau Detected') }}</h4>
                                <p class="text-sm text-orange-700 mt-1">{{ __('No progression for') }} {{ $userStats->plateau_weeks }} {{ __('weeks. Consider changing your training approach.') }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Technical Analysis -->
        <div class="bg-white border border-gray-200 shadow-sm sm:rounded-lg">
            <div class="bg-gray-50 p-6 border-b border-gray-100">
                <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                    
                    <svg class="text-gray-600 w-7 h-7" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960"  fill="currentColor"><path d="M360-400v-160q0-17 11.5-28.5T400-600h160q17 0 28.5 11.5T600-560v160q0 17-11.5 28.5T560-360H400q-17 0-28.5-11.5T360-400Zm80-40h80v-80h-80v80Zm-80 280v-40h-80q-33 0-56.5-23.5T200-280v-80h-40q-17 0-28.5-11.5T120-400q0-17 11.5-28.5T160-440h40v-80h-40q-17 0-28.5-11.5T120-560q0-17 11.5-28.5T160-600h40v-80q0-33 23.5-56.5T280-760h80v-40q0-17 11.5-28.5T400-840q17 0 28.5 11.5T440-800v40h80v-40q0-17 11.5-28.5T560-840q17 0 28.5 11.5T600-800v40h80q33 0 56.5 23.5T760-680v80h40q17 0 28.5 11.5T840-560q0 17-11.5 28.5T800-520h-40v80h40q17 0 28.5 11.5T840-400q0 17-11.5 28.5T800-360h-40v80q0 33-23.5 56.5T680-200h-80v40q0 17-11.5 28.5T560-120q-17 0-28.5-11.5T520-160v-40h-80v40q0 17-11.5 28.5T400-120q-17 0-28.5-11.5T360-160Zm320-120v-400H280v400h400ZM480-480Z"/></svg>
                    {{ __('Technical Analysis') }}
                </h3>
                <p class="text-sm text-gray-600 mt-2">{{ __('How you climb, not just what you succeed') }}</p>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Consistency Variance -->
                    <div class="border-2 rounded-xl p-5 hover:shadow-md transition-shadow border-gray-200">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-semibold text-gray-900 flex items-center text-sm">
                                <svg class="w-5 h-5 mr-2 text-gray-500" xmlns="http://www.w3.org/2000/svg"  viewBox="0 -960 960 960" fill="currentColor"><path d="M680-160q-17 0-28.5-11.5T640-200v-200q0-17 11.5-28.5T680-440h80q17 0 28.5 11.5T800-400v200q0 17-11.5 28.5T760-160h-80Zm-240 0q-17 0-28.5-11.5T400-200v-560q0-17 11.5-28.5T440-800h80q17 0 28.5 11.5T560-760v560q0 17-11.5 28.5T520-160h-80Zm-240 0q-17 0-28.5-11.5T160-200v-360q0-17 11.5-28.5T200-600h80q17 0 28.5 11.5T320-560v360q0 17-11.5 28.5T280-160h-80Z"/></svg>
                                {{ __('Consistency') }}
                            </h4>
                        </div>
                        <p class="text-3xl font-bold text-gray-600 mb-2">
                            {{ $userStats->consistency_variance !== null ? number_format($userStats->consistency_variance, 2) : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-600">{{ __('Variance in difficulty. Lower = more consistent.') }}</p>
                    </div>

                    <!-- Flash/Work Ratio -->
                    <div class="border-2 rounded-xl p-5 hover:shadow-md transition-shadow {{ $this->getFlashWorkRatioStatus() === 'explosive' ? 'border-purple-200 bg-purple-50' : ($this->getFlashWorkRatioStatus() === 'balanced' ? 'border-green-200 bg-green-50' : 'border-blue-200 bg-blue-50') }}">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-semibold text-gray-900 flex items-center text-sm">
                                <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                {{ __('Flash/Work Ratio') }}
                            </h4>
                            @if($this->getFlashWorkRatioStatus() === 'explosive')
                            <span class="text-xs bg-purple-500 text-white px-2 py-1 rounded-full font-medium">{{ __('Explosive') }}</span>
                            @elseif($this->getFlashWorkRatioStatus() === 'balanced')
                            <span class="text-xs bg-green-500 text-white px-2 py-1 rounded-full font-medium">{{ __('Balanced') }}</span>
                            @else
                            <span class="text-xs bg-blue-500 text-white px-2 py-1 rounded-full font-medium">{{ __('Methodical') }}</span>
                            @endif
                        </div>
                        <p class="text-3xl font-bold text-purple-600 mb-2">
                            {{ $userStats->flash_work_ratio !== null ? number_format($userStats->flash_work_ratio, 2) : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-600">{{ __('Higher = explosive, Lower = methodical') }}</p>
                    </div>

                    <!-- Abandonment Rate -->
                    <div class="border-2 rounded-xl p-5 hover:shadow-md transition-shadow {{ $userStats->risk_profile_abandonment_rate > 40 ? 'border-red-200 bg-red-50' : ($userStats->risk_profile_abandonment_rate > 20 ? 'border-yellow-200 bg-yellow-50' : 'border-green-200 bg-green-50') }}">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-semibold text-gray-900 flex items-center text-sm">
                                
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960"  class="w-5 h-5 mr-2 {{ $userStats->risk_profile_abandonment_rate > 40 ? 'text-red-500' : ($userStats->risk_profile_abandonment_rate > 20 ? 'text-yellow-500' : 'text-green-500') }}" fill="currentColor" stroke="currentColor"><path d="M320-80q-17 0-28.5-11.5T280-120v-640q0-17 11.5-28.5T320-800h80v-40q0-17 11.5-28.5T440-880h80q17 0 28.5 11.5T560-840v40h80q17 0 28.5 11.5T680-760v300q0 17-11.5 28.5T640-420q-17 0-28.5-11.5T600-460v-260H360v560h140q17 0 28.5 11.5T540-120q0 17-11.5 28.5T500-80H320Zm40-80Zm380-3-56 55q-11 11-27.5 11.5T628-108q-11-11-11-28t11-28l56-56-56-56q-11-11-11-28t11-28q11-11 28-11t28 11l56 56 56-56q11-11 27.5-11.5T852-332q11 11 11 28t-11 28l-55 56 55 56q11 11 11.5 27.5T852-108q-11 11-28 11t-28-11l-56-55Z"/></svg>
                                {{ __('Abandonment Rate') }}
                            </h4>
                            @if($userStats->risk_profile_abandonment_rate > 40)
                            <span class="text-xs bg-red-500 text-white px-2 py-1 rounded-full font-medium">{{ __('High') }}</span>
                            @elseif($userStats->risk_profile_abandonment_rate > 20)
                            <span class="text-xs bg-yellow-500 text-white px-2 py-1 rounded-full font-medium">{{ __('Medium') }}</span>
                            @else
                            <span class="text-xs bg-green-500 text-white px-2 py-1 rounded-full font-medium">{{ __('Low') }}</span>
                            @endif
                        </div>
                        <p class="text-3xl font-bold {{ $userStats->risk_profile_abandonment_rate > 40 ? 'text-red-600' : ($userStats->risk_profile_abandonment_rate > 20 ? 'text-yellow-600' : 'text-green-600') }} mb-2">
                            {{ $userStats->risk_profile_abandonment_rate !== null ? number_format($userStats->risk_profile_abandonment_rate, 1) . '%' : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-600">{{ __('Routes attempted but never completed') }}</p>
                    </div>

                    <!-- Endurance vs Power -->
                    <div class="border-2 border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow">
                        <h4 class="font-semibold text-gray-900 flex items-center text-sm mb-2">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            {{ __('Endurance vs Power') }}
                        </h4>
                        <div class="flex items-baseline space-x-2 mb-2">
                            <p class="text-2xl font-bold text-gray-600">{{ $userStats->long_routes_count ?? 0 }}</p>
                            <span class="text-gray-400">/</span>
                            <p class="text-2xl font-bold text-gray-600">{{ $userStats->short_routes_count ?? 0 }}</p>
                        </div>
                        <p class="text-xs text-gray-600">{{ __('Long / Short routes completed') }}</p>
                    </div>

                    <!-- Movement Preferences -->
                    @if($userStats->movement_preferences)
                    <div class="border-2 border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow md:col-span-2">
                        <h4 class="font-semibold text-gray-900 flex items-center text-sm mb-3">
                            
                            <svg class="w-5 h-5 mr-2 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960"  fill="currentColor">
              <path d="M856-390 570-104q-12 12-27 18t-30 6q-15 0-30-6t-27-18L103-457q-11-11-17-25.5T80-513v-287q0-33 23.5-56.5T160-880h287q16 0 31 6.5t26 17.5l352 353q12 12 17.5 27t5.5 30q0 15-5.5 29.5T856-390ZM260-640q25 0 42.5-17.5T320-700q0-25-17.5-42.5T260-760q-25 0-42.5 17.5T200-700q0 25 17.5 42.5T260-640Z" />
            </svg>
                            {{ __('Movement Preferences') }}
                        </h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach(array_slice($userStats->movement_preferences, 0, 8, true) as $tag => $count)
                                <span class=" mr-2 inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                    <svg class="h-1.5 w-1.5 fill-gray-500" viewBox="0 0 6 6" aria-hidden="true">
                <circle cx="3" cy="3" r="3"></circle>
              </svg>
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
        <div class="bg-white border border-gray-200 shadow-sm sm:rounded-lg">
            <div class="bg-gray-50 p-6 border-b border-gray-100">
                <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                    
                    <svg class="w-7 h-7 mr-3 text-emerald-600"  xmlns="http://www.w3.org/2000/svg"  viewBox="0 -960 960 960"fill="currentColor"><path d="m447-426 2 26q.9 7.11 5.85 11.56Q459.8-384 467-384h26q7.2 0 12.15-4.44 4.95-4.45 5.85-11.56l2-26q11.43-3.82 20.71-8.91Q543-440 552-448l23 10q6.44 3 12.88 1.06T598-445l13-22q4-6 2-13t-6.69-10.75L586-505q2-11.5 2-23t-2-23l20.31-14.25Q611-569 613-576t-2-13l-13-22q-3.68-6.13-10.12-8.06Q581.44-621 575-618l-23 10q-8-8-18-13t-21-9l-2-26q-.9-7.11-5.85-11.56Q500.2-672 493-672h-26q-7.2 0-12.15 4.44-4.95 4.45-5.85 11.56l-2 26q-11.43 3.82-20.71 8.91Q417-616 408-608l-23-10q-6.44-3-12.88-1.06-6.44 1.93-10.12 8.06l-13 22q-4 6-2 13t6.69 10.75L374-551q-2 11.5-2 23t2 23l-20.31 14.25Q349-487 347-480t2 13l13 22q3.68 6.12 10.12 8.06Q378.56-435 385-438l23-10q8 8 18 13t21 9Zm33-54q-20 0-34-14t-14-34q0-20 14-34t34-14q20 0 34 14t14 34q0 20-14 34t-34 14ZM264-271q-57-48-88.5-115.57T144-529q0-139.58 98.29-237.29Q340.58-864 481-864q109 0 196 58.5T792-653l66 223q5 17.48-5.5 31.74Q842-384 824-384h-56v120q0 29.7-21.15 50.85Q725.7-192 696-192h-96v60q0 15.3-10.29 25.65Q579.42-96 564.21-96t-25.71-10.35Q528-116.7 528-132v-96q0-15.3 10.35-25.65Q548.7-264 564-264h132v-156q0-15.3 10.35-25.65Q716.7-456 732-456h44l-52-173q-22-72-89.5-117.5T481-792q-111 0-188 76.63T216-529q0 58.93 25 111.96Q266-364 311-326l25 22v172q0 15.3-10.29 25.65Q315.42-96 300.21-96t-25.71-10.35Q264-116.7 264-132v-139Zm232-173Z"/></svg>
                    {{ __('Behavioral Analysis') }}
                </h3>
                <p class="text-sm text-gray-600 mt-2">{{ __('What you choose to climb reveals your style') }}</p>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Preferred Hour -->
                    <div class="border-2 border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow">
                        <h4 class="font-semibold text-gray-900 flex items-center text-sm mb-2">
                            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ __('Preferred Hour') }}
                        </h4>
                        <p class="text-3xl font-bold text-emerald-600 mb-2">
                            {{ $userStats->preferred_climbing_hour ?? 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-600">{{ __('Most common climbing time') }}</p>
                    </div>

                    <!-- Session Duration -->
                    <div class="border-2 border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow">
                        <h4 class="font-semibold text-gray-900 flex items-center text-sm mb-2">
                            <svg class="w-5 h-5 mr-2 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ __('Avg Session') }}
                        </h4>
                        <p class="text-3xl font-bold text-teal-600 mb-2">
                            {{ $userStats->avg_session_duration !== null ? number_format($userStats->avg_session_duration, 1) . 'h' : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-600">{{ __('Average session duration') }}</p>
                    </div>

                    <!-- Routes Per Session -->
                    <div class="border-2 border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow">
                        <h4 class="font-semibold text-gray-900 flex items-center text-sm mb-2">
                            <svg class="w-5 h-5 mr-2 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ __('Routes/Session') }}
                        </h4>
                        <p class="text-3xl font-bold text-cyan-600 mb-2">
                            {{ $userStats->avg_routes_per_session !== null ? number_format($userStats->avg_routes_per_session, 1) : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-600">{{ __('Average routes per session') }}</p>
                    </div>

                    <!-- Exploration Ratio -->
                    <div class="border-2 border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow">
                        <h4 class="font-semibold text-gray-900 flex items-center text-sm mb-2">
                            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            {{ __('Explorer Score') }}
                        </h4>
                        <p class="text-3xl font-bold text-green-600 mb-2">
                            {{ $userStats->exploration_ratio !== null ? number_format($userStats->exploration_ratio, 1) . '%' : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-600">{{ __('New vs repeated routes') }}</p>
                    </div>

                    <!-- Project Count -->
                    <div class="border-2 border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow">
                        <h4 class="font-semibold text-gray-900 flex items-center text-sm mb-2">
                            <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            {{ __('Projects') }}
                        </h4>
                        <p class="text-3xl font-bold text-amber-600 mb-2">
                            {{ $userStats->project_count ?? 0 }}
                        </p>
                        <p class="text-xs text-gray-600">{{ __('Multi-session routes') }}</p>
                    </div>

                    <!-- Attempts Before Success -->
                    <div class="border-2 border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow">
                        <h4 class="font-semibold text-gray-900 flex items-center text-sm mb-2">
                            <svg class="w-5 h-5 mr-2 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            {{ __('Tenacity') }}
                        </h4>
                        <p class="text-3xl font-bold text-rose-600 mb-2">
                            {{ $userStats->avg_attempts_before_success !== null ? number_format($userStats->avg_attempts_before_success, 1) : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-600">{{ __('Avg attempts to send') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progression Analysis -->
        <div class="bg-white border border-gray-200 shadow-sm sm:rounded-lg">
            <div class="bg-gray-50 p-6 border-b border-gray-100">
                <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                    <svg class="w-7 h-7 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    {{ __('Progression Analysis') }}
                </h3>
                <p class="text-sm text-gray-600 mt-2">{{ __('Your actual improvement over time') }}</p>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Progression Rate -->
                    <div class="border-2 rounded-xl p-5 hover:shadow-md transition-shadow {{ $userStats->progression_rate && $userStats->progression_rate > 0 ? 'border-green-200 bg-green-50' : 'border-gray-200' }}">
                        <h4 class="font-semibold text-gray-900 flex items-center text-sm mb-2">
                            <svg class="w-5 h-5 mr-2 {{ $userStats->progression_rate && $userStats->progression_rate > 0 ? 'text-green-500' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            {{ __('Progression Rate') }}
                        </h4>
                        <p class="text-3xl font-bold {{ $userStats->progression_rate && $userStats->progression_rate > 0 ? 'text-green-600' : 'text-gray-600' }} mb-2">
                            {{ $userStats->progression_rate !== null ? ($userStats->progression_rate > 0 ? '+' : '') . number_format($userStats->progression_rate, 1) . ' pts/mo' : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-600">{{ __('Grade progression per month') }}</p>
                    </div>

                    <!-- Plateau Detection -->
                    <div class="border-2 rounded-xl p-5 hover:shadow-md transition-shadow {{ $userStats->plateau_detected ? 'border-orange-200 bg-orange-50' : 'border-green-200 bg-green-50' }}">
                        <h4 class="font-semibold text-gray-900 flex items-center text-sm mb-2">
                            <svg class="w-5 h-5 mr-2 {{ $userStats->plateau_detected ? 'text-orange-500' : 'text-green-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            {{ __('Plateau Status') }}
                        </h4>
                        <p class="text-3xl font-bold {{ $userStats->plateau_detected ? 'text-orange-600' : 'text-green-600' }} mb-2">
                            {{ $userStats->plateau_detected ? __('Detected') : __('Progressing') }}
                        </p>
                        <p class="text-xs text-gray-600">
                            {{ $userStats->plateau_detected ? __('Stagnation for ' . $userStats->plateau_weeks . ' weeks') : __('No plateau detected') }}
                        </p>
                    </div>

                    <!-- Progression by Style -->
                    @if($userStats->progression_by_style)
                    <div class="border-2 border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow md:col-span-2">
                        <h4 class="font-semibold text-gray-900 flex items-center text-sm mb-3">
                            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                            </svg>
                            {{ __('Progression by Style') }}
                        </h4>
                        <div class="space-y-2">
                            @foreach($userStats->progression_by_style as $style => $rate)
                                <div class="flex justify-between items-center p-2 rounded {{ $rate > 0 ? 'bg-green-50' : 'bg-red-50' }}">
                                    <span class="text-sm font-medium capitalize">{{ $style }}</span>
                                    <span class="font-bold {{ $rate > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $rate > 0 ? '+' : '' }}{{ number_format($rate, 1) }} pts/mo
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
        <div class="bg-white border border-gray-200 shadow-sm sm:rounded-lg">
            <div class="bg-gray-50 p-6 border-b border-gray-100">
                <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                    <svg class="w-7 h-7 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    {{ __('Training Load') }}
                </h3>
                <p class="text-sm text-gray-600 mt-2">{{ __('Optimize progression and prevent injuries') }}</p>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Weekly Volume -->
                    <div class="border-2 border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow">
                        <h4 class="font-semibold text-gray-900 flex items-center text-sm mb-2">
                            <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            {{ __('Weekly Volume') }}
                        </h4>
                        <p class="text-3xl font-bold text-purple-600 mb-2">
                            {{ $userStats->weekly_volume !== null ? number_format($userStats->weekly_volume) : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-600">{{ __('Total load last 7 days') }}</p>
                    </div>

                    <!-- Weekly Intensity -->
                    <div class="border-2 border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow">
                        <h4 class="font-semibold text-gray-900 flex items-center text-sm mb-2">
                            <svg class="w-5 h-5 mr-2 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            {{ __('Weekly Intensity') }}
                        </h4>
                        <p class="text-3xl font-bold text-pink-600 mb-2">
                            {{ $userStats->weekly_intensity !== null ? number_format($userStats->weekly_intensity) : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-600">{{ __('Avg difficulty last 7 days') }}</p>
                    </div>

                    <!-- Acute/Chronic Ratio -->
                    <div class="border-2 rounded-xl p-5 hover:shadow-md transition-shadow {{ $userStats->acute_chronic_ratio && $userStats->acute_chronic_ratio > 1.5 ? 'border-red-200 bg-red-50' : ($userStats->acute_chronic_ratio && $userStats->acute_chronic_ratio < 0.8 ? 'border-yellow-200 bg-yellow-50' : 'border-green-200 bg-green-50') }}">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-semibold text-gray-900 flex items-center text-sm">
                                <svg class="w-5 h-5 mr-2 {{ $userStats->acute_chronic_ratio && $userStats->acute_chronic_ratio > 1.5 ? 'text-red-500' : 'text-green-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                {{ __('A/C Ratio') }}
                            </h4>
                            @if($userStats->acute_chronic_ratio && $userStats->acute_chronic_ratio > 1.5)
                            <span class="text-xs bg-red-500 text-white px-2 py-1 rounded-full font-medium">{{ __('Risk') }}</span>
                            @elseif($userStats->acute_chronic_ratio && $userStats->acute_chronic_ratio >= 0.8)
                            <span class="text-xs bg-green-500 text-white px-2 py-1 rounded-full font-medium">{{ __('Good') }}</span>
                            @else
                            <span class="text-xs bg-yellow-500 text-white px-2 py-1 rounded-full font-medium">{{ __('Low') }}</span>
                            @endif
                        </div>
                        <p class="text-3xl font-bold {{ $userStats->acute_chronic_ratio && $userStats->acute_chronic_ratio > 1.5 ? 'text-red-600' : ($userStats->acute_chronic_ratio && $userStats->acute_chronic_ratio < 0.8 ? 'text-yellow-600' : 'text-green-600') }} mb-2">
                            {{ $userStats->acute_chronic_ratio !== null ? number_format($userStats->acute_chronic_ratio, 2) : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-600">{{ __('Sweet spot: 0.8-1.3') }}</p>
                    </div>

                    <!-- Recovery Time -->
                    <div class="border-2 border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow">
                        <h4 class="font-semibold text-gray-900 flex items-center text-sm mb-2">
                            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ __('Recovery Time') }}
                        </h4>
                        <p class="text-3xl font-bold text-indigo-600 mb-2">
                            {{ $userStats->avg_recovery_time !== null ? number_format($userStats->avg_recovery_time, 1) . 'h' : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-600">{{ __('Between sessions') }}</p>
                    </div>

                    <!-- Time Between Performances -->
                    <div class="border-2 border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow">
                        <h4 class="font-semibold text-gray-900 flex items-center text-sm mb-2">
                            <svg class="w-5 h-5 mr-2 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                            {{ __('Peak Frequency') }}
                        </h4>
                        <p class="text-3xl font-bold text-violet-600 mb-2">
                            {{ $userStats->avg_time_between_performances !== null ? number_format($userStats->avg_time_between_performances / 24, 1) . ' days' : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-600">{{ __('Between peak performances') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help Section -->
        <div class="bg-gray-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-gray-800">{{ __('📖 Need More Details?') }}</h3>
                    <p class="text-sm text-gray-700 mt-1">
                        {{ __('For detailed explanations of how each statistic is calculated, see the') }}
                        <a href="https://github.com/paulhenry46/TopoClimb/blob/main/STATS_CALCULATION_DOCUMENTATION.md" target="_blank" class="font-medium underline hover:text-blue-900">
                            {{ __('Statistics Calculation Documentation') }}
                        </a>
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
