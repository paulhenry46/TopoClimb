<?php
use Livewire\Volt\Component;
use App\Models\Site;
use App\Models\Route;
use Livewire\Attributes\Validate; 
new class extends Component {

    public Site $site;
    public $points;
    public bool $default_cotation;
    public $modal_open_grade;
    public $hint;

    public function save()
    {
        $this->site->default_cotation = $this->default_cotation ;
        $this->site->save();

        if($this->default_cotation == false){
             $this->validate(); 

             $points_array =[];
             // Transform points array to ['label' => value, ...]
            foreach ($this->points as $point) {
                if (isset($point['label']) && isset($point['value'])) {
                    $points_array[$point['label']] = $point['value'];
                }
            }
            $final_array = [
                'free' => false,
                'hint' => $this->hint,
                'points'=> $points_array
        ];

        $this->site->custom_cotation = $final_array;
        $this->site->save();
        $grades_array = $points_array;

        }else{
        $this->site->custom_cotation = NULL;
        $this->site->save();
        $grades_array = config('climb.default_cotation.points');
        }
       
        $routes = Route::whereHas('line.sector.area.site', function($query) {
            $query->where('id', $this->site->id);
        })->get();


        foreach ($routes as $route) {
            // Find the closest grade value for this route
            $route->grade = $this->findClosest($grades_array, $route->grade);
            $route->save();
        }

        $this->modal_open_grade = false;
    }

    public function rules()
{
    return [
        'points' => 'required|array|min:1',
        'points.*.label' => 'required|string',
        'points.*.value' => 'required|numeric',
    ];
}



    public function mount(Site $site){
      $this->site = $site;
      $this->default_cotation = $this->site->default_cotation;
      if($this->default_cotation == false){
        $this->points = collect($this->site->cotations())
        ->map(function($value, $key) {
            return ['label' => $key, 'value' => $value];
        })
        ->values()
        ->toArray();
      }else{
        $this->points = [];
      }
    
      $this->modal_open_grade = false;
    }

    public function open(){
        $this->modal_open_grade =true;
    }

    private function findClosest($array, $target) {
        $closest = null;
        $minDiff = 10000;

        foreach ($array as $key => $value) {
            $diff = abs($target - $value);
            if ($diff < $minDiff) {
                $minDiff = $diff;
                $closest = $value;
            }
        }
        return $closest;
}
}
?>

<div class='inline-flex cursor-pointer items-center '>
     <x-button wire:click="open" ><x-icons.icon-settings class='mr-2'/> <p class='ml-2'>{{__('Grading System')}}</p> </x-button>
 <x-drawer open='modal_open_grade' save_method_name='save' :title="$this->site->name" :subtitle="__('Edit grading system')">
                    <div class="space-y-2 px-4 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5"
                    x-data="{points: $wire.points,
                            label :'',
                            value :'',
                            error : '',
                            addPoint(){ 
                            if (isNaN(this.value) || this.value === '') {
                                this.error = '{{ __("Value must be a number") }}';
                                return;
                            }
                            this.points.push({ label: this.label, value: this.value }); 
                            this.value = ''; 
                            this.label=''; 
                            this.error = '';
                            this.sortPoints();}, 
                            removePoint(index) { this.points.splice(index, 1);},
                            sortPoints() {
                                this.points.sort((a, b) => {
                                    // If values are numeric, compare as numbers
                                    let va = isNaN(a.value) ? a.value : Number(a.value);
                                    let vb = isNaN(b.value) ? b.value : Number(b.value);
                                    if (va < vb) return -1;
                                    if (va > vb) return 1;
                                    return 0;
                                });
                            },
                            save(){$wire.points = this.points; $wire.save();}
                            }"
                            @save_points.windows='save()'>
                        
                        

                        <div class="flex items-center mb-3" x-data="{enabled: $wire.default_cotation, toogle(){this.enabled = !this.enabled;
                            $wire.set('default_cotation', this.enabled);}}">
                        <!-- Enabled: "bg-indigo-600", Not Enabled: "bg-gray-200" -->
                        <button x-on:click='toogle()' :class="enabled ? 'bg-gray-600' : 'bg-gray-200'" type="button" class="bg-gray-200 relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-600 focus:ring-offset-2" role="switch" aria-checked="false" aria-labelledby="annual-billing-label">
                            <!-- Enabled: "translate-x-5", Not Enabled: "translate-x-0" -->
                            <span  :class="enabled ? 'translate-x-5' : 'translate-x-0'" aria-hidden="true" class="translate-x-0 pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                        </button>
                        <span class="ml-3 text-sm" id="annual-billing-label">
                            <span class="font-medium text-gray-900">{{__('Use default grade system') }}</span>
                        </span>
                        </div>


                        <div wire:show='!default_cotation'>
                        <table class="min-w-full divide-y divide-gray-200 mb-4">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left">{{ __('Label') }}</th>
                                    <th class="px-4 py-2 text-left">{{ __('Value') }}</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in points" :key="index">
                                    <tr>
                                        <td class="px-4 py-2">
                                            <x-input type="text" x-model="item.label" class=" h-10 w-full" x-on:focusout="sortPoints()" />
                                        </td>
                                        <td class="px-4 py-2">
                                            <x-input type="text" x-model="item.value" class=" h-10 w-full" x-on:focusout="sortPoints()"/>
                                        </td>
                                        <td class="px-4 py-2">
                                            
                                            <button type='button' x-on:click="removePoint(index)" class='inline-flex cursor-pointer items-center px-2 py-2 bg-red-800 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-hidden disabled:opacity-50 transition ease-in-out duration-150'>
                                            <x-icons.icon-delete />
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr>
                                        <td class="px-4 py-2">
                                            <x-input type="text" x-model="label" class=" h-10 w-full" />
                                        <td class="px-4 py-2">
                                             <x-input type="text" x-model="value" class=" h-10 w-full" />
                                        </td>
                                        <td class="px-4 py-2">
                                            <button type='button' @click="addPoint()" class='inline-flex cursor-pointer items-center px-2 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-hidden disabled:opacity-50 transition ease-in-out duration-150'>
                                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M440-440H240q-17 0-28.5-11.5T200-480q0-17 11.5-28.5T240-520h200v-200q0-17 11.5-28.5T480-760q17 0 28.5 11.5T520-720v200h200q17 0 28.5 11.5T760-480q0 17-11.5 28.5T720-440H520v200q0 17-11.5 28.5T480-200q-17 0-28.5-11.5T440-240v-200Z"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                            </tbody>
                            <template x-if="error">
                                <div class="pl-5 text-red-600 mb-2" x-text="error"></div>
                            </template>
                        </table>
                        <div>
                            <x-label for="hint" value="{{ __('Hint') }}" />
                            <textarea wire:model="hint" id="hint" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6"></textarea>
                            <x-input-error for="hint" class="mt-2" />
                        </div>
                      </div>
                    </div>
                  <x-slot name="footer">
                    <div class="flex justify-end space-x-3">
                      <x-secondary-button x-on:click="open = ! open" type="button">{{__('Cancel')}}</x-secondary-button>
                      <x-button  @click="$dispatch('save_points')">{{__('Save')}}</x-button>
                    </div>
                  </x-slot>
      </x-drawer>
</div>