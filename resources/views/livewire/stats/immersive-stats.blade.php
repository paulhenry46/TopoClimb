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

<div class="min-h-screen" style="background: linear-gradient(135deg, #1E1E1E 0%, #2D2D2D 100%);">
    @if(!$hasStats)
        <div class="max-w-4xl mx-auto px-4 py-16">
            <div class="bg-gradient-to-r from-orange-500/10 to-red-500/10 border-l-4 border-[#FF6F3C] p-6 rounded-r-lg backdrop-blur">
                <div class="flex items-start">
                    <svg class="w-8 h-8 text-[#FF6F3C] mt-1 mr-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <h3 class="text-xl font-bold text-[#F5F5F5] mb-2">{{ __('Start Your Climbing Journey') }}</h3>
                        <p class="text-gray-300">
                            {{ __('Statistics will be calculated after the nightly update at 2 AM. Begin logging your climbs to unlock your personalized climbing analytics!') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Hero Header -->
        <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #FF6F3C 0%, #E63946 100%);">
            <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23fff&quot; fill-opacity=&quot;1&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            <div class="max-w-7xl mx-auto px-4 py-12 relative z-10">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-5xl font-bold text-white mb-2" style="font-family: 'Inter', sans-serif; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                            {{ __('Your Climbing Analytics') }}
                        </h1>
                        <p class="text-white/90 text-lg">{{ __('Updated') }} {{ $lastCalculated }}</p>
                    </div>
                    <div class="hidden md:block">
                        <svg class="w-24 h-24 text-white/20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-8 bg-gradient-to-t from-[#1E1E1E] to-transparent"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 py-8 space-y-12">
            
            <!-- Critical Alerts -->
            @if($userStats->overtraining_detected || $userStats->plateau_detected)
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-r from-[#E63946]/20 to-[#F7B801]/20 blur-xl"></div>
                <div class="relative bg-[#2D2D2D]/80 backdrop-blur border-l-4 border-[#E63946] rounded-r-2xl p-6">
                    <h3 class="text-2xl font-bold text-[#E63946] mb-4 flex items-center">
                        <svg class="w-8 h-8 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        {{ __('⚠️ Critical Alerts') }}
                    </h3>
                    <div class="space-y-3">
                        @if($userStats->overtraining_detected)
                        <div class="bg-[#E63946]/10 border border-[#E63946]/30 rounded-xl p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mr-3">
                                    <div class="w-3 h-3 bg-[#E63946] rounded-full animate-pulse"></div>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[#F5F5F5] text-lg">{{ __('Overtraining Risk Detected') }}</h4>
                                    <p class="text-gray-300 mt-1">{{ __('Your acute/chronic ratio is') }} <span class="font-bold text-[#E63946]">{{ number_format($userStats->acute_chronic_ratio, 2) }}</span>. {{ __('Take rest days to prevent injury!') }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($userStats->plateau_detected)
                        <div class="bg-[#F7B801]/10 border border-[#F7B801]/30 rounded-xl p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mr-3">
                                    <div class="w-3 h-3 bg-[#F7B801] rounded-full"></div>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[#F5F5F5] text-lg">{{ __('Plateau Detected') }}</h4>
                                    <p class="text-gray-300 mt-1">{{ __('No progression for') }} <span class="font-bold text-[#F7B801]">{{ $userStats->plateau_weeks }}</span> {{ __('weeks. Time to change your training approach!') }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- TECHNICAL ANALYSIS -->
            <section class="relative">
                <!-- Diagonal separator -->
                <div class="absolute -top-8 left-0 right-0 h-16 bg-gradient-to-br from-[#3C91E6]/20 to-transparent transform -skew-y-2"></div>
                
                <div class="bg-gradient-to-br from-[#2D2D2D] to-[#1E1E1E] rounded-3xl p-8 border border-[#3C91E6]/20">
                    <h2 class="text-3xl font-bold text-[#F5F5F5] mb-2 flex items-center">
                        <span class="text-4xl mr-3">🎯</span>
                        {{ __('Technical Analysis') }}
                    </h2>
                    <p class="text-gray-400 mb-8">{{ __('How you climb, not just what you succeed') }}</p>

                    <div class="space-y-8">
                        <!-- Consistency: Wavy Line -->
                        <div class="relative p-6 bg-[#1E1E1E]/50 rounded-2xl border border-[#3C91E6]/10">
                            <h3 class="text-xl font-semibold text-[#F5F5F5] mb-4">{{ __('Consistency') }}</h3>
                            <div class="flex items-end justify-between">
                                <div>
                                    <div class="text-4xl font-bold text-[#3C91E6]">
                                        {{ $userStats->consistency_variance !== null ? number_format($userStats->consistency_variance, 1) : 'N/A' }}
                                    </div>
                                    <p class="text-sm text-gray-400 mt-1">{{ __('Variance • Lower is better') }}</p>
                                </div>
                                <div class="flex-1 ml-8">
                                    <!-- Wavy line visualization -->
                                    <svg class="w-full h-20" viewBox="0 0 400 80" preserveAspectRatio="none">
                                        <defs>
                                            <linearGradient id="waveGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                                <stop offset="0%" style="stop-color:#3C91E6;stop-opacity:1" />
                                                <stop offset="100%" style="stop-color:#6BCB77;stop-opacity:1" />
                                            </linearGradient>
                                        </defs>
                                        @php
                                            $amplitude = $userStats->consistency_variance ? min($userStats->consistency_variance / 10, 30) : 5;
                                            $path = "M 0 40 ";
                                            for($i = 0; $i <= 400; $i += 10) {
                                                $y = 40 + sin($i / 20) * $amplitude;
                                                $path .= "L $i $y ";
                                            }
                                        @endphp
                                        <path d="{{ $path }}" fill="none" stroke="url(#waveGradient)" stroke-width="3" opacity="0.8"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Flash/Work Ratio: Horizontal Slider -->
                        <div class="relative p-6 bg-[#1E1E1E]/50 rounded-2xl border border-[#FF6F3C]/10">
                            <h3 class="text-xl font-semibold text-[#F5F5F5] mb-4">{{ __('Climbing Style') }}</h3>
                            <div class="relative">
                                <div class="flex justify-between text-sm text-gray-400 mb-2">
                                    <span>{{ __('Methodical') }}</span>
                                    <span>{{ __('Explosive') }}</span>
                                </div>
                                <div class="h-3 bg-[#1E1E1E] rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-[#3C91E6] via-[#6BCB77] to-[#FF6F3C]"></div>
                                </div>
                                @php
                                    $ratio = $userStats->flash_work_ratio ?? 1;
                                    $position = min(max(($ratio / 3) * 100, 0), 100);
                                @endphp
                                <div class="relative mt-2">
                                    <div class="absolute top-0 transition-all duration-500" style="left: {{ $position }}%;">
                                        <div class="relative -ml-6">
                                            <!-- Climbing Hold Icon -->
                                            <div class="w-12 h-12 bg-gradient-to-br from-[#FF6F3C] to-[#E63946] rounded-full flex items-center justify-center shadow-lg border-2 border-white/20">
                                                <span class="text-white font-bold text-lg">{{ number_format($ratio, 1) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Abandonment Rate: Ghost Hold -->
                        <div class="relative p-6 bg-[#1E1E1E]/50 rounded-2xl border border-gray-700/10">
                            <h3 class="text-xl font-semibold text-[#F5F5F5] mb-4">{{ __('Abandonment Rate') }}</h3>
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="text-4xl font-bold" style="color: {{ $userStats->risk_profile_abandonment_rate > 40 ? '#E63946' : ($userStats->risk_profile_abandonment_rate > 20 ? '#F7B801' : '#6BCB77') }}">
                                        {{ $userStats->risk_profile_abandonment_rate !== null ? number_format($userStats->risk_profile_abandonment_rate, 1) . '%' : 'N/A' }}
                                    </div>
                                    <p class="text-sm text-gray-400 mt-1">{{ __('Routes abandoned') }}</p>
                                </div>
                                <div class="ml-8">
                                    <!-- Ghost Hold SVG -->
                                    <svg class="w-24 h-24" viewBox="0 0 100 100">
                                        <defs>
                                            <filter id="glow">
                                                <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
                                                <feMerge>
                                                    <feMergeNode in="coloredBlur"/>
                                                    <feMergeNode in="SourceGraphic"/>
                                                </feMerge>
                                            </filter>
                                        </defs>
                                        <ellipse cx="50" cy="50" rx="35" ry="40" fill="{{ $userStats->risk_profile_abandonment_rate > 40 ? '#E63946' : '#888' }}" opacity="{{ ($userStats->risk_profile_abandonment_rate ?? 0) / 100 }}" filter="url(#glow)"/>
                                        <circle cx="40" cy="45" r="5" fill="#fff" opacity="0.3"/>
                                        <circle cx="60" cy="45" r="5" fill="#fff" opacity="0.3"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Endurance vs Power: Dual Bars -->
                        <div class="relative p-6 bg-[#1E1E1E]/50 rounded-2xl border border-[#6BCB77]/10">
                            <h3 class="text-xl font-semibold text-[#F5F5F5] mb-6">{{ __('Endurance vs Power') }}</h3>
                            <div class="flex justify-around items-end h-48">
                                <div class="flex flex-col items-center flex-1 mx-4">
                                    <div class="text-2xl font-bold text-[#6BCB77] mb-2">{{ $userStats->long_routes_count ?? 0 }}</div>
                                    @php $longHeight = min(($userStats->long_routes_count ?? 0) * 10, 100); @endphp
                                    <div class="w-full bg-[#1E1E1E] rounded-t-xl overflow-hidden" style="height: {{ $longHeight }}%;">
                                        <div class="w-full h-full bg-gradient-to-t from-[#6BCB77] to-[#3C91E6]"></div>
                                    </div>
                                    <p class="text-sm text-gray-400 mt-2">{{ __('Long Routes') }}</p>
                                </div>
                                <div class="flex flex-col items-center flex-1 mx-4">
                                    <div class="text-2xl font-bold text-[#FF6F3C] mb-2">{{ $userStats->short_routes_count ?? 0 }}</div>
                                    @php $shortHeight = min(($userStats->short_routes_count ?? 0) * 10, 100); @endphp
                                    <div class="w-full bg-[#1E1E1E] rounded-t-xl overflow-hidden" style="height: {{ $shortHeight }}%;">
                                        <div class="w-full h-full bg-gradient-to-t from-[#FF6F3C] to-[#F7B801]"></div>
                                    </div>
                                    <p class="text-sm text-gray-400 mt-2">{{ __('Short Routes') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Movement Preferences: Radar Chart -->
                        @if($userStats->movement_preferences && count($userStats->movement_preferences) > 0)
                        <div class="relative p-6 bg-[#1E1E1E]/50 rounded-2xl border border-[#FF6F3C]/10">
                            <h3 class="text-xl font-semibold text-[#F5F5F5] mb-6">{{ __('Movement Preferences') }}</h3>
                            <div class="flex items-center">
                                <div class="w-64 h-64">
                                    <canvas id="radarChart" class="w-full h-full"></canvas>
                                </div>
                                <div class="flex-1 ml-8">
                                    <div class="grid grid-cols-2 gap-3">
                                        @foreach(array_slice($userStats->movement_preferences, 0, 6, true) as $tag => $count)
                                            <div class="flex items-center justify-between p-3 bg-[#2D2D2D] rounded-lg">
                                                <span class="text-[#F5F5F5] capitalize">{{ $tag }}</span>
                                                <span class="font-bold text-[#6BCB77]">{{ $count }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const ctx = document.getElementById('radarChart');
                                if (ctx) {
                                    const radarCtx = ctx.getContext('2d');
                                    const data = @json(array_slice($userStats->movement_preferences ?? [], 0, 6, true));
                                    const labels = Object.keys(data);
                                    const values = Object.values(data);
                                    const maxValue = Math.max(...values);
                                    
                                    // Simple radar chart drawing
                                    const centerX = ctx.width / 2;
                                    const centerY = ctx.height / 2;
                                    const radius = Math.min(centerX, centerY) - 40;
                                    const points = labels.length;
                                    
                                    // Draw web
                                    radarCtx.strokeStyle = '#3C91E6';
                                    radarCtx.lineWidth = 1;
                                    for (let level = 1; level <= 5; level++) {
                                        radarCtx.beginPath();
                                        for (let i = 0; i <= points; i++) {
                                            const angle = (i * 2 * Math.PI) / points - Math.PI / 2;
                                            const r = (radius * level) / 5;
                                            const x = centerX + r * Math.cos(angle);
                                            const y = centerY + r * Math.sin(angle);
                                            if (i === 0) radarCtx.moveTo(x, y);
                                            else radarCtx.lineTo(x, y);
                                        }
                                        radarCtx.closePath();
                                        radarCtx.stroke();
                                    }
                                    
                                    // Draw data
                                    radarCtx.fillStyle = '#FF6F3C80';
                                    radarCtx.strokeStyle = '#FF6F3C';
                                    radarCtx.lineWidth = 2;
                                    radarCtx.beginPath();
                                    for (let i = 0; i <= points; i++) {
                                        const angle = (i * 2 * Math.PI) / points - Math.PI / 2;
                                        const value = values[i % points];
                                        const r = (radius * value) / maxValue;
                                        const x = centerX + r * Math.cos(angle);
                                        const y = centerY + r * Math.sin(angle);
                                        if (i === 0) radarCtx.moveTo(x, y);
                                        else radarCtx.lineTo(x, y);
                                    }
                                    radarCtx.closePath();
                                    radarCtx.fill();
                                    radarCtx.stroke();
                                }
                            });
                        </script>
                        @endif
                    </div>
                </div>
            </section>

            <!-- BEHAVIORAL ANALYSIS -->
            <section class="relative">
                <div class="absolute -top-8 left-0 right-0 h-16 bg-gradient-to-br from-[#6BCB77]/20 to-transparent transform skew-y-2"></div>
                
                <div class="bg-gradient-to-br from-[#2D2D2D] to-[#1E1E1E] rounded-3xl p-8 border border-[#6BCB77]/20">
                    <h2 class="text-3xl font-bold text-[#F5F5F5] mb-2 flex items-center">
                        <span class="text-4xl mr-3">🧠</span>
                        {{ __('Behavioral Analysis') }}
                    </h2>
                    <p class="text-gray-400 mb-8">{{ __('Your climbing habits and patterns') }}</p>

                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Preferred Hour: 24h Ring -->
                        <div class="p-6 bg-[#1E1E1E]/50 rounded-2xl border border-[#6BCB77]/10">
                            <h3 class="text-xl font-semibold text-[#F5F5F5] mb-4">{{ __('Preferred Hour') }}</h3>
                            <div class="flex items-center justify-center">
                                <div class="relative w-48 h-48">
                                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 200 200">
                                        <!-- 24h ring background -->
                                        <circle cx="100" cy="100" r="80" fill="none" stroke="#2D2D2D" stroke-width="20"/>
                                        <!-- Highlighted segment -->
                                        @php
                                            $hour = $userStats->preferred_climbing_hour ? (int)substr($userStats->preferred_climbing_hour, 0, 2) : 18;
                                            $startAngle = ($hour * 15) - 7.5;
                                            $endAngle = ($hour * 15) + 7.5;
                                            $start = deg2rad($startAngle);
                                            $end = deg2rad($endAngle);
                                            $largeArc = ($endAngle - $startAngle) > 180 ? 1 : 0;
                                            $startX = 100 + 80 * cos($start);
                                            $startY = 100 + 80 * sin($start);
                                            $endX = 100 + 80 * cos($end);
                                            $endY = 100 + 80 * sin($end);
                                        @endphp
                                        <path d="M 100 100 L {{ $startX }} {{ $startY }} A 80 80 0 {{ $largeArc }} 1 {{ $endX }} {{ $endY }} Z" fill="#6BCB77" opacity="0.8"/>
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="text-center">
                                            <div class="text-3xl font-bold text-[#6BCB77]">{{ $userStats->preferred_climbing_hour ?? 'N/A' }}</div>
                                            <p class="text-xs text-gray-400">{{ __('Peak Time') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Avg Session: Progress Bar -->
                        <div class="p-6 bg-[#1E1E1E]/50 rounded-2xl border border-[#3C91E6]/10">
                            <h3 class="text-xl font-semibold text-[#F5F5F5] mb-4">{{ __('Average Session') }}</h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm text-gray-400">{{ __('Duration') }}</span>
                                        <span class="font-bold text-[#3C91E6]">{{ $userStats->avg_session_duration ? number_format($userStats->avg_session_duration, 1) . 'h' : 'N/A' }}</span>
                                    </div>
                                    @php $durationPercent = min(($userStats->avg_session_duration ?? 0) * 20, 100); @endphp
                                    <div class="h-4 bg-[#1E1E1E] rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-[#3C91E6] to-[#6BCB77] transition-all duration-500" style="width: {{ $durationPercent }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Routes/Session: Climbing Holds -->
                        <div class="p-6 bg-[#1E1E1E]/50 rounded-2xl border border-[#FF6F3C]/10">
                            <h3 class="text-xl font-semibold text-[#F5F5F5] mb-4">{{ __('Routes Per Session') }}</h3>
                            <div class="flex justify-around items-end h-32">
                                @php
                                    $routesPerSession = $userStats->avg_routes_per_session ?? 0;
                                    $holds = 3;
                                @endphp
                                @for($i = 1; $i <= $holds; $i++)
                                    @php
                                        $filled = $routesPerSession >= ($i * 5);
                                        $partial = $routesPerSession > (($i-1) * 5) && $routesPerSession < ($i * 5);
                                        $opacity = $filled ? 1 : ($partial ? ($routesPerSession - (($i-1) * 5)) / 5 : 0.2);
                                    @endphp
                                    <div class="flex flex-col items-center">
                                        <!-- Climbing Hold Shape -->
                                        <svg class="w-16 h-16" viewBox="0 0 100 100">
                                            <ellipse cx="50" cy="50" rx="40" ry="35" fill="#FF6F3C" opacity="{{ $opacity }}" transform="rotate({{ rand(-15, 15) }} 50 50)"/>
                                            <circle cx="50" cy="50" r="8" fill="#000" opacity="{{ $opacity * 0.5 }}"/>
                                        </svg>
                                    </div>
                                @endfor
                            </div>
                            <div class="text-center mt-4">
                                <span class="text-3xl font-bold text-[#FF6F3C]">{{ number_format($routesPerSession, 1) }}</span>
                                <span class="text-gray-400 ml-2">{{ __('routes') }}</span>
                            </div>
                        </div>

                        <!-- Explorer Score: Compass -->
                        <div class="p-6 bg-[#1E1E1E]/50 rounded-2xl border border-[#6BCB77]/10">
                            <h3 class="text-xl font-semibold text-[#F5F5F5] mb-4">{{ __('Explorer Score') }}</h3>
                            <div class="flex items-center justify-center">
                                <div class="relative w-32 h-32">
                                    <svg class="w-full h-full" viewBox="0 0 200 200">
                                        <!-- Compass circle -->
                                        <circle cx="100" cy="100" r="90" fill="none" stroke="#3C91E6" stroke-width="3"/>
                                        <circle cx="100" cy="100" r="70" fill="none" stroke="#3C91E6" stroke-width="1" opacity="0.3"/>
                                        <!-- Compass needle -->
                                        @php $angle = ($userStats->exploration_ratio ?? 50) * 3.6; @endphp
                                        <g transform="rotate({{ $angle }} 100 100)">
                                            <path d="M 100 40 L 105 100 L 100 95 L 95 100 Z" fill="#6BCB77"/>
                                            <path d="M 100 160 L 105 100 L 100 105 L 95 100 Z" fill="#E63946" opacity="0.5"/>
                                        </g>
                                        <circle cx="100" cy="100" r="8" fill="#F5F5F5"/>
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="text-center mt-8">
                                            <div class="text-2xl font-bold text-[#6BCB77]">{{ $userStats->exploration_ratio ? number_format($userStats->exploration_ratio, 0) . '%' : 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Projects: Climbing Bag -->
                        <div class="p-6 bg-[#1E1E1E]/50 rounded-2xl border border-[#F7B801]/10">
                            <h3 class="text-xl font-semibold text-[#F5F5F5] mb-4">{{ __('Active Projects') }}</h3>
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-4xl font-bold text-[#F7B801]">{{ $userStats->project_count ?? 0 }}</div>
                                    <p class="text-sm text-gray-400 mt-1">{{ __('Multi-session routes') }}</p>
                                </div>
                                <div class="relative">
                                    <!-- Bag Icon -->
                                    <svg class="w-20 h-20" viewBox="0 0 100 100">
                                        <rect x="30" y="40" width="40" height="50" rx="5" fill="#F7B801" opacity="0.8"/>
                                        <path d="M 40 40 Q 50 20 60 40" fill="none" stroke="#F7B801" stroke-width="3"/>
                                        <circle cx="50" cy="65" r="3" fill="#fff"/>
                                    </svg>
                                    @if($userStats->project_count > 0)
                                    <div class="absolute -top-2 -right-2 w-8 h-8 bg-[#E63946] rounded-full flex items-center justify-center text-white font-bold text-sm border-2 border-[#1E1E1E]">
                                        {{ min($userStats->project_count, 99) }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Tenacity: Rope Graphic -->
                        <div class="p-6 bg-[#1E1E1E]/50 rounded-2xl border border-[#FF6F3C]/10">
                            <h3 class="text-xl font-semibold text-[#F5F5F5] mb-4">{{ __('Tenacity') }}</h3>
                            <div class="flex items-end justify-between">
                                <div>
                                    <div class="text-4xl font-bold text-[#FF6F3C]">{{ $userStats->avg_attempts_before_success ? number_format($userStats->avg_attempts_before_success, 1) : 'N/A' }}</div>
                                    <p class="text-sm text-gray-400 mt-1">{{ __('Avg attempts') }}</p>
                                </div>
                                <div class="ml-8">
                                    <!-- Rope visualization -->
                                    @php $ropeLength = min(($userStats->avg_attempts_before_success ?? 0) * 20, 100); @endphp
                                    <svg class="w-12" style="height: {{ $ropeLength }}px;" viewBox="0 0 50 100" preserveAspectRatio="none">
                                        <defs>
                                            <pattern id="ropePattern" x="0" y="0" width="10" height="10" patternUnits="userSpaceOnUse">
                                                <line x1="5" y1="0" x2="5" y2="10" stroke="#FF6F3C" stroke-width="8"/>
                                            </pattern>
                                        </defs>
                                        <rect x="15" y="0" width="20" height="100" fill="url(#ropePattern)"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- PROGRESSION ANALYSIS -->
            <section class="relative">
                <div class="absolute -top-8 left-0 right-0 h-16 bg-gradient-to-br from-[#6BCB77]/20 to-transparent transform -skew-y-2"></div>
                
                <div class="bg-gradient-to-br from-[#2D2D2D] to-[#1E1E1E] rounded-3xl p-8 border border-[#6BCB77]/20">
                    <h2 class="text-3xl font-bold text-[#F5F5F5] mb-2 flex items-center">
                        <span class="text-4xl mr-3">📈</span>
                        {{ __('Progression Analysis') }}
                    </h2>
                    <p class="text-gray-400 mb-8">{{ __('Track your improvement over time') }}</p>

                    <div class="space-y-6">
                        <!-- Progression Rate: Diagonal Slope -->
                        <div class="p-6 bg-[#1E1E1E]/50 rounded-2xl border border-[#6BCB77]/10">
                            <h3 class="text-xl font-semibold text-[#F5F5F5] mb-4">{{ __('Progression Rate') }}</h3>
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-4xl font-bold" style="color: {{ $userStats->progression_rate && $userStats->progression_rate > 0 ? '#6BCB77' : '#888' }}">
                                        {{ $userStats->progression_rate !== null ? ($userStats->progression_rate > 0 ? '+' : '') . number_format($userStats->progression_rate, 1) . ' pts/mo' : 'N/A' }}
                                    </div>
                                    <p class="text-sm text-gray-400 mt-1">{{ __('Grade progression per month') }}</p>
                                </div>
                                <div class="flex-1 ml-8">
                                    <!-- Slope Graph -->
                                    <svg class="w-full h-32" viewBox="0 0 200 100" preserveAspectRatio="none">
                                        @php
                                            $slope = max(min($userStats->progression_rate ?? 0, 50), -50);
                                            $endY = 50 - $slope;
                                        @endphp
                                        <defs>
                                            <linearGradient id="slopeGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                                <stop offset="0%" style="stop-color:{{ $slope > 0 ? '#6BCB77' : '#E63946' }};stop-opacity:0.3" />
                                                <stop offset="100%" style="stop-color:{{ $slope > 0 ? '#6BCB77' : '#E63946' }};stop-opacity:0.8" />
                                            </linearGradient>
                                        </defs>
                                        <path d="M 0 50 L 200 {{ $endY }} L 200 100 L 0 100 Z" fill="url(#slopeGradient)"/>
                                        <path d="M 0 50 L 200 {{ $endY }}" stroke="{{ $slope > 0 ? '#6BCB77' : '#E63946' }}" stroke-width="3" fill="none"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Plateau Status: Smooth Wall -->
                        @if($userStats->plateau_detected)
                        <div class="p-6 bg-[#1E1E1E]/50 rounded-2xl border border-[#F7B801]/10">
                            <h3 class="text-xl font-semibold text-[#F5F5F5] mb-4">{{ __('Plateau Detected') }}</h3>
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <!-- Smooth wall texture -->
                                    <svg class="w-full h-32" viewBox="0 0 400 100">
                                        <rect x="0" y="0" width="400" height="100" fill="#2D2D2D"/>
                                        <rect x="0" y="0" width="400" height="100" fill="url(#wallPattern)" opacity="0.1"/>
                                        <defs>
                                            <pattern id="wallPattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                                                <rect x="0" y="0" width="20" height="20" fill="#888" opacity="0.1"/>
                                            </pattern>
                                        </defs>
                                        <text x="200" y="55" text-anchor="middle" fill="#F7B801" font-size="24" font-weight="bold">
                                            {{ $userStats->plateau_weeks }} {{ __('weeks') }}
                                        </text>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Progression by Style: Colored Holds -->
                        @if($userStats->progression_by_style)
                        <div class="p-6 bg-[#1E1E1E]/50 rounded-2xl border border-[#3C91E6]/10">
                            <h3 class="text-xl font-semibold text-[#F5F5F5] mb-6">{{ __('Progression by Style') }}</h3>
                            <div class="grid grid-cols-3 gap-6">
                                @foreach($userStats->progression_by_style as $style => $rate)
                                    <div class="text-center">
                                        <!-- Climbing Hold -->
                                        <svg class="w-20 h-20 mx-auto mb-3" viewBox="0 0 100 100">
                                            <ellipse cx="50" cy="50" rx="35" ry="40" fill="{{ $rate > 0 ? '#6BCB77' : '#888' }}" opacity="0.8" transform="rotate({{ rand(-20, 20) }} 50 50)"/>
                                            <circle cx="50" cy="50" r="6" fill="#000" opacity="0.3"/>
                                        </svg>
                                        <p class="text-sm text-gray-400 capitalize mb-1">{{ $style }}</p>
                                        <p class="font-bold" style="color: {{ $rate > 0 ? '#6BCB77' : '#E63946' }}">
                                            {{ $rate > 0 ? '+' : '' }}{{ number_format($rate, 1) }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </section>

            <!-- TRAINING LOAD -->
            <section class="relative mb-12">
                <div class="absolute -top-8 left-0 right-0 h-16 bg-gradient-to-br from-[#FF6F3C]/20 to-transparent transform skew-y-2"></div>
                
                <div class="bg-gradient-to-br from-[#2D2D2D] to-[#1E1E1E] rounded-3xl p-8 border border-[#FF6F3C]/20">
                    <h2 class="text-3xl font-bold text-[#F5F5F5] mb-2 flex items-center">
                        <span class="text-4xl mr-3">💪</span>
                        {{ __('Training Load') }}
                    </h2>
                    <p class="text-gray-400 mb-8">{{ __('Optimize your training and recovery') }}</p>

                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Weekly Volume: Vertical Bar -->
                        <div class="p-6 bg-[#1E1E1E]/50 rounded-2xl border border-[#FF6F3C]/10">
                            <h3 class="text-xl font-semibold text-[#F5F5F5] mb-4">{{ __('Weekly Volume') }}</h3>
                            <div class="flex items-end justify-between h-48">
                                <div class="flex flex-col items-center flex-1">
                                    @php $volumeHeight = min(($userStats->weekly_volume ?? 0) / 50, 100); @endphp
                                    <div class="w-full bg-[#1E1E1E] rounded-t-2xl overflow-hidden" style="height: {{ $volumeHeight }}%;">
                                        <div class="w-full h-full bg-gradient-to-t from-[#FF6F3C] to-[#F7B801]"></div>
                                    </div>
                                    <div class="text-3xl font-bold text-[#FF6F3C] mt-3">{{ $userStats->weekly_volume ? number_format($userStats->weekly_volume) : 'N/A' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Weekly Intensity: Thermometer -->
                        <div class="p-6 bg-[#1E1E1E]/50 rounded-2xl border border-[#E63946]/10">
                            <h3 class="text-xl font-semibold text-[#F5F5F5] mb-4">{{ __('Weekly Intensity') }}</h3>
                            <div class="flex items-center justify-between">
                                <div class="flex-1 mr-8">
                                    <!-- Thermometer -->
                                    <div class="relative h-48">
                                        <div class="absolute bottom-0 left-1/2 -ml-6 w-12 h-full bg-[#2D2D2D] rounded-full overflow-hidden">
                                            @php $intensityPercent = min(($userStats->weekly_intensity ?? 0) / 10, 100); @endphp
                                            <div class="absolute bottom-0 w-full bg-gradient-to-t from-[#E63946] to-[#F7B801] transition-all duration-500" style="height: {{ $intensityPercent }}%;"></div>
                                        </div>
                                        <div class="absolute bottom-0 left-1/2 -ml-8 w-16 h-16 bg-[#E63946] rounded-full border-4 border-[#2D2D2D]"></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-3xl font-bold text-[#E63946]">{{ $userStats->weekly_intensity ? number_format($userStats->weekly_intensity) : 'N/A' }}</div>
                                    <p class="text-sm text-gray-400 mt-1">{{ __('Avg difficulty') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- A/C Ratio: Triangle -->
                        <div class="p-6 bg-[#1E1E1E]/50 rounded-2xl border border-[#3C91E6]/10">
                            <h3 class="text-xl font-semibold text-[#F5F5F5] mb-4">{{ __('Acute/Chronic Ratio') }}</h3>
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-4xl font-bold" style="color: {{ $userStats->acute_chronic_ratio && $userStats->acute_chronic_ratio > 1.5 ? '#E63946' : ($userStats->acute_chronic_ratio && $userStats->acute_chronic_ratio < 0.8 ? '#F7B801' : '#6BCB77') }}">
                                        {{ $userStats->acute_chronic_ratio ? number_format($userStats->acute_chronic_ratio, 2) : 'N/A' }}
                                    </div>
                                    <p class="text-sm text-gray-400 mt-1">{{ __('Sweet spot: 0.8-1.3') }}</p>
                                </div>
                                <div class="ml-8">
                                    <!-- Triangle visualization -->
                                    <svg class="w-32 h-32" viewBox="0 0 100 100">
                                        @php
                                            $ratio = $userStats->acute_chronic_ratio ?? 1;
                                            $color = $ratio > 1.5 ? '#E63946' : ($ratio < 0.8 ? '#F7B801' : '#6BCB77');
                                            $height = min($ratio * 40, 90);
                                        @endphp
                                        <polygon points="50,{{ 90 - $height }} 20,90 80,90" fill="{{ $color }}" opacity="0.6"/>
                                        <polygon points="50,10 20,90 80,90" fill="none" stroke="#3C91E6" stroke-width="2"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Recovery Time: Hourglass -->
                        <div class="p-6 bg-[#1E1E1E]/50 rounded-2xl border border-[#6BCB77]/10">
                            <h3 class="text-xl font-semibold text-[#F5F5F5] mb-4">{{ __('Recovery Time') }}</h3>
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-4xl font-bold text-[#6BCB77]">{{ $userStats->avg_recovery_time ? number_format($userStats->avg_recovery_time, 1) . 'h' : 'N/A' }}</div>
                                    <p class="text-sm text-gray-400 mt-1">{{ __('Between sessions') }}</p>
                                </div>
                                <div class="ml-8">
                                    <!-- Hourglass -->
                                    <svg class="w-20 h-24" viewBox="0 0 60 80">
                                        <path d="M 15 5 L 45 5 L 45 25 L 30 40 L 45 55 L 45 75 L 15 75 L 15 55 L 30 40 L 15 25 Z" fill="none" stroke="#6BCB77" stroke-width="3"/>
                                        @php $sandLevel = min(($userStats->avg_recovery_time ?? 0) / 2, 35); @endphp
                                        <rect x="18" y="{{ 40 - $sandLevel }}" width="24" height="{{ $sandLevel }}" fill="#6BCB77" opacity="0.6"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Peak Frequency: Mountain -->
                        <div class="p-6 bg-[#1E1E1E]/50 rounded-2xl border border-[#F7B801]/10 md:col-span-2">
                            <h3 class="text-xl font-semibold text-[#F5F5F5] mb-4">{{ __('Peak Performance Frequency') }}</h3>
                            <div class="flex items-end justify-between">
                                <div>
                                    <div class="text-4xl font-bold text-[#F7B801]">{{ $userStats->avg_time_between_performances ? number_format($userStats->avg_time_between_performances / 24, 1) . ' days' : 'N/A' }}</div>
                                    <p class="text-sm text-gray-400 mt-1">{{ __('Between peak performances') }}</p>
                                </div>
                                <div class="flex-1 ml-8">
                                    <!-- Mountain peaks -->
                                    <svg class="w-full h-32" viewBox="0 0 400 100" preserveAspectRatio="none">
                                        <defs>
                                            <linearGradient id="mountainGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                                <stop offset="0%" style="stop-color:#F7B801;stop-opacity:0.8" />
                                                <stop offset="100%" style="stop-color:#F7B801;stop-opacity:0.2" />
                                            </linearGradient>
                                        </defs>
                                        <polygon points="0,100 50,30 100,100" fill="url(#mountainGradient)"/>
                                        <polygon points="120,100 180,20 240,100" fill="url(#mountainGradient)"/>
                                        <polygon points="260,100 320,40 380,100" fill="url(#mountainGradient)"/>
                                        <!-- Flag on highest peak -->
                                        <line x1="180" y1="20" x2="180" y2="5" stroke="#E63946" stroke-width="2"/>
                                        <polygon points="180,5 200,10 180,15" fill="#E63946"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Documentation Link -->
            <div class="bg-gradient-to-r from-[#3C91E6]/10 to-[#6BCB77]/10 border-l-4 border-[#3C91E6] p-6 rounded-r-2xl backdrop-blur">
                <div class="flex items-start">
                    <svg class="w-8 h-8 text-[#3C91E6] mr-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <h3 class="text-lg font-bold text-[#F5F5F5] mb-2">{{ __('Want to learn more?') }}</h3>
                        <p class="text-gray-300">
                            {{ __('For detailed explanations of how each statistic is calculated, see the') }}
                            <a href="https://github.com/paulhenry46/TopoClimb/blob/main/STATS_CALCULATION_DOCUMENTATION.md" target="_blank" class="font-semibold text-[#3C91E6] hover:text-[#6BCB77] underline transition-colors">
                                {{ __('Statistics Calculation Documentation') }}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
