<?php

use Livewire\Volt\Component;
use App\Models\UserStats;

new class extends Component {
    
    public $userStats;
    public $lastCalculated;
    public $movement_preferences;
    
    public function mount()
    {
        $this->userStats = auth()->user()->stats;
        $this->lastCalculated = $this->userStats?->last_calculated_at?->diffForHumans();
        $this->movement_preferences = $this->userStats->movement_preferences ;
    }
    
    public function with(): array
    {
        return [
            'hasStats' => $this->userStats !== null,
        ];
    }
}; ?>

<div class="min-h-screen">
    @if(!$hasStats)
        <div class="max-w-4xl mx-auto px-4 py-16">
            <div class="bg-gradient-to-r from-orange-100 to-red-100 border-l-4 border-orange-500 p-6 rounded-r-lg backdrop-blur">
                <div class="flex items-start">
                    <svg class="w-8 h-8 text-[#FF6F3C] mt-1 mr-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <h3 class="text-xl font-bold text-gray-600 mb-2">{{ __('Start Your Climbing Journey') }}</h3>
                        <p class="text-gray-400">
                            {{ __('Statistics will be calculated after the nightly update at 2 AM. Begin logging your climbs to unlock your personalized climbing analytics!') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Hero Header -->
        

        <div class="max-w-7xl mx-auto px-4 py-8 space-y-12">
            
            <!-- Critical Alerts -->
            @if($userStats->overtraining_detected || $userStats->plateau_detected)
            <div class="relative">
                <div class="absolute inset-0 "></div>
                <div class="relative bg-red-100/80 backdrop-blur border-l-4 border-red-500 rounded-r-2xl p-6">
                    <h3 class="text-2xl font-bold text-red-500 mb-4 flex items-center">
                        
                        <svg class="h-8 w-8 mr-2" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M109-120q-11 0-20-5.5T75-140q-5-9-5.5-19.5T75-180l370-640q6-10 15.5-15t19.5-5q10 0 19.5 5t15.5 15l370 640q6 10 5.5 20.5T885-140q-5 9-14 14.5t-20 5.5H109Zm371-120q17 0 28.5-11.5T520-280q0-17-11.5-28.5T480-320q-17 0-28.5 11.5T440-280q0 17 11.5 28.5T480-240Zm0-120q17 0 28.5-11.5T520-400v-120q0-17-11.5-28.5T480-560q-17 0-28.5 11.5T440-520v120q0 17 11.5 28.5T480-360Z"/></svg>
                        {{ __('Critical Alerts') }}
                    </h3>
                    <div class="space-y-3">
                        @if($userStats->overtraining_detected)
                        <div class="rounded-xl p-4">
                            <div class="flex items-start">
                                
                                <div>
                                    <h4 class="font-bold text-gray-600 text-lg">{{ __('Overtraining Risk Detected') }}</h4>
                                    <p class="text-gray-400 mt-1">{{ __('Your acute/chronic ratio is') }} <span class="font-bold text-red-500">{{ number_format($userStats->acute_chronic_ratio, 2) }}</span>. {{ __('Take rest days to prevent injury!') }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($userStats->plateau_detected)
                        <div class=" rounded-xl p-4">
                            <div class="flex items-start">
                                
                                <div>
                                    <h4 class="font-bold text-red-500 text-lg">{{ __('Plateau Detected') }}</h4>
                                    <p class="text-gray-400 mt-1">{{ __('No progression for') }} <span class="font-bold text-red-500">{{ $userStats->plateau_weeks }}</span> {{ __('weeks. Time to change your training approach!') }}</p>
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
                
                <div class="bg-gray-50 rounded-3xl p-8 border border-gray-400/20">
                    <h2 class="text-3xl font-bold text-gray-700 mb-2 flex items-center">
                        <span class="text-4xl mr-3 "><svg class="text-gray-600 w-14 h-14" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960"  fill="currentColor"><path d="M360-400v-160q0-17 11.5-28.5T400-600h160q17 0 28.5 11.5T600-560v160q0 17-11.5 28.5T560-360H400q-17 0-28.5-11.5T360-400Zm80-40h80v-80h-80v80Zm-80 280v-40h-80q-33 0-56.5-23.5T200-280v-80h-40q-17 0-28.5-11.5T120-400q0-17 11.5-28.5T160-440h40v-80h-40q-17 0-28.5-11.5T120-560q0-17 11.5-28.5T160-600h40v-80q0-33 23.5-56.5T280-760h80v-40q0-17 11.5-28.5T400-840q17 0 28.5 11.5T440-800v40h80v-40q0-17 11.5-28.5T560-840q17 0 28.5 11.5T600-800v40h80q33 0 56.5 23.5T760-680v80h40q17 0 28.5 11.5T840-560q0 17-11.5 28.5T800-520h-40v80h40q17 0 28.5 11.5T840-400q0 17-11.5 28.5T800-360h-40v80q0 33-23.5 56.5T680-200h-80v40q0 17-11.5 28.5T560-120q-17 0-28.5-11.5T520-160v-40h-80v40q0 17-11.5 28.5T400-120q-17 0-28.5-11.5T360-160Zm320-120v-400H280v400h400ZM480-480Z"/></svg>
                   </span>
                        {{ __('Technical Analysis') }}
                    </h2>
                    <p class="text-gray-400 mb-8">{{ __('How you climb, not just what you succeed') }}</p>

                    <div class="space-y-8">
                        <!-- Consistency: Wavy Line -->
                        <div class="relative p-6 bg-gray-200/50 rounded-2xl border border-blue-400/10">
                            <h3 class="text-xl font-semibold text-gray-600 mb-4">{{ __('Consistency') }}</h3>
                            <div class="flex items-end justify-between">
                                <div>
                                    <div class="text-4xl font-bold text-blue-400">
                                        {{ $userStats->consistency_variance !== null ? number_format($userStats->consistency_variance, 1) : 'N/A' }}
                                    </div>
                                    <p class="text-sm text-gray-400 mt-1">{{ __('Variance • Lower is better') }}</p>
                                </div>
                                <div class="flex-1 ml-8">
                                    <!-- Wavy line visualization -->
                                    <svg class="w-full h-20 text-blue-400" viewBox="0 0 400 80" preserveAspectRatio="none">
                                        
                                        @php
                                            $amplitude = $userStats->consistency_variance ? min($userStats->consistency_variance / 10, 30) : 5;
                                            $path = "M 0 40 ";
                                            for($i = 0; $i <= 400; $i += 10) {
                                                $y = 40 + sin($i / 20) * $amplitude;
                                                $path .= "L $i $y ";
                                            }
                                        @endphp
                                        <path d="{{ $path }}" fill="none" stroke="currentColor" stroke-width="3" opacity="0.8"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Flash/Work Ratio: Horizontal Slider -->
                        <div class="relative p-6 bg-gray-200/50 rounded-2xl border border-orange-400/10">
                            <h3 class="text-xl font-semibold text-gray-600 mb-4">{{ __('Climbing Style') }}</h3>
                            
                            @php
                                    $ratio = $userStats->flash_work_ratio ?? 1;
                                    $position = min(max(($ratio / 3) * 100, 0), 100);
                                @endphp
                            <svg class="hidden"><symbol id="gradient-color-stop" viewBox="0 0 32 34"><path d="M1 4a4 4 0 0 1 4-4h22a4 4 0 0 1 4 4v19.6a4 4 0 0 1-2.118 3.53L16 34 3.118 27.13A4 4 0 0 1 1 23.6V4Z"></path><path fill="none" stroke="#000" stroke-opacity=".05" d="M5 .5h22A3.5 3.5 0 0 1 30.5 4v19.6a3.5 3.5 0 0 1-1.853 3.088L16 33.433 3.353 26.688A3.5 3.5 0 0 1 1.5 23.6V4A3.5 3.5 0 0 1 5 .5Z"></path></symbol></svg>

                            <div class="mx-5">
                                <div class="relative h-[3.625rem]">
                                    <div class="absolute top-0  flex h-18 flex-col items-center -ml-4" style="left: {{ $position }}%;">
                                        <svg viewBox="0 0 32 34" class="w-8 flex-none fill-blue-800 drop-shadow"><use href="#gradient-color-stop"></use>
                                        </svg>
                                        <div class="mt-2 h-2 w-0.5 bg-gray-900/30">
                                        </div>
                                        <div class="absolute top-0 left-0 flex h-8 w-full items-center justify-center font-mono text-[1rem] font-semibold tracking-wider text-white">{{ number_format($ratio, 0) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="h-10 rounded-lg bg-gradient-to-r from-indigo-400  to-blue-400">
                                </div>
                                <div class="flex justify-between text-sm text-gray-400 mb-2">
                                    <span>{{ __('Methodical') }}</span>
                                    <span>{{ __('Explosive') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 space-x-8">
                        <!-- Abandonment Rate: Ghost Hold -->
                            <div class="relative p-6 bg-gray-200/50 rounded-2xl border border-gray-700/10">
                                <h3 class="text-xl font-semibold text-gray-600 mb-4">{{ __('Abandonment Rate') }}</h3>
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="text-4xl font-bold  {{ $userStats->risk_profile_abandonment_rate > 40 ? 'text-red-500' : ($userStats->risk_profile_abandonment_rate > 20 ? 'text-pink-500' : 'text-indigo-500') }}">
                                            {{ $userStats->risk_profile_abandonment_rate !== null ? number_format($userStats->risk_profile_abandonment_rate, 1) . '%' : 'N/A' }}
                                        </div>
                                        <p class="text-sm text-gray-400 mt-1">{{ __('Routes abandoned') }}</p>
                                    </div>
                                    <div class="ml-8">
                                        <!-- Ghost Hold SVG -->
                                        <svg
                                        version="1.1"
                                        id="svg1"
                                        class="h-32 w-32"
                                        viewBox="0 0 171.20456 182.4651"
                                        xmlns="http://www.w3.org/2000/svg"
                                        xmlns:svg="http://www.w3.org/2000/svg">
                                        <defs
                                            id="defs1" />
                                        <g
                                            id="g1"
                                            transform="translate(2.6586854,-12.560471)">
                                            <path class="text-gray-500"
                                            style="fill:currentColor;fill-opacity:0.521699;stroke:none"
                                            d="M 69,13.46373 C 62.46825,14.61788 55.81651,16.66312 50,19.86496 10.2697,41.73549 22.05693,89.98555 12.26543,127 c -3.33758,12.61694 -21.30565,37.74644 -12.537797,49.8912 7.765207,10.75597 17.716757,-1.5039 26.233017,2.22994 8.42284,3.69289 11.91174,13.02537 22.03935,15.34953 11.04195,2.534 17.8664,-4.36065 28,-5.62344 5.14346,-0.64096 9.56716,3.30707 15,2.12422 3.81161,-0.82986 7.05746,-4.42932 11,-4.41513 9.01051,0.0325 14.46274,9.99983 25,8.11421 9.21361,-1.64877 10.79211,-11.16653 18.10417,-14.54939 7.74729,-3.5842 18.12209,6.26483 22.73303,-6.125 2.56549,-6.8936 -2.42794,-13.2726 -5.52856,-18.99614 -5.23766,-9.66843 -8.88957,-20.3196 -11.42438,-31 C 142.13255,87.12475 149.82867,37.71335 109,18.32021 103.64532,15.7768 97.8828,14.05382 92,13.28935 84.87779,12.36382 76.07762,12.21314 69,13.46373 m 4,4.82562 c 6.86157,-0.89166 14.25401,-0.51318 21,0.9213 50.80096,10.80238 42.65979,67.79705 52.12962,106.78935 2.71233,11.16806 6.27472,21.80792 11.65125,32 2.1651,4.10431 6.78241,9.77982 4.47299,14.72067 -3.049,6.52318 -13.26077,-0.64267 -18.2392,1.06174 -8.34146,2.85574 -8.98527,14.52917 -19.01466,15.84799 -9.23772,1.21471 -14.38609,-9.39323 -23,-9.22763 -4.79738,0.0922 -8.29202,4.34593 -13,4.87964 -5.23955,0.59396 -9.53456,-3.0437 -15,-1.67207 -9.43374,2.36753 -15.77137,8.01684 -26,5.08873 C 39.32192,186.21484 36.44826,177.93781 28.99614,174.17902 21.92018,170.60995 8.85656,180.75732 4.17901,172.85185 1.403663,168.16125 4.11121,162.42378 6.00077,158 10.37927,147.74945 14.90533,137.87589 17.625,127 26.43108,91.78478 15.29082,41.05643 56,23.03935 c 5.3724,-2.37771 11.16989,-3.99237 17,-4.75 M 56.00077,59.61574 C 41.07233,63.05399 42.74359,98.14479 59.99614,94.59105 75.20888,91.45748 72.28322,55.86565 56.00077,59.61574 m 45.00309,0.0772 c -14.39194,3.85879 -11.75116,39.24509 4.99537,34.8696 14.48618,-3.78491 11.8086,-39.37511 -4.99537,-34.8696 M 94,102 c -4.45711,2.25605 -7.52709,8.24583 -13,8.17284 -3.87788,-0.0517 -9.80966,-8.69641 -12.39969,-6.99691 -3.77398,2.47638 2.46373,8.77597 4.41821,10.12114 C 81.41912,119.07874 96.76037,113.4288 94,102 Z"
                                            id="path1" />
                                        </g>
                                        </svg>

                                    </div>
                                </div>
                            </div>

                            <!-- Endurance vs Power: Dual Bars -->
                            <div class="relative p-6 bg-gray-200/50 rounded-2xl border border-green-400/10">
                                <h3 class="text-xl font-semibold text-gray-600 mb-6">{{ __('Endurance vs Power') }}</h3>
                                <div class="flex justify-around items-end h-48">
                                    <div class="flex flex-col flex-1 mx-4 h-full justify-end items-center">
                                        <div class="text-2xl font-bold text-indigo-500 mb-2 font-mono">{{ $userStats->long_routes_count ?? 0 }}</div>
                                        @php 
                                        $longHeight = min(($userStats->long_routes_count ?? 0) * 10, 100); 
                                        if ($longHeight < 5){
                                            $longHeight = 5;
                                        }
                                        @endphp
                                        <div class="w-full bg-[#1E1E1E] rounded-t-xl overflow-hidden" style="height: {{ $longHeight }}%;">
                                            <div class="w-full h-full bg-indigo-300"></div>
                                        </div>
                                        <p class="text-sm text-gray-400 mt-2">{{ __('Long Routes') }}</p>
                                    </div>
                                    <div class="flex flex-col flex-1 mx-4 h-full justify-end items-center" >
                                        <div class="text-2xl font-bold text-blue-500 mb-2 font-mono">{{ $userStats->short_routes_count ?? 0 }}</div>
                                        @php 
                                        $shortHeight = min(($userStats->short_routes_count ?? 0) * 10, 100); 
                                        if ($shortHeight < 5){
                                            $shortHeight = 5;
                                        }
                                        @endphp
                                        <div class="w-full bg-[#1E1E1E] rounded-t-xl overflow-hidden" style="height: {{ $shortHeight }}%;">
                                            <div class="w-full h-full bg-blue-300"></div>
                                        </div>
                                        <p class="text-sm text-gray-400 mt-2">{{ __('Short Routes') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Movement Preferences: Radar Chart -->
                        @if($userStats->movement_preferences && count($userStats->movement_preferences) > 0)
                        <div class="relative p-6 bg-gray-200/50 rounded-2xl border border-orange-400/10">
                            <h3 class="text-xl font-semibold text-gray-600 mb-6">{{ __('Movement Preferences') }}</h3>
                            <div class="flex items-center">
                                <div class="w-64 h-64">
                                    <canvas id="radarChart" class="w-full h-full"></canvas>
                                </div>
                                <div class="flex-1 ml-8">
                                    <div class="grid grid-cols-2 gap-3">
                                        @foreach(array_slice($userStats->movement_preferences, 0, 6, true) as $tag => $count)
                                            <div class="flex items-center justify-between p-3 bg-gray-300 rounded-lg">
                                                <span class="text-gray-600 capitalize">{{ $tag }}</span>
                                                <span class="font-bold text-indigo-500">{{ $count }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        

                         @assets
 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 @endassets

                       @script
                        <script>
                                const ctx = document.getElementById('radarChart');
                                if (ctx) {
                                    // Get movement preferences from Livewire
                                    let data = $wire.movement_preferences;;
                                    let labels = Object.keys(data);
                                    let values = Object.values(data);
                                    // Destroy previous chart instance if exists
                                    if (window.radarChartInstance) {
                                        window.radarChartInstance.destroy();
                                    }
                                    window.radarChartInstance = new Chart(ctx, {
                                        type: 'radar',
                                        data: {
                                            labels: labels,
                                            datasets: [{
                                                label: 'Movement Preferences',
                                                data: values,
                                                backgroundColor: 'rgba(255, 111, 60, 0.5)',
                                                borderColor: '#615fff',
                                                pointBackgroundColor: '#3C91E6',
                                                pointBorderColor: '#fff',
                                                pointHoverBackgroundColor: '#fff',
                                                pointHoverBorderColor: '#FF6F3C',
                                            }]
                                        },
                                        options: {
                                            responsive: true,
                                            plugins: {
                                                legend: {
                                                    display: false
                                                },
                                            },
                                            scales: {
                                                r: {
                                                    angleLines: { color: '#3C91E6' },
                                                    grid: { color: '#3C91E6', lineWidth: 0.5 },
                                                    pointLabels: { color: '#fff', font: { size: 14 } },
                                                    ticks: {
                                                        color: '#fff',
                                                        backdropColor: 'transparent',
                                                        stepSize: 1,
                                                        beginAtZero: true,
                                                        z: 1
                                                    },
                                                    min: 0,
                                                    max: Math.max(...values, 5)
                                                }
                                            }
                                        }
                                    });
                                }
                        </script>
                        @endscript

                        @endif
                    </div>
                </div>
            </section>

            <!-- BEHAVIORAL ANALYSIS -->
            <section class="relative">
                 
                <div class="bg-gray-50 rounded-3xl p-8 border border-green-400/20">
                    <h2 class="text-3xl font-bold text-gray-600 mb-2 flex items-center">
                        <span class="text-4xl mr-3"><svg class="w-14 h-14"  xmlns="http://www.w3.org/2000/svg"  viewBox="0 -960 960 960"fill="currentColor"><path d="m447-426 2 26q.9 7.11 5.85 11.56Q459.8-384 467-384h26q7.2 0 12.15-4.44 4.95-4.45 5.85-11.56l2-26q11.43-3.82 20.71-8.91Q543-440 552-448l23 10q6.44 3 12.88 1.06T598-445l13-22q4-6 2-13t-6.69-10.75L586-505q2-11.5 2-23t-2-23l20.31-14.25Q611-569 613-576t-2-13l-13-22q-3.68-6.13-10.12-8.06Q581.44-621 575-618l-23 10q-8-8-18-13t-21-9l-2-26q-.9-7.11-5.85-11.56Q500.2-672 493-672h-26q-7.2 0-12.15 4.44-4.95 4.45-5.85 11.56l-2 26q-11.43 3.82-20.71 8.91Q417-616 408-608l-23-10q-6.44-3-12.88-1.06-6.44 1.93-10.12 8.06l-13 22q-4 6-2 13t6.69 10.75L374-551q-2 11.5-2 23t2 23l-20.31 14.25Q349-487 347-480t2 13l13 22q3.68 6.12 10.12 8.06Q378.56-435 385-438l23-10q8 8 18 13t21 9Zm33-54q-20 0-34-14t-14-34q0-20 14-34t34-14q20 0 34 14t14 34q0 20-14 34t-34 14ZM264-271q-57-48-88.5-115.57T144-529q0-139.58 98.29-237.29Q340.58-864 481-864q109 0 196 58.5T792-653l66 223q5 17.48-5.5 31.74Q842-384 824-384h-56v120q0 29.7-21.15 50.85Q725.7-192 696-192h-96v60q0 15.3-10.29 25.65Q579.42-96 564.21-96t-25.71-10.35Q528-116.7 528-132v-96q0-15.3 10.35-25.65Q548.7-264 564-264h132v-156q0-15.3 10.35-25.65Q716.7-456 732-456h44l-52-173q-22-72-89.5-117.5T481-792q-111 0-188 76.63T216-529q0 58.93 25 111.96Q266-364 311-326l25 22v172q0 15.3-10.29 25.65Q315.42-96 300.21-96t-25.71-10.35Q264-116.7 264-132v-139Zm232-173Z"/></svg>
                    </span>
                        {{ __('Behavioral Analysis') }}
                    </h2>
                    <p class="text-gray-400 mb-8">{{ __('Your climbing habits and patterns') }}</p>

                    <div class="grid md:grid-cols-6 gap-6">
                        <!-- Preferred Hour: 24h Ring -->
                        <div class="p-6 bg-gray-200/50 rounded-2xl border border-green-400/10 md:col-span-2">
                            <h3 class="text-xl font-semibold text-gray-600 mb-4">{{ __('Preferred Hour') }}</h3>
                            <div class="flex items-center justify-center">
                                <div class="relative w-48 h-48">
                                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 200 200">
                                        <!-- 24h ring background -->
                                        <circle class="text-gray-400" cx="100" cy="100" r="72" fill="none" stroke="currentColor" stroke-width="20"/>
                                        
                                        <!-- Highlighted segment -->
                                        @php
                                            $hour = $userStats->preferred_climbing_hour ? (int)substr($userStats->preferred_climbing_hour, 0, 2) : 18;
                                            $startAngle = ($hour * 15) - 7.5;
                                            $endAngle = ($hour * 15) + 7.5;
                                            $start = deg2rad($startAngle);
                                            $end = deg2rad($endAngle);
                                            $largeArc = ($endAngle - $startAngle) > 180 ? 1 : 0;
                                            $startX = 100 + 82 * cos($start);
                                            $startY = 100 + 82 * sin($start);
                                            $endX = 100 + 82 * cos($end);
                                            $endY = 100 + 82 * sin($end);
                                        @endphp
                                        <path d="M 100 100 L {{ $startX }} {{ $startY }} A 80 80 0 {{ $largeArc }} 1 {{ $endX }} {{ $endY }} Z" fill="currentColor" opacity="0.8" class="text-indigo-500"/>
                                        <circle class="text-gray-200" cx="100" cy="100" r="62" fill="currentColor" stroke="none" stroke-width="20"/>
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="text-center">
                                            <div class="text-3xl font-bold text-indigo-500">{{ $userStats->preferred_climbing_hour ?? 'N/A' }}</div>
                                            <p class="text-xs text-gray-400">{{ __('Peak Time') }}</p>
                                             <span class="absolute left-1/2 -translate-x-1/2 -top-3 text-xs text-gray-500 font-semibold select-none">0h</span>
                                    <span class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-3 text-xs text-gray-500 font-semibold select-none">6h</span>
                                    <span class="absolute left-1/2 -translate-x-1/2 -bottom-3 text-xs text-gray-500 font-semibold select-none">12h</span>
                                    <span class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-3 text-xs text-gray-500 font-semibold select-none">18h</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Avg Session: Progress Bar -->
                        <div class="p-6 bg-gray-200/50 rounded-2xl border border-blue-400/10 md:col-span-4">
                            <h3 class="text-xl font-semibold text-gray-600 mb-4">{{ __('Average Session') }}</h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm text-gray-400">{{ __('Duration') }}</span>
                                        <span class="font-bold text-indigo-500">{{ $userStats->avg_session_duration ? number_format($userStats->avg_session_duration, 1) . 'h' : 'N/A' }}</span>
                                    </div>
                                    @php $durationPercent = min(($userStats->avg_session_duration ?? 0) * 20, 100); @endphp
                                    <div class="h-4 bg-gray-400 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-500 transition-all duration-500" style="width: {{ $durationPercent }}%"></div>
                                    </div>
                                    @php
                                    $routesPerSession = $userStats->avg_routes_per_session ?? 0;
                                    $holds = 5;
                                @endphp
                                    <div class="flex justify-between mb-2 mt-6">
                                        <span class="text-sm text-gray-400">{{ __('Average Routes') }}</span>
                                        <span class="font-bold text-indigo-500">{{ number_format($routesPerSession, 1) }}</span>
                                    </div>
                                    <div class="flex justify-around items-end h-16">
                                        
                                
                                @for($i = 1; $i <= $holds; $i++)
                                    @php
                                        $filled = $routesPerSession >= ($i * 5);
                                        $partial = $routesPerSession > (($i-1) * 5) && $routesPerSession < ($i * 5);
                                        $opacity = $filled ? 1 : ($partial ? ($routesPerSession - (($i-1) * 5)) / 5 : 0.2);
                                    @endphp
                                    <div class="flex flex-col items-center">
                                        <!-- Climbing Hold Shape -->
                                        <svg class="w-16 h-16" viewBox="0 0 100 100">
                                            <ellipse class="text-indigo-500" cx="50" cy="50" rx="40" ry="35" fill="currentColor" opacity="{{ $opacity }}" transform="rotate({{ rand(-15, 15) }} 50 50)"/>
                                            <circle cx="50" cy="50" r="8" fill="#000" opacity="{{ $opacity * 0.5 }}"/>
                                        </svg>
                                    </div>
                                @endfor
                            </div>
                            

                                </div>
                            </div>
                        </div>

                        <!-- Routes/Session: Climbing Holds -->
                        

                        <!-- Explorer Score: Compass -->
                        <div class="p-6 bg-gray-200/50 rounded-2xl border border-green-400/10 md:col-span-3">
                            <h3 class="text-xl font-semibold text-gray-600 mb-4">{{ __('Explorer Score') }}</h3>
                            <div class="flex items-center justify-center">
                                <div class="relative w-32 h-32">
                                    <svg class="w-full h-full" viewBox="0 0 200 200">
                                        <!-- Background circle -->
                                        <circle cx="100" cy="100" r="90" fill="none" stroke="#3C91E6" stroke-width="3" opacity="0.2"/>
                                        <circle cx="100" cy="100" r="70" fill="none" stroke="#3C91E6" stroke-width="1" opacity="0.1"/>
                                        <!-- Progress arc -->
                                        @php
                                            $exploration = $userStats->exploration_ratio ?? 0;
                                            $percent = max(0, min($exploration, 100));
                                            $r = 90;
                                            $cx = 100;
                                            $cy = 100;
                                            $circumference = 2 * M_PI * $r;
                                            $arcLength = $circumference * ($percent / 100);
                                            $dashArray = $arcLength . ',' . ($circumference - $arcLength);
                                        @endphp
                                        <circle
                                            cx="100" cy="100" r="90"
                                            fill="none"
                                            stroke="#615fff"
                                            stroke-width="12"
                                            stroke-linecap="round"
                                            stroke-dasharray="{{ $dashArray }}"
                                            stroke-dashoffset="0"
                                            transform="rotate(-90 100 100)"
                                        />
                                        <!-- Compass needle at 45° -->
                                        <g transform="rotate(45 100 100)">
                                            <path class="text-red-700" d="M 100 40 L 105 100 L 100 95 L 95 100 Z" fill="currentColor"/>
                                            <path class="text-gray-300" d="M 100 160 L 105 100 L 100 105 L 95 100 Z" fill="currentColor" opacity="0.5"/>
                                        </g>
                                        <circle cx="100" cy="100" r="8" fill="#F5F5F5"/>
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="text-center mt-8">
                                            <div class="text-2xl font-bold text-indigo-500">{{ $userStats->exploration_ratio ? number_format($userStats->exploration_ratio, 0) . '%' : 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Active Projects & Tenacity Combined Card -->
                        <div class="p-6 bg-gray-200/50 rounded-2xl border border-yellow-400/10 md:col-span-3">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-8">
                                <!-- Active Projects -->
                                <div class="flex-1 flex flex-col items-center md:items-start">
                                    <h3 class="text-xl font-semibold text-gray-600 mb-2">{{ __('Active Projects') }}</h3>
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-4xl font-bold text-indigo-500">{{ $userStats->project_count ?? 0 }}</div>
                                            <p class="text-sm text-gray-400 mt-1">{{ __('Multi-session routes') }}</p>
                                        </div>
                                        
                                    </div>
                                </div>
                                <!-- Divider -->
                                <div class="hidden md:block w-px h-20 bg-gray-700/30 mx-8"></div>
                                <!-- Tenacity -->
                                <div class="flex-1 flex flex-col items-center md:items-start">
                                    <h3 class="text-xl font-semibold text-gray-600 mb-2">{{ __('Tenacity') }}</h3>
                                    <div class="flex items-end">
                                        <div>
                                            <div class="text-4xl font-bold text-pink-500">{{ $userStats->avg_attempts_before_success ? number_format($userStats->avg_attempts_before_success, 1) : 'N/A' }}</div>
                                            <p class="text-sm text-gray-400 mt-1">{{ __('Avg attempts') }}</p>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- PROGRESSION ANALYSIS -->
            <section class="relative">
                
                <div class="bg-gray-50 rounded-3xl p-8 border border-green-400/20">
                    <h2 class="text-3xl font-bold text-gray-600 mb-2 flex items-center">
                        <span class="text-4xl mr-3"><svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg></span>
                        {{ __('Progression Analysis') }}
                    </h2>
                    <p class="text-gray-400 mb-8">{{ __('Track your improvement over time') }}</p>

                    <div class="space-y-6">
                        <!-- Progression Rate: Diagonal Slope -->
                        <div class="p-6 bg-gray-200/50 rounded-2xl border border-green-400/10">
                            <h3 class="text-xl font-semibold text-gray-600 mb-4">{{ __('Progression Rate') }}</h3>
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-4xl font-bold {{ $userStats->progression_rate && $userStats->progression_rate > 0 ? 'text-indigo-500' : 'text-gray-600' }}">
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

                      
                        

                        <!-- Progression by Style: Colored Holds -->
                        @if($userStats->progression_by_style)
                        <div class="p-6 bg-gray-200/50 rounded-2xl border border-blue-400/10">
                            <h3 class="text-xl font-semibold text-gray-600 mb-6">{{ __('Progression by Style') }}</h3>
                            <div class="grid grid-cols-3 gap-6">
                                @php
                                    $colors = ['text-indigo-500', 'text-pink-500', 'text-purple-500']
                                @endphp
                                @foreach($userStats->progression_by_style as $style => $rate)
                                @php
                                    $color = $colors[rand(0, 2)]
                                @endphp
                                    <div class="text-center">
                                        <!-- Climbing Hold -->
                                        <svg class="w-20 h-20 mx-auto mb-3" viewBox="0 0 100 100">
                                            <ellipse class="{{ $rate > 0 ? $color : 'text-red-500' }}" cx="50" cy="50" rx="35" ry="40" fill="currentColor" opacity="0.8" transform="rotate({{ rand(-20, 20) }} 50 50)"/>
                                            <circle cx="50" cy="50" r="6" fill="#000" opacity="0.3"/>
                                        </svg>
                                        <p class="text-sm text-gray-400 capitalize mb-1">{{ $style }}</p>
                                        <p class="font-bold  {{ $rate > 0 ? $color : 'text-red-500' }}">
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
                
                <div class="bg-gray-50 rounded-3xl p-8 border border-blue-400/20">
                    <h2 class="text-3xl font-bold text-gray-600 mb-2 flex items-center">
                        <span class="text-4xl mr-3"><svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg></span>
                        {{ __('Training Load') }}
                    </h2>
                    <p class="text-gray-400 mb-8">{{ __('Optimize your training and recovery') }}</p>

                    <div class="grid md:grid-cols-2 gap-6">
                        

                        <!-- Weekly Intensity: Thermometer -->
                        <div class="p-6 bg-gray-200/50 rounded-2xl border border-red-400/10">
                            <h3 class="text-xl font-semibold text-gray-600 mb-4">{{ __('Weekly Intensity') }}</h3>
                            <div class="flex items-center justify-between">
                                <div class="flex-1 mr-8">
                                    <!-- Thermometer -->
                                    <div class="relative h-48">
                                        <div class="absolute bottom-0 left-1/2 -ml-6 w-12 h-full border-4 border-[#2D2D2D] rounded-full overflow-hidden">
                                            @php $intensityPercent = min(($userStats->weekly_intensity ?? 0) / 10, 100); @endphp
                                            <div class="absolute bottom-0 w-full bg-indigo-500  transition-all duration-500" style="height: {{ $intensityPercent }}%;"></div>
                                        </div>
                                        <div class="absolute bottom-0 left-1/2 -ml-8 w-16 h-16 bg-[#E63946] rounded-full border-4 border-[#2D2D2D]"></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-3xl font-bold text-indigo-500">{{ $userStats->weekly_intensity ? number_format($userStats->weekly_intensity) : 'N/A' }}</div>
                                    <p class="text-sm text-gray-400 mt-1">{{ __('Avg difficulty') }}</p>
                                    <div class="text-3xl font-bold text-blue-500">{{ $userStats->weekly_volume ? number_format($userStats->weekly_volume) : 'N/A' }}</div>
                                    <p class="text-sm text-gray-400 mt-1">{{ __('Weekly Volume') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- A/C Ratio: Triangle -->
                        <div class="p-6 bg-gray-200/50 rounded-2xl border border-blue-400/10">
                            <h3 class="text-xl font-semibold text-gray-600 mb-4">{{ __('Acute/Chronic Ratio') }}</h3>
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-4xl font-bold {{ $userStats->acute_chronic_ratio && $userStats->acute_chronic_ratio > 1.5 ? 'text-red-500' : ($userStats->acute_chronic_ratio && $userStats->acute_chronic_ratio < 0.8 ? 'text-pink-500' : 'text-purple-500') }}">
                                        {{ $userStats->acute_chronic_ratio ? number_format($userStats->acute_chronic_ratio, 2) : 'N/A' }}
                                    </div>
                                    <p class="text-sm text-gray-400 mt-1">{{ __('A/C Ratio : Sweet spot: 0.8-1.3') }}</p>
                                    <div class="text-4xl font-bold text-indigo-500 mt-2">{{ $userStats->avg_recovery_time ? number_format($userStats->avg_recovery_time, 1) . 'h' : 'N/A' }}</div>
                                    <p class="text-sm text-gray-400 mt-1">{{ __('Between sessions') }}</p>
                                </div>
                                <div class="ml-8">
                                    <!-- Triangle visualization -->
                                    <svg class="w-32 h-32" viewBox="0 0 100 100">
                                        @php
                                            $ratio = $userStats->acute_chronic_ratio ?? 1;
                                           
                                            $height = min($ratio * 40, 90);
                                        @endphp
                                        <polygon points="50,{{ 90 - $height }} 20,90 80,90" fill="currentColor" class="{{ $userStats->acute_chronic_ratio && $userStats->acute_chronic_ratio > 1.5 ? 'text-red-500' : ($userStats->acute_chronic_ratio && $userStats->acute_chronic_ratio < 0.8 ? 'text-pink-500' : 'text-purple-500') }}"/>
                                        <polygon class="text-blue-500" points="50,10 20,90 80,90" fill="none" stroke="currentColor" stroke-width="2"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Recovery Time: Hourglass -->
                        

                        <!-- Peak Frequency: Mountain -->
                        <div class="p-6 bg-gray-200/50 rounded-2xl border border-pink-400/10 sm:col-span-2">
                            <h3 class="text-xl font-semibold text-gray-600 mb-4">{{ __('Peak Performance Frequency') }}</h3>
                            <div class="flex items-end justify-between">
                                <div>
                                    <div class="text-4xl font-bold text-indigo-500">{{ $userStats->avg_time_between_performances ? number_format($userStats->avg_time_between_performances / 24, 1) . ' days' : 'N/A' }}</div>
                                    <p class="text-sm text-gray-400 mt-1">{{ __('Between peak performances') }}</p>
                                </div>
                                <div class="flex-1 ml-8">
                                    <!-- Mountain peaks -->
                                    <svg class="w-full h-32" viewBox="0 0 400 100" preserveAspectRatio="none">
                                        
                                        <polygon points="0,100 50,30 100,100" fill="currentColor" class="text-indigo-500"/>
                                        <polygon points="120,100 180,20 240,100" fill="currentColor" class="text-indigo-600"/>
                                        <polygon points="260,100 320,40 380,100" fill="currentColor" class="text-indigo-400"/>
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
            <div class="bg-indigo-200/10 border-l-4 border-indigo-400 p-6 rounded-r-2xl backdrop-blur">
                <div class="flex items-start">
                    
                    <div>
                        <h3 class="text-lg font-bold text-gray-600 mb-2">{{ __('Want to learn more?') }}</h3>
                        <p class="text-gray-400">
                            {{ __('For detailed explanations of how each statistic is calculated, see the') }}
                            <a href="https://github.com/paulhenry46/TopoClimb/blob/main/STATS_CALCULATION_DOCUMENTATION.md" target="_blank" class="font-semibold text-indigo-400 hover:text-indigo-800 underline transition-colors">
                                {{ __('Statistics Calculation Documentation') }}
                            </a>
                        </p>
                        <div class="mt-4">
            <h3 class="text-lg font-bold text-gray-600 mb-2">{{ __('Access to legacy statistics') }}</h3>
            <a href="/stats" class="inline-block px-4 py-2 bg-gray-500 text-white text-sm font-semibold rounded hover:bg-gray-600 transition">{{ __('Legacy Stats') }}</a>
        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
