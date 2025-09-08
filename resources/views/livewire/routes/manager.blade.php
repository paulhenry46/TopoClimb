<?php

use Livewire\Volt\Component;
use App\Models\Site;
use App\Models\Area;

use Livewire\Attributes\Computed;
new class extends Component {

    public Site $site;
    public Area $area;

    public function mount($lines, Site $site, Area $area){
      $this->site = $site;
      $this->area = $area;
    }
}; ?>

<div>
  <div class="px-4 sm:px-6 lg:px-8 py-8" >
    <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
        <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Routes')}}</h1>
        <p class="mt-2 text-sm text-gray-700">{{__('Registered routes')}}</p>
      </div>
      <a class='mr-2' href="{{ route('site.area.view', [$this->site->slug, $this->area->slug]) }}" wire:navigate> <x-button type="button">{{__('See routes')}}</x-button></a>
      <a href="{{route('admin.routes.new', ['site' => $this->site->id, 'area' => $this->area->id])}}" wire:navigate> <x-button type="button">{{__('Add route')}}</x-button></a>
    </div>
  </div>
</div>