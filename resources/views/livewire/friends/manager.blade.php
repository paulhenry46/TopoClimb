<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\Attributes\Validate;

new class extends Component {
    #[Validate('required|min:2')]
    public string $search = '';

    public $searchResults = [];
    public $friends = [];

    public function mount()
    {
        $this->loadFriends();
    }

    public function loadFriends()
    {
        $user = auth()->user();
        $this->friends = $user->friends->merge($user->friendOf)->unique('id');
    }

    public function searchUsers()
    {
        $this->validate();

        $user = auth()->user();
        
        $this->searchResults = User::where('name', 'LIKE', '%' . $this->search . '%')
            ->where('id', '!=', $user->id)
            ->limit(10)
            ->get(['id', 'name', 'profile_photo_path'])
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'profile_photo_url' => $u->profile_photo_url,
            ]);
    }

    public function addFriend($friendId)
    {
        $user = auth()->user();

        // Check if already friends
        $alreadyFriends = $user->friends()->where('friend_id', $friendId)->exists() ||
                         $user->friendOf()->where('user_id', $friendId)->exists();

        if (!$alreadyFriends) {
            $user->friends()->attach($friendId);
            $this->loadFriends();
            $this->dispatch('friend-added');
        }

        $this->searchResults = [];
        $this->search = '';
    }

    public function removeFriend($friendId)
    {
        $user = auth()->user();
        $user->friends()->detach($friendId);
        $user->friendOf()->detach($friendId);
        
        $this->loadFriends();
        $this->dispatch('friend-removed');
    }
}; ?>

<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
    <h2 class="text-2xl font-semibold text-gray-900 mb-6">{{ __('Friends') }}</h2>
    
    <!-- Search Section -->
    <div class="mb-6">
        <x-label for="search" value="{{ __('Search Users') }}" />
        <div class="flex gap-2 mt-2">
            <input 
                wire:model="search" 
                type="text" 
                id="search"
                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500"
                placeholder="{{ __('Enter name to search...') }}"
            />
            <x-button wire:click="searchUsers">{{ __('Search') }}</x-button>
        </div>
        <x-input-error for="search" class="mt-2" />
    </div>

    <!-- Search Results -->
    @if(!empty($searchResults))
        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('Search Results') }}</h3>
            <div class="space-y-2">
                @foreach ($searchResults as $result)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <img src="{{ $result['profile_photo_url'] }}" alt="{{ $result['name'] }}" class="h-10 w-10 rounded-full">
                            <span class="font-medium text-gray-900">{{ $result['name'] }}</span>
                        </div>
                        <x-button wire:click="addFriend({{ $result['id'] }})">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
                            </svg>
                            {{ __('Add Friend') }}
                        </x-button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Friends List -->
    <div>
        <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('Your Friends') }}</h3>
        @if($friends->isEmpty())
            <div class="text-center py-8 text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="text-sm">{{ __('You have no friends yet. Search for users above to add friends!') }}</p>
            </div>
        @else
            <div class="space-y-2">
                @foreach ($friends as $friend)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <img src="{{ $friend->profile_photo_url }}" alt="{{ $friend->name }}" class="h-10 w-10 rounded-full">
                            <span class="font-medium text-gray-900">{{ $friend->name }}</span>
                        </div>
                        <x-danger-button wire:click="removeFriend({{ $friend->id }})" wire:confirm="{{ __('Are you sure you want to remove this friend?') }}">
                            {{ __('Remove') }}
                        </x-danger-button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
