<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Log;
new class extends Component {

    public User $user;
    public int $total;
    public string $level_b;
    public string $level_t;

    public function mount(){
      
      $this->user  = auth()->user();
      $logs = Log::where('user_id', $this->user->id)->with('route.line.sector.area');
    
      $this->total = count(array_unique((clone $logs)->get()->pluck('route_id')->toArray()));

    $bouldering_logs = (clone $logs)->whereHas('route.line.sector.area', function ($query) {
        $query->where('type', 'bouldering');
    });

    $trad_logs = (clone $logs)->whereHas('route.line.sector.area', function ($query) {
        $query->where('type', 'trad');
    });


    $route_b = $bouldering_logs->whereHas('route', function ($query) {
        $query->orderBy('grade', 'desc');
    })
    ->with('route')
    ->take(3)
    ->get()
    ->pluck('route')->sortBy('grade')->first();

    $route_t = $trad_logs->whereHas('route', function ($query) {
        $query->orderBy('grade', 'desc');
    })
    ->with('route')
    ->take(3)
    ->get()
    ->pluck('route')->sortBy('grade')->first();
    
    
    if($route_b !== null){
        $this->level_b = $route_b->gradeFormated();
    }else{
        $this->level_b = '3a';
    }

    if($route_t !== null){
        $this->level_t = $route_t->gradeFormated();
    }else{
        $this->level_t = '3a';
    }


    }
}; ?>


<div>
    <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
      <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5  sm:px-6 sm:pt-6">
        <dt>
          <div class="absolute rounded-md bg-gray-500 p-3 text-white">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M480-269 314-169q-11 7-23 6t-21-8q-9-7-14-17.5t-2-23.5l44-189-147-127q-10-9-12.5-20.5T140-571q4-11 12-18t22-9l194-17 75-178q5-12 15.5-18t21.5-6q11 0 21.5 6t15.5 18l75 178 194 17q14 2 22 9t12 18q4 11 1.5 22.5T809-528L662-401l44 189q3 13-2 23.5T690-171q-9 7-21 8t-23-6L480-269Z"/>
            </svg>
          </div>
          <p class="ml-16 truncate text-sm font-medium text-gray-500">{{__('Total climbed') }}</p>
        </dt>
        <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
          <p class="text-3xl font-semibold text-gray-900">{{ $this->total }}</p>
          <div class="absolute inset-x-0 bottom-0 bg-gray-50 px-4 py-4 sm:px-6">
            <div class="text-sm">
              <a href="#" class="font-medium text-gray-600 hover:text-gray-500">{{ __('View all') }}</a>
            </div>
          </div>
        </dd>
      </div>
      <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5  sm:px-6 sm:pt-6">
        <dt>
          <div class="absolute rounded-md bg-gray-500 p-3 text-white">
            <svg
            width="24px"
            height="24px"
            viewBox="0 0 33.982883 33.948418"
            version="1.1"
            id="svg1"
            xmlns="http://www.w3.org/2000/svg"
            xmlns:svg="http://www.w3.org/2000/svg">
           <defs
              id="defs1" />
           <g
              id="layer1"
              transform="translate(-91.545832,-161.39583)">
             <g
                id="g9"
                transform="matrix(0.26458333,0,0,0.26458333,58.003734,148.54369)">
                 <path
            class="st1"
            d="m 210.8,78.5 -53.3,14.8 c 0.1,0.1 0.2,0.2 0.3,0.2 3.9,3.9 3.9,10.1 0.2,14 l 56.5,-15.7 z"
            id="path8"
            style="fill:currentColor" />
         
                 <path
            class="st1"
            d="m 242.3,61.4 c -17.1,-17.1 -45,-17.1 -62.2,0 l 9.8,9.8 c 11.9,-11.9 31.3,-11.7 43,0.5 11.4,11.9 10.8,30.8 -0.8,42.5 L 192,154.3 c -11.9,11.9 -31.3,11.7 -43,-0.5 -11.4,-11.9 -10.8,-30.8 0.8,-42.5 l 5.7,-5.7 c 2.7,-2.7 2.7,-7.1 0,-9.8 -2.7,-2.7 -7.1,-2.7 -9.8,0 l -6.1,6.1 c -17.3,17.3 -17.1,45.6 0.6,62.8 17.3,16.8 45.1,16 62.2,-1 l 40,-40 c 17.1,-17.2 17.1,-45.1 -0.1,-62.3 z m -93.5,41 c -0.9,-0.9 -0.9,-2.3 0,-3.2 0.9,-0.9 2.3,-0.9 3.2,0 0.9,0.9 0.9,2.3 0,3.2 -0.9,0.8 -2.3,0.8 -3.2,0 z"
            id="path9"
            style="fill:currentColor" />
         
             </g>
           </g>
         </svg>
          </div>
          <p class="ml-16 truncate text-sm font-medium text-gray-500">{{__('climbing level') }}</p>
        </dt>
        <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
          <p class="text-3xl font-semibold text-gray-900">{{ $this->level_t }}</p>
          <div class="absolute inset-x-0 bottom-0 bg-gray-50 px-4 py-4 sm:px-6">
            <div class="text-sm">
              <a href="#" class="font-medium text-gray-600 hover:text-gray-500">{{ __('View all') }}</a>
            </div>
          </div>
        </dd>
      </div>
      <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5  sm:px-6 sm:pt-6">
        <dt>
          <div class="absolute rounded-md bg-gray-500 p-3 text-white">
            <svg
   width="24px"
   height="24px"
   viewBox="0 0 35.50708 25.361851"
   version="1.1"
   id="svg1"
   xmlns="http://www.w3.org/2000/svg"
   xmlns:svg="http://www.w3.org/2000/svg">
  <defs
     id="defs1" />
  <g
     id="layer1"
     transform="translate(-93.662501,-163.77708)">
    <path
       class="st1"
       d="m 104.24583,165.09912 c -7.990412,2.9898 -10.583329,8.7048 -10.583329,12.46188 0,12.46187 12.594169,14.12875 18.018129,8.49312 6.37645,-6.64104 17.48895,-2.80458 17.48895,-10.9802 0,-8.22855 -14.12875,-14.02292 -24.92375,-9.9748 z m 5.42396,11.95917 c -1.11125,0 -1.98437,-0.89958 -1.98437,-1.98437 0,-1.11125 0.89958,-1.98438 1.98437,-1.98438 1.08479,0 1.98438,0.89958 1.98438,1.98438 0.0265,1.11124 -0.87313,1.98437 -1.98438,1.98437 z"
       id="path24"
       style="fill:currentColor;stroke-width:0.264583" />
  </g>
</svg>
          </div>
          <p class="ml-16 truncate text-sm font-medium text-gray-500">{{__('bouldering level') }}</p>
        </dt>
        <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
          <p class="text-3xl font-semibold text-gray-900">{{ $this->level_b }}</p>
          <div class="absolute inset-x-0 bottom-0 bg-gray-50 px-4 py-4 sm:px-6">
            <div class="text-sm">
              <a href="#" class="font-medium text-gray-600 hover:text-gray-500">{{ __('View all') }}</a>
            </div>
          </div>
        </dd>
      </div>
    </dl>
  </div>