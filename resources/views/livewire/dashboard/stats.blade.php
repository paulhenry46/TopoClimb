<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Log;
new class extends Component {

    public User $user;
    public int $total;
    public string $level_b;
    public string $level_t;
    public $logs_t;
    public $logs_b;

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


    $this->logs_b = $bouldering_logs->whereHas('route', function ($query) {
        $query->orderBy('grade', 'desc');
    })
    ->with('route')
    ->take(3)
    ->get();
    $route_b = $this->logs_b->pluck('route')->sortBy('grade')->first();

    $this->logs_t = $trad_logs->whereHas('route', function ($query) {
        $query->orderBy('grade', 'desc');
    })
    ->with('route')
    ->take(3)
    ->get();
    $route_t = $this->logs_t->pluck('route')->sortBy('grade')->first();
    
    
    if($route_b !== null and $route_b->gradeFormated() !== null){
        $this->level_b = $route_b->gradeFormated();
    }else{
        $this->level_b = '3a';
    }

    if($route_t !== null and $route_t->gradeFormated() !== null){
        $this->level_t = $route_t->gradeFormated();
    }else{
        $this->level_t = '3a';
    }


    }
}; ?>


<div x-data="{ open_modal_total: false, open_modal_level_ascent: false, open_modal_level_bouldering: false}">
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
              <button x-on:click='open_modal_total = true' class="font-medium text-gray-600 hover:text-gray-500">{{ __('View all') }}</button>
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
          <p class="ml-16 truncate text-sm font-medium text-gray-500">{{__('Climbing level') }}</p>
        </dt>
        <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
          <p class="text-3xl font-semibold text-gray-900">{{ $this->level_t }}</p>
          <div class="absolute inset-x-0 bottom-0 bg-gray-50 px-4 py-4 sm:px-6">
            <div class="text-sm">
              <button x-on:click='open_modal_level_ascent = true' class="font-medium text-gray-600 hover:text-gray-500">{{ __('See more') }}</button>
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
          <p class="ml-16 truncate text-sm font-medium text-gray-500">{{__('Bouldering level') }}</p>
        </dt>
        <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
          <p class="text-3xl font-semibold text-gray-900">{{ $this->level_b }}</p>
          <div class="absolute inset-x-0 bottom-0 bg-gray-50 px-4 py-4 sm:px-6">
            <div class="text-sm">
              <button x-on:click='open_modal_level_bouldering = true' class="font-medium text-gray-600 hover:text-gray-500">{{ __('See more') }}</button>
            </div>
          </div>
        </dd>
      </div>
    </dl>

    <div>
  <div class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-show="open_modal_total" x-cloak>
    <div class="fixed inset-0 bg-gray-500/75 transition-opacity" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
      <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
          <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gray-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-7 w-7"
                  viewBox="0 0 389 515"
                  id="svg2"
                  version="1.1"
                  xmlns="http://www.w3.org/2000/svg"
                  xmlns:svg="http://www.w3.org/2000/svg">
                  <defs
                    id="defs17" />
                  <g
                    id="g1"
                    transform="translate(-984.5876,180.9994)">
                    <path
                      id="path5040"
                      d="m 1185.0503,160.80697 c -4.8166,1.77753 -10.2066,2.80966 -15.7113,2.80966 -5.906,0 -11.7548,-0.68808 -17.1448,-2.23626 l -60.8955,139.96802 c -5.562,12.90161 -20.8719,17.25946 -34.2322,9.74786 -13.303,-7.5116 -19.6105,-23.96832 -13.8764,-36.8699 l 74.6572,-171.21854 h 95.1852 V 87.00982 l 48.1659,-22.42013 c 12.6149,-6.13541 29.0143,4.35788 32.34,17.7182 l 23.6243,94.72641 c 3.3831,13.41766 -4.8166,27.00735 -18.2343,30.3331 -13.475,3.38309 -27.122,-4.81659 -30.4477,-18.23425 l -15.0233,-60.37949 z"
                      style="fill:#231f20;fill-rule:nonzero" />
                    <path
                      id="path5042"
                      d="M 1228.9158,62.69748 C 1333.218,10.97639 1373.643,-166.20556 1373.643,-180.9994 h -14.3351 c -5.39,36.92725 -50.1729,184.80823 -130.3921,227.69889 z"
                      style="fill:#231f20;fill-rule:nonzero" />
                    <path
                      id="path5044"
                      d="m 1036.4812,23.93533 c 5.4474,8.54373 16.4568,12.32819 26.2046,8.31436 l 55.1615,-21.61734 v 76.49216 h 95.1852 V -1.46648 l 85.3225,-136.52759 c 6.2501,-9.97723 3.3831,-23.16553 -6.7088,-29.58766 -9.9772,-6.25011 -23.1655,-3.32574 -28.7849,6.42213 l -72.4783,115.9997 -43.6361,0.11469 c -2.8097,0 -5.6193,0.45872 -8.429,1.5482 l -75.4027,29.58764 -38.8768,-58.88862 c -6.3648,-9.86255 -19.6105,-12.78691 -29.5877,-6.42214 -9.97723,6.42214 -12.84426,19.72512 -6.47947,29.70237 z"
                      style="fill:#231f20;fill-rule:nonzero" />
                    <path
                      id="path5046"
                      d="m 1158.9031,-52.90085 c 20.9866,0 38.0167,-16.91544 38.0167,-37.8447 0,-20.92925 -17.0301,-37.95936 -38.0167,-37.95936 -20.9293,0 -37.9594,17.03011 -37.9594,37.95936 0,20.92926 17.0301,37.8447 37.9594,37.8447"
                      style="fill:#231f20;fill-rule:nonzero" />
                    <path
                      id="path5048"
                      d="M 1213.0325,334.37646 V 163.61664 l -15.998,7.45425 v 163.30557 z"
                      style="fill:#231f20;fill-rule:nonzero" />
                  </g>
                </svg>
              </div>
              <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
              
                <div class="mt-2 w-full">
                   <h2 class="text-base font-semibold leading-6 text-gray-900">{{ __('Total climbed') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ __('This takes into account all routes climbed, whether traditional or bouldering, including those that have been dismantled since you completed them. A route climbed with lead and top rope is only counted once.') }}</p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 sm:gap-x-1">
            <x-button x-on:click='open_modal_total = false'>{{__('Close')}}</x-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


    <div>
  <div class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-show="open_modal_level_ascent" x-cloak>
    <div class="fixed inset-0 bg-gray-500/75 transition-opacity" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
      <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
          <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gray-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-7 w-7"
                  viewBox="0 0 389 515"
                  id="svg2"
                  version="1.1"
                  xmlns="http://www.w3.org/2000/svg"
                  xmlns:svg="http://www.w3.org/2000/svg">
                  <defs
                    id="defs17" />
                  <g
                    id="g1"
                    transform="translate(-984.5876,180.9994)">
                    <path
                      id="path5040"
                      d="m 1185.0503,160.80697 c -4.8166,1.77753 -10.2066,2.80966 -15.7113,2.80966 -5.906,0 -11.7548,-0.68808 -17.1448,-2.23626 l -60.8955,139.96802 c -5.562,12.90161 -20.8719,17.25946 -34.2322,9.74786 -13.303,-7.5116 -19.6105,-23.96832 -13.8764,-36.8699 l 74.6572,-171.21854 h 95.1852 V 87.00982 l 48.1659,-22.42013 c 12.6149,-6.13541 29.0143,4.35788 32.34,17.7182 l 23.6243,94.72641 c 3.3831,13.41766 -4.8166,27.00735 -18.2343,30.3331 -13.475,3.38309 -27.122,-4.81659 -30.4477,-18.23425 l -15.0233,-60.37949 z"
                      style="fill:#231f20;fill-rule:nonzero" />
                    <path
                      id="path5042"
                      d="M 1228.9158,62.69748 C 1333.218,10.97639 1373.643,-166.20556 1373.643,-180.9994 h -14.3351 c -5.39,36.92725 -50.1729,184.80823 -130.3921,227.69889 z"
                      style="fill:#231f20;fill-rule:nonzero" />
                    <path
                      id="path5044"
                      d="m 1036.4812,23.93533 c 5.4474,8.54373 16.4568,12.32819 26.2046,8.31436 l 55.1615,-21.61734 v 76.49216 h 95.1852 V -1.46648 l 85.3225,-136.52759 c 6.2501,-9.97723 3.3831,-23.16553 -6.7088,-29.58766 -9.9772,-6.25011 -23.1655,-3.32574 -28.7849,6.42213 l -72.4783,115.9997 -43.6361,0.11469 c -2.8097,0 -5.6193,0.45872 -8.429,1.5482 l -75.4027,29.58764 -38.8768,-58.88862 c -6.3648,-9.86255 -19.6105,-12.78691 -29.5877,-6.42214 -9.97723,6.42214 -12.84426,19.72512 -6.47947,29.70237 z"
                      style="fill:#231f20;fill-rule:nonzero" />
                    <path
                      id="path5046"
                      d="m 1158.9031,-52.90085 c 20.9866,0 38.0167,-16.91544 38.0167,-37.8447 0,-20.92925 -17.0301,-37.95936 -38.0167,-37.95936 -20.9293,0 -37.9594,17.03011 -37.9594,37.95936 0,20.92926 17.0301,37.8447 37.9594,37.8447"
                      style="fill:#231f20;fill-rule:nonzero" />
                    <path
                      id="path5048"
                      d="M 1213.0325,334.37646 V 163.61664 l -15.998,7.45425 v 163.30557 z"
                      style="fill:#231f20;fill-rule:nonzero" />
                  </g>
                </svg>
              </div>
              <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
              
                <div class="mt-2 w-full">
                   <h2 class="text-base font-semibold leading-6 text-gray-900">{{ __('Climbing level') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ __('We take the 3 hardest routes you\'ve ever done, and then we take the lowest rating from those three. So, to have a level of 7a, you\'ll need to climb 3 routes with a minimum level of 7a. Here the three routes') }}</p>

                  <table class="border-separate border-spacing-y-3 min-w-full divide-y divide-gray-300 table-fixed">
                    <tbody class="bg-white"> @foreach ($this->logs_t as $log) <tr 
                      class="hover:bg-gray-50 cursor-pointer">
                      <td class="bg-{{$log->route->color}}-300 border-2 border-{{$log->route->color}}-300 rounded-l-md text-center h-16 w-16 relative whitespace-nowrap font-medium text-gray-900">
                      <div class='grayscale rounded-l h-full w-full bg-cover' style="background-image: url({{ $log->route->thumbnail() }})"></div>
                      </td>
                        <td class=" text-2xl text-center w-16 bg-{{$log->route->color}}-300 relative whitespace-nowrap py-4 pl-4 pr-3 font-medium text-gray-900 sm:pl-3">
                          {{$log->route->gradeFormated()}}
                        </td>
                        <td class="  whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                          <div class="flex items-center">
                            <div>
                              <div class="font-bold pb-1">{{$log->route->name}}</div>   <div class="text-sm opacity-50">{{$log->created_at->format('d/m/y')}}</div>
                            </div>
                          </div>
                        </td>
                        <td class=" relative whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3"> 
                            <span class=""> @if($log->way == 'top-rope') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                                <svg class="h-1.5 w-1.5 fill-red-500" viewBox="0 0 6 6" aria-hidden="true">
                                  <circle cx="3" cy="3" r="3" />
                                </svg>
                                {{ __('Top-rope') }}
                              </a> @elseif($log->way == 'lead') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                                <svg class="h-1.5 w-1.5 fill-green-500" viewBox="0 0 6 6" aria-hidden="true">
                                  <circle cx="3" cy="3" r="3" />
                                </svg>
                                {{ __('Leading') }}
                              </a> @endif @if($log->type == 'view') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                                <svg class="h-1.5 w-1.5 fill-indigo-500" viewBox="0 0 6 6" aria-hidden="true">
                                  <circle cx="3" cy="3" r="3" />
                                </svg>
                                {{ __('View') }}
                              </a> @elseif($log->type == 'work') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                                <svg class="h-1.5 w-1.5 fill-emerald-500" viewBox="0 0 6 6" aria-hidden="true">
                                  <circle cx="3" cy="3" r="3" />
                                </svg>
                                {{ __('After work') }}
                              </a> @elseif($log->type == 'flash') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                                <svg class="h-1.5 w-1.5 fill-amber-500" viewBox="0 0 6 6" aria-hidden="true">
                                  <circle cx="3" cy="3" r="3" />
                                </svg>
                                {{ __('Flash') }}
                              </a> @endif </span>
                        </td>
                      </tr> @endforeach </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 sm:gap-x-1">
            <x-button x-on:click='open_modal_level_ascent = false'>{{__('Close')}}</x-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


    <div>
  <div class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-show="open_modal_level_bouldering" x-cloak>
    <div class="fixed inset-0 bg-gray-500/75 transition-opacity" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
      <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
          <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gray-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-7 w-7"
                  viewBox="0 0 389 515"
                  id="svg2"
                  version="1.1"
                  xmlns="http://www.w3.org/2000/svg"
                  xmlns:svg="http://www.w3.org/2000/svg">
                  <defs
                    id="defs17" />
                  <g
                    id="g1"
                    transform="translate(-984.5876,180.9994)">
                    <path
                      id="path5040"
                      d="m 1185.0503,160.80697 c -4.8166,1.77753 -10.2066,2.80966 -15.7113,2.80966 -5.906,0 -11.7548,-0.68808 -17.1448,-2.23626 l -60.8955,139.96802 c -5.562,12.90161 -20.8719,17.25946 -34.2322,9.74786 -13.303,-7.5116 -19.6105,-23.96832 -13.8764,-36.8699 l 74.6572,-171.21854 h 95.1852 V 87.00982 l 48.1659,-22.42013 c 12.6149,-6.13541 29.0143,4.35788 32.34,17.7182 l 23.6243,94.72641 c 3.3831,13.41766 -4.8166,27.00735 -18.2343,30.3331 -13.475,3.38309 -27.122,-4.81659 -30.4477,-18.23425 l -15.0233,-60.37949 z"
                      style="fill:#231f20;fill-rule:nonzero" />
                    <path
                      id="path5042"
                      d="M 1228.9158,62.69748 C 1333.218,10.97639 1373.643,-166.20556 1373.643,-180.9994 h -14.3351 c -5.39,36.92725 -50.1729,184.80823 -130.3921,227.69889 z"
                      style="fill:#231f20;fill-rule:nonzero" />
                    <path
                      id="path5044"
                      d="m 1036.4812,23.93533 c 5.4474,8.54373 16.4568,12.32819 26.2046,8.31436 l 55.1615,-21.61734 v 76.49216 h 95.1852 V -1.46648 l 85.3225,-136.52759 c 6.2501,-9.97723 3.3831,-23.16553 -6.7088,-29.58766 -9.9772,-6.25011 -23.1655,-3.32574 -28.7849,6.42213 l -72.4783,115.9997 -43.6361,0.11469 c -2.8097,0 -5.6193,0.45872 -8.429,1.5482 l -75.4027,29.58764 -38.8768,-58.88862 c -6.3648,-9.86255 -19.6105,-12.78691 -29.5877,-6.42214 -9.97723,6.42214 -12.84426,19.72512 -6.47947,29.70237 z"
                      style="fill:#231f20;fill-rule:nonzero" />
                    <path
                      id="path5046"
                      d="m 1158.9031,-52.90085 c 20.9866,0 38.0167,-16.91544 38.0167,-37.8447 0,-20.92925 -17.0301,-37.95936 -38.0167,-37.95936 -20.9293,0 -37.9594,17.03011 -37.9594,37.95936 0,20.92926 17.0301,37.8447 37.9594,37.8447"
                      style="fill:#231f20;fill-rule:nonzero" />
                    <path
                      id="path5048"
                      d="M 1213.0325,334.37646 V 163.61664 l -15.998,7.45425 v 163.30557 z"
                      style="fill:#231f20;fill-rule:nonzero" />
                  </g>
                </svg>
              </div>
              <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
              
                <div class="mt-2 w-full">
                   <h2 class="text-base font-semibold leading-6 text-gray-900">{{ __('Bouldering level') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ __('We take the 3 hardest routes you\'ve ever done, and then we take the lowest rating from those three. So, to have a level of 7a, you\'ll need to climb 3 routes with a minimum level of 7a. Here the three routes') }}</p>

                  <table class="border-separate border-spacing-y-3 min-w-full divide-y divide-gray-300 table-fixed">
                    <tbody class="bg-white"> @foreach ($this->logs_b as $log) <tr 
                      class="hover:bg-gray-50 cursor-pointer">
                      <td class="bg-{{$log->route->color}}-300 border-2 border-{{$log->route->color}}-300 rounded-l-md text-center h-16 w-16 relative whitespace-nowrap font-medium text-gray-900">
                      <div class='grayscale rounded-l h-full w-full bg-cover' style="background-image: url({{ $log->route->thumbnail() }})"></div>
                      </td>
                        <td class=" text-2xl text-center w-16 bg-{{$log->route->color}}-300 relative whitespace-nowrap py-4 pl-4 pr-3 font-medium text-gray-900 sm:pl-3">
                          {{$log->route->gradeFormated()}}
                        </td>
                        <td class="  whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">
                          <div class="flex items-center">
                            <div>
                              <div class="font-bold pb-1">{{$log->route->name}}</div>   <div class="text-sm opacity-50">{{$log->created_at->format('d/m/y')}}</div>
                            </div>
                          </div>
                        </td>
                        <td class=" relative whitespace-nowrap pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3"> 
                            <span class=""> @if($log->way == 'top-rope') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                                <svg class="h-1.5 w-1.5 fill-red-500" viewBox="0 0 6 6" aria-hidden="true">
                                  <circle cx="3" cy="3" r="3" />
                                </svg>
                                {{ __('Top-rope') }}
                              </a> @elseif($log->way == 'lead') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                                <svg class="h-1.5 w-1.5 fill-green-500" viewBox="0 0 6 6" aria-hidden="true">
                                  <circle cx="3" cy="3" r="3" />
                                </svg>
                                {{ __('Leading') }}
                              </a> @endif @if($log->type == 'view') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                                <svg class="h-1.5 w-1.5 fill-indigo-500" viewBox="0 0 6 6" aria-hidden="true">
                                  <circle cx="3" cy="3" r="3" />
                                </svg>
                                {{ __('View') }}
                              </a> @elseif($log->type == 'work') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                                <svg class="h-1.5 w-1.5 fill-emerald-500" viewBox="0 0 6 6" aria-hidden="true">
                                  <circle cx="3" cy="3" r="3" />
                                </svg>
                                {{ __('After work') }}
                              </a> @elseif($log->type == 'flash') <a class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                                <svg class="h-1.5 w-1.5 fill-amber-500" viewBox="0 0 6 6" aria-hidden="true">
                                  <circle cx="3" cy="3" r="3" />
                                </svg>
                                {{ __('Flash') }}
                              </a> @endif </span>
                        </td>
                      </tr> @endforeach </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 sm:gap-x-1">
            <x-button x-on:click='open_modal_level_bouldering = false'>{{__('Close')}}</x-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

  </div>