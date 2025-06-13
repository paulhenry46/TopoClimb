<?php

use Livewire\Volt\Component;
use App\Models\User;
new class extends Component {

    public User $user;
    public string $color;
    public $favorite_site_names;

    public function mount(){
      
      $this->user  = auth()->user();
      $hr = $this->user->hr();

      if($hr == 0){
        $this->color = 'amber';
      }elseif($hr == 1){
        $this->color = 'red';
      }elseif($hr == 2){
        $this->color = 'blue';
      }elseif($hr == 3){
        $this->color = 'green';
      }else{
        $this->color = 'gray';
      }

      $this->favorite_site_names = $this->user->favoriteSites()->pluck('name');
    }
}; ?>


<div>

<div class="flex items-center justify-center">
    <div class="">
        <div class='hidden ring-red-500 ring-amber-500 ring-blue-500 ring-green-500 ring-gray-500' >
        </div>
        <div class="rounded-full ring-{{ $this->color }}-500 ring-3 ring-offset-base-100 ring-offset-2">
            <img  class='w-24 rounded-full' src="{{ $this->user->profile_photo_url }}">
        </div>
    </div>
</div>
<div class=" mt-2">
    <h5 class="flex items-center justify-center"><p class="mr-1 text-xl">{{ $this->user->name }}</p>
                <p class="text-{{ $this->color }}-500">
                    @if($this->color !== 'gray')
                <svg fill="currentColor" xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20">
                    <path d="m438-452-58-57q-11-11-27.5-11T324-508q-11 11-11 28t11 28l86 86q12 12 28 12t28-12l170-170q12-12 11.5-28T636-592q-12-12-28.5-12.5T579-593L438-452ZM326-90l-58-98-110-24q-15-3-24-15.5t-7-27.5l11-113-75-86q-10-11-10-26t10-26l75-86-11-113q-2-15 7-27.5t24-15.5l110-24 58-98q8-13 22-17.5t28 1.5l104 44 104-44q14-6 28-1.5t22 17.5l58 98 110 24q15 3 24 15.5t7 27.5l-11 113 75 86q10 11 10 26t-10 26l-75 86 11 113q2 15-7 27.5T802-212l-110 24-58 98q-8 13-22 17.5T584-74l-104-44-104 44q-14 6-28 1.5T326-90Z"></path>
                </svg>
                @endif
            </p>
                                                            
        </h5>
        <div class="flex items-center justify-center mt-1 mb-3">
            <div class="flex items-center">
                        <p >
                        {{ $this->user->created_at->format('d/m/Y') }} -
                        @foreach ($this->favorite_site_names as $name)
                          {{ $name }}
                        @endforeach
                    </p>
                                                                    
                </div>
            </div>
</div>

</div>