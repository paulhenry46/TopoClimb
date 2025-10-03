<?php

use Livewire\Volt\Component;
use App\Models\Contest;
use App\Models\Site;
use App\Models\Route;
use App\Models\User;
use Livewire\Attributes\Validate; 
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component {
  public int $number;
  public Site $site;

      public function mount(Site $site ){
        $this->site = $site;
        $this->number = Contest::where('site_id', $this->site->id)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->count();
      }
}; ?>
<div class="relative inline-block">
    <x-button href="{{ route('admin.contests.manage', ['site' => $this->site->id]) }}" wire:navigate>
        <x-icons.icon-trophy/>
        <p class='ml-2'>{{ __('Contests') }}</p>
        @if($this->number > 0)
            <span class="absolute -top-2 -right-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-gray-600 rounded-full">
                {{ $this->number }}
            </span>
        @endif
    </x-button>
</div>