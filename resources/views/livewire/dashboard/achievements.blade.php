<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Achievement;
use App\Services\AchievementService;

new class extends Component {

    public User $user;
    public $achievements;
    public $unlockedAchievements;
    public $lockedAchievements;
    public $unlockedCount = 0;
    public $totalCount = 0;

    public function mount(){
        $this->user = auth()->user();
        
        // Get all achievements
        $this->achievements = Achievement::all();
        $this->totalCount = $this->achievements->count();
        
        // Get user's unlocked achievements with unlock dates
        $userAchievementIds = $this->user->achievements()->pluck('achievements.id')->toArray();
        
        // Separate unlocked and locked achievements
        $this->unlockedAchievements = $this->achievements->filter(function($achievement) use ($userAchievementIds) {
            return in_array($achievement->id, $userAchievementIds);
        });
        
        $this->lockedAchievements = $this->achievements->filter(function($achievement) use ($userAchievementIds) {
            return !in_array($achievement->id, $userAchievementIds);
        });
        
        $this->unlockedCount = $this->unlockedAchievements->count();
    }
    
    public function getProgressPercentage()
    {
        if ($this->totalCount == 0) {
            return 0;
        }
        return round(($this->unlockedCount / $this->totalCount) * 100);
    }
}; ?>

<div x-data="{ expanded: false }" class="relative">
    <div class="mb-6">
        <div class="flex justify-between items-center mb-2">
            <h3 class="text-lg font-semibold text-gray-900">{{ __('Progression') }}</h3>
            <span class="text-sm text-gray-600">{{ $unlockedCount }} / {{ $totalCount }}</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5">
            <div class="bg-gray-600 h-2.5 rounded-full" style="width: {{ $this->getProgressPercentage() }}%"></div>
        </div>
    </div>

    <!-- Achievements Collapsible Container -->
    <div
        :class="expanded ? 'max-h-none overflow-visible' : 'max-h-72 overflow-hidden cursor-pointer'"
        class="transition-all duration-300 ease-in-out relative group  rounded-lg"
        @click="expanded = !expanded"
        @keydown.enter.space="expanded = !expanded"
        tabindex="0"
        aria-expanded="false"
    >
    <!-- Unlocked Achievements -->
    @if($unlockedAchievements->count() > 0)
    <div class="mb-6">
        <h3 class="text-md font-semibold text-gray-900 mb-3">{{ __('Unlocked achievements') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($unlockedAchievements as $achievement)
            @php
                // Determine rarity based on percent
                if ($achievement->percent > 0.5) {
                    $rarity = 'common';
                    $borderColor = 'border-teal-600';
                    $bgColor = 'bg-teal-50';
                    $textColor = 'text-teal-600';
                    $badgeBg = 'bg-teal-100';
                    $badgeText = 'text-teal-700';
                    $rarityLabel = __('Common');
                } elseif ($achievement->percent > 0.1) {
                    $rarity = 'rare';
                    $borderColor = 'border-pink-600';
                    $bgColor = 'bg-pink-50';
                    $textColor = 'text-pink-600';
                    $badgeBg = 'bg-pink-100';
                    $badgeText = 'text-pink-700';
                    $rarityLabel = __('Rare');
                } elseif ($achievement->percent > 0.05) {
                    $rarity = 'epic';
                    $borderColor = 'border-purple-600';
                    $bgColor = 'bg-purple-50';
                    $textColor = 'text-purple-600';
                    $badgeBg = 'bg-purple-100';
                    $badgeText = 'text-purple-700';
                    $rarityLabel = __('Epic');
                } else {
                    $rarity = 'legendary';
                    $borderColor = 'border-amber-600';
                    $bgColor = 'bg-amber-50';
                    $textColor = 'text-amber-600';
                    $badgeBg = 'bg-amber-100';
                    $badgeText = 'text-amber-700';
                    $rarityLabel = __('Legendary');
                }
            @endphp
            <div class="border-2 {{ $borderColor }} {{ $bgColor }} rounded-lg p-4 relative">
                <!-- Rarity Badge -->
                <div class="absolute top-2 right-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeBg }} {{ $badgeText }}">
                        {{ $rarityLabel }}
                    </span>
                </div>
                
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 {{ $textColor }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1 pr-16">
                        <h4 class="text-sm font-bold text-gray-900 mb-1">{{ $achievement->name }}</h4>
                        <p class="text-xs text-gray-700 mb-2">{{ $achievement->description }}</p>
                        
                        <!-- Stats section -->
                        <div class="flex flex-col gap-1 mt-2 pt-2 border-t border-gray-200">
                            <div class="flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <span class="text-xs text-gray-600">
                                    {{ __('Unlocked by :percent% of users', ['percent' => number_format($achievement->percent * 100, 1)]) }}
                                </span>
                            </div>
                            @php
                                $unlockedAt = $user->achievements()->where('achievements.id', $achievement->id)->first();
                            @endphp
                            @if($unlockedAt && $unlockedAt->pivot->unlocked_at)
                            <div class="flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-xs text-gray-600">
                                    {{ __('Unlocked at') }} {{ \Carbon\Carbon::parse($unlockedAt->pivot->unlocked_at)->format('d/m/Y') }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Locked Achievements -->
    @if($lockedAchievements->count() > 0)
    <div>
        <h3 class="text-md font-semibold text-gray-900 mb-3">{{ __('Available achievements') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($lockedAchievements as $achievement)
            @php
                // Determine rarity badge for locked achievements
                if ($achievement->percent > 0.5) {
                    $rarityLabel = __('Common');
                    $badgeBg = 'bg-gray-200';
                    $badgeText = 'text-gray-600';
                } elseif ($achievement->percent > 0.1) {
                    $rarityLabel = __('Rare');
                    $badgeBg = 'bg-gray-200';
                    $badgeText = 'text-gray-600';
                } elseif ($achievement->percent > 0.05) {
                    $rarityLabel = __('Epic');
                    $badgeBg = 'bg-gray-200';
                    $badgeText = 'text-gray-600';
                } else {
                    $rarityLabel = __('Legendary');
                    $badgeBg = 'bg-gray-200';
                    $badgeText = 'text-gray-600';
                }
            @endphp
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 opacity-75 hover:opacity-100 transition-opacity relative">
                <!-- Rarity Badge (muted for locked achievements) -->
                <div class="absolute top-2 right-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeBg }} {{ $badgeText }}">
                        {{ $rarityLabel }}
                    </span>
                </div>
                
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" class="h-7 w-7 text-gray-400" fill="currentColor">
                            <path d="M240-80q-33 0-56.5-23.5T160-160v-400q0-33 23.5-56.5T240-640h40v-80q0-83 58.5-141.5T480-920q83 0 141.5 58.5T680-720v80h40q33 0 56.5 23.5T800-560v400q0 33-23.5 56.5T720-80H240Zm240-200q33 0 56.5-23.5T560-360q0-33-23.5-56.5T480-440q-33 0-56.5 23.5T400-360q0 33 23.5 56.5T480-280ZM360-640h240v-80q0-50-35-85t-85-35q-50 0-85 35t-35 85v80Z"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1 pr-16">
                        <h4 class="text-sm font-semibold text-gray-700 mb-1">{{ $achievement->name }}</h4>
                        <p class="text-xs text-gray-500 mb-2">{{ $achievement->description }}</p>
                        
                        <!-- Stats section -->
                        <div class="flex items-center gap-1.5 mt-2 pt-2 border-t border-gray-200">
                            <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span class="text-xs text-gray-500">
                                {{ __('Unlocked by :percent% of users', ['percent' => number_format($achievement->percent * 100, 1)]) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    </div>

    @if($totalCount == 0)
    <div class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
        </svg>
        <p class="mt-2 text-sm text-gray-500">{{ __('No achievements for now') }}</p>
    </div>
    @endif
    <!-- Overlay for collapsed state -->
    <template x-if="!expanded">
        <div class="absolute bottom-0 left-0 w-full h-16 bg-gradient-to-t from-white to-transparent pointer-events-none group-hover:from-gray-100 transition-all duration-300"></div>
    </template>
    <!-- Expand/collapse button -->
    <button
        type="button"
        class="absolute bottom-2 left-1/2 -translate-x-1/2 z-10 px-4 py-1 rounded-full bg-gray-600 text-white text-xs font-semibold shadow group-hover:bg-gray-700 transition-all duration-200"
        x-text="expanded ? '{{ __('Fold') }}' : '{{ __('Unfold') }}'"
        @click.stop="expanded = !expanded"
        
        x-cloak
    ></button>
</div>
