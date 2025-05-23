<?php

use Livewire\Volt\Component;
use App\Models\Route;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
new class extends Component {

    public Route $route;
    public bool $isRegistered;

    public function mount(Route $route)
    {
        $this->route = $route;
        $this->isRegistered = Auth::user()->registeredRoutes->contains($route->id);
    }

    public function toggleRegister()
    {
        $user = Auth::user();

        if ($this->isRegistered) {
            $user->registeredRoutes()->detach($this->route->id);
            $this->isRegistered = false;
        } else {
            $user->registeredRoutes()->attach($this->route->id);
            $this->isRegistered = true;
        }
    }

}; ?>
<button wire:click="toggleRegister"  type="button" class="rounded-md bg-gray-800 p-2 text-white shadow-xs hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">
    @if(!$isRegistered)
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
      <path d="m480-240-168 72q-40 17-76-6.5T200-241v-519q0-33 23.5-56.5T280-840h400q33 0 56.5 23.5T760-760v519q0 43-36 66.5t-76 6.5l-168-72Zm0-88 200 86v-518H280v518l200-86Zm0-432H280h400-200Z"></path>
    </svg>
    @else
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
        <path d="m480-240-168 72q-40 17-76-6.5T200-241v-519q0-33 23.5-56.5T280-840h400q33 0 56.5 23.5T760-760v519q0 43-36 66.5t-76 6.5l-168-72Z"/>
    </svg>
    @endif
  </button>