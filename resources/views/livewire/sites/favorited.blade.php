<?php

use Livewire\Volt\Component;
use App\Models\Site;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
new class extends Component {

    public Site $site;
    public bool $isFavorited;

    public function mount(Site $site)
    {
        $this->site = $site;
        $this->isFavorited = Auth::user()->favoriteSites->contains($site->id);
    }

    public function toggleFavorite()
    {
        $user = Auth::user();

        if ($this->isFavorited) {
            $user->favoriteSites()->detach($this->site->id);
            $this->isFavorited = false;
        } else {
            $user->favoriteSites()->attach($this->site->id);
            $this->isFavorited = true;
        }
    }

}; ?>
<div class='flex justify-end' >
    <button wire:click="toggleFavorite" 
            class="cursor-pointer px-4 py-2 font-semibold text-white rounded-md 
                   {{ $isFavorited ? 'bg-gray-500 hover:bg-gray-600' : 'bg-gray-900 hover:bg-gray-900' }}">
        {{ $isFavorited ? __('Unfavorite') : __('Favorite') }}
    </button>
</div>