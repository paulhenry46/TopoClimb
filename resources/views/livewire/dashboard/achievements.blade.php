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
            <div class=" border-2 border-yellow-300 rounded-lg p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h4 class="text-sm font-semibold text-gray-900">{{ $achievement->name }}</h4>
                        <p class="text-xs text-gray-600 mt-1">{{ $achievement->description }}</p>
                        @php
                            $unlockedAt = $user->achievements()->where('achievements.id', $achievement->id)->first();
                        @endphp
                        @if($unlockedAt && $unlockedAt->pivot->unlocked_at)
                        <p class="text-xs text-gray-500 mt-2">
                            {{ __('Unlocked at') }} {{ \Carbon\Carbon::parse($unlockedAt->pivot->unlocked_at)->format('d/m/Y') }}
                        </p>
                        @endif
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
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 opacity-75 hover:opacity-100 transition-opacity">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 -960 960 960" class="h-6 w-6 text-gray-400" fill="currentColor"><path d="M240-80q-33 0-56.5-23.5T160-160v-400q0-33 23.5-56.5T240-640h40v-80q0-83 58.5-141.5T480-920q83 0 141.5 58.5T680-720v80h40q33 0 56.5 23.5T800-560v400q0 33-23.5 56.5T720-80H240Zm240-200q33 0 56.5-23.5T560-360q0-33-23.5-56.5T480-440q-33 0-56.5 23.5T400-360q0 33 23.5 56.5T480-280ZM360-640h240v-80q0-50-35-85t-85-35q-50 0-85 35t-35 85v80Z"/></svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h4 class="text-sm font-semibold text-gray-700">{{ $achievement->name }}</h4>
                        <p class="text-xs text-gray-500 mt-1">{{ $achievement->description }}</p>
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
