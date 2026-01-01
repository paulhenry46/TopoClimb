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

<div>
    <div class="mb-6">
        <div class="flex justify-between items-center mb-2">
            <h3 class="text-lg font-semibold text-gray-900">{{ __('Progression') }}</h3>
            <span class="text-sm text-gray-600">{{ $unlockedCount }} / {{ $totalCount }}</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5">
            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $this->getProgressPercentage() }}%"></div>
        </div>
    </div>

    <!-- Unlocked Achievements -->
    @if($unlockedAchievements->count() > 0)
    <div class="mb-6">
        <h3 class="text-md font-semibold text-gray-900 mb-3">{{ __('Réussites obtenues') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($unlockedAchievements as $achievement)
            <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 border border-yellow-300 rounded-lg p-4 shadow-sm">
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
                            {{ __('Débloqué le') }} {{ $unlockedAt->pivot->unlocked_at->format('d/m/Y') }}
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
        <h3 class="text-md font-semibold text-gray-900 mb-3">{{ __('Réussites disponibles') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($lockedAchievements as $achievement)
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 opacity-75 hover:opacity-100 transition-opacity">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                        </svg>
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

    @if($totalCount == 0)
    <div class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
        </svg>
        <p class="mt-2 text-sm text-gray-500">{{ __('Aucune réussite disponible pour le moment') }}</p>
    </div>
    @endif
</div>
