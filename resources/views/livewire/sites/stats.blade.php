<?php

use Livewire\Volt\Component;
use App\Models\Site;
use App\Models\Area;
use App\Models\Route;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use App\Models\Log;
use \Carbon\CarbonPeriod;
use \Carbon\Carbon;
new class extends Component {

    public Site $site;
    public $area;
    public $areas;
    public $data_routes_grade;
    public $data_routes_week;
    public $data_routes_by_tag;
    public $data_logs_by_month;

    public function mount(Site $site){
      $this->site = $site;
      $this->areas = $this->site->areas;
      $this->area = null;
      $values = $this->getValues();
        $this->data_routes_grade = $values['data_routes_grade'];
        $this->data_routes_week = $values['data_routes_week'];
        $this->data_routes_by_tag = $values['data_routes_by_tag'];
        $this->data_logs_by_month = $values['data_logs_by_month'];

    }
    public function getValues(){
            #Number of routes by difficulty (bar chart) with 2 bars for each type (bouldering and sport climbing)
            $routes = Route::where(function($query) {
          $query->whereNull('removing_at')
              ->orWhere('removing_at', '>', now());
        })->whereHas('line.sector.area.site', function ($query) {
                $query->where('id', $this->site->id);
            })
            ->when(($this->area != null), function ($query) {
                        return $query->whereHas('line.sector.area', function ($query) {
                                $query->where('id', 7);
                            });
                    })
            ->get();

            $logs = Log::whereIn('route_id', ($routes->pluck('id')))->with('route')->get();

            $logsByGrade = $logs->groupBy(function ($log) {
            return $log->route->gradeFormated();
        })->map(function ($group) {
            return $group->count();
        });

        $routesByGrade = (clone $routes)->groupBy(function ($route) {
            return $route->gradeFormated();
        })->map(function ($group) {
            return $group->count();
        });

        $data_routes_grade =  [
        'labels' => $routesByGrade->keys()->toArray(),
        'datasets' => [
            [
                'label' => __('Number or routes'),
                'data' => $routesByGrade->values()->toArray(),
                'backgroundColor' => 'rgba(0, 0, 0, 0.2)',
                'borderColor' => 'rgba(0, 0, 0, 1)',
                'borderWidth' => 1,
            ],
            [
                'label' =>  __('Number or logs'),
                'data' => $logsByGrade->values()->toArray(),
                'backgroundColor' => 'rgba(0, 0, 0, 0.2)',
                'borderColor' => 'rgba(0, 0, 0, 1)',
                'borderWidth' => 1,
            ],
        ],
        ];

        # Routes created by week

        $startDate = Carbon::now()->subYear()->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();

        $routes_by_week = Route::whereHas('line.sector.area.site', function ($query) {
                $query->where('id', $this->site->id);
            })
            ->when(($this->area != null), function ($query) {
                        return $query->whereHas('line.sector.area', function ($query) {
                                $query->where('id', $this->area);
                            });
                    })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($route) {
                // Group by year-week (ISO-8601 week)
                return Carbon::parse($route->created_at)->format('o-W');
            })
            ->map(function ($group) {
                return [
                    'year_week' => $group->first() ? Carbon::parse($group->first()->created_at)->format('o-W') : null,
                    'week_start' => $group->first() ? Carbon::parse($group->first()->created_at)->startOfWeek()->toDateString() : null,
                    'count' => $group->count(),
                ];
            })
            ->keyBy('year_week');

        $weeks = [];
        $labels = [];
        $counts = [];
        $period = CarbonPeriod::create($startDate, '1 week', $endDate);
        foreach ($period as $dt) {
            $year_week = $dt->format('o-W');
            $weeks[] = $year_week;
            $labels[] = $year_week;
            $counts[] = isset($routes_by_week[$year_week]) ? $routes_by_week[$year_week]['count'] : 0;
        }

        $data_routes_week =  [
        'labels' => $labels,
        'datasets' => [
            [
                'label' =>  __('Number of routes created by weeks'),
                'data' => $counts,
                'backgroundColor' => 'rgba(0, 0, 0, 0.2)',
                'borderColor' => 'rgba(0, 0, 0, 1)',
                'borderWidth' => 1,
            ],
        ],
        ];

        # Routes by Tag

        $tags = Tag::withCount(['routes' => function ($query) {
            $query->whereHas('line.sector.area.site', function ($query) {
                $query->where('id', $this->site->id);
            })->where(function($query) {
          $query->whereNull('removing_at')
              ->orWhere('removing_at', '>', now());
        });
            if ($this->area != null) {
                $query->whereHas('line.sector.area', function ($query) {
                    $query->where('id', $this->area);
                });
            }
        }])->get();

        $data_routes_by_tag = [
            'labels' => $tags->pluck('name')->toArray(),
            'datasets' => [
                [
                    'label' => __('Number of routes by tags'),
                    'data' => $tags->pluck('routes_count')->toArray(),
                    'backgroundColor' => 'rgba(0, 0, 0, 0.2)',
                    'borderColor' => 'rgba(0, 0, 0, 1)',
                    'borderWidth' => 1,
                ],
            ],
        ];

        ## Logs by months 

            $startMonth = Carbon::now()->subMonths(11)->startOfMonth();
            $endMonth = Carbon::now()->endOfMonth();

            $logs_by_month = Log::whereHas('route.line.sector.area.site', function ($query) {
                $query->where('id', $this->site->id);
                })
                ->when(($this->area != null), function ($query) {
                return $query->whereHas('route.line.sector.area', function ($query) {
                    $query->where('id', $this->area);
                });
                })
                ->whereBetween('created_at', [$startMonth, $endMonth])
                ->get()
                ->groupBy(function ($log) {
                return Carbon::parse($log->created_at)->format('Y-m');
                })
                ->map(function ($group) {
                return (object)[
                    'count' => $group->count()
                ];
                });

            // Prepare labels for the last 12 months
            $labels = [];
            $data = [];
            $period = CarbonPeriod::create($startMonth, '1 month', $endMonth);
            foreach ($period as $dt) {
                $label = $dt->format('Y-m');
                $labels[] = $label;
                $data[] = $logs_by_month->has($label) ? $logs_by_month[$label]->count : 0;
            }

                $data_logs_by_month = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => __('Number of logs by month'),
                        'data' => $data,
                        'backgroundColor' => 'rgba(0, 0, 0, 0.2)',
                        'borderColor' => 'rgba(0, 0, 0, 1)',
                        'borderWidth' => 1,
                    ],
                ],
            ];
        return ['data_logs_by_month' => $data_logs_by_month, 
            'data_routes_by_tag'=> $data_routes_by_tag,
            'data_routes_week'=> $data_routes_week,
            'data_routes_grade' => $data_routes_grade];
    }
    public function updated(){
        if($this->area == 'null'){
            $this->area = null;
        }
        $values = $this->getValues();
        $this->data_routes_grade = $values['data_routes_grade'];
        $this->data_routes_week = $values['data_routes_week'];
        $this->data_routes_by_tag = $values['data_routes_by_tag'];
        $this->data_logs_by_month = $values['data_logs_by_month'];
    }
}; ?>

<div class='pb-4'>
     @assets
 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 @endassets

    <div class='grid grid-cols-6 gap-2'>
        <div class="bg-white overflow-hidden  sm:rounded-lg col-span-6 flex justify-between items-center">
            <h2 class="px-4 py-4 text-xl font-semibold text-gray-900">
                {{ __('Stats of Site') }}
            </h2>
            <div class='flex items-center gap-2 px-2'> {{ __('Areas') }}
            <select wire:model.live='area' class='h-10 block  rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-gray-600 sm:text-sm sm:leading-6'>
                <option value="null">{{ __('All') }}</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                @endforeach
            </select>
        </div>
        </div>
    
    <div class='col-span-3 bg-white overflow-hidden  sm:rounded-lg  min-h-32'>
        <h2 class="px-4 py-4 text-xl font-semibold text-gray-900">
                {{ __('Number of routes by grade') }}
            </h2>

        <div x-data="{
        data: $wire.entangle('data_routes_grade'),
        updateChart(){
        if (Chart.getChart('data_routes_grade')){ Chart.getChart('data_routes_grade').destroy(); }
         new Chart(document.getElementById('data_routes_grade').getContext('2d'), 
{ type: 'bar', data: this.data, options: {} });}
 }"
x-init="updateChart()"
x-effect="updateChart()" class='mx-2'>

 <canvas id="data_routes_grade" class='h-96'></canvas>
</div>

        </div>
        <div class='col-span-3 bg-white overflow-hidden  sm:rounded-lg  min-h-32 '>
            <h2 class="px-4 py-4 text-xl font-semibold text-gray-900">
                {{ __('Number of routes by tags') }}
            </h2>

            <div x-data="{
        data: $wire.entangle('data_routes_by_tag'),
        updateChart(){
        if (Chart.getChart('data_routes_by_tag')){ Chart.getChart('data_routes_by_tag').destroy(); }
         new Chart(document.getElementById('data_routes_by_tag').getContext('2d'), 
{ type: 'bar', data: this.data, options: {} });}
 }"
x-init="updateChart()"
x-effect="updateChart()" class='mx-2'>

 <canvas id="data_routes_by_tag" class='h-96'></canvas>
</div>

        </div>
        <div class='col-span-2 bg-white overflow-hidden  sm:rounded-lg  min-h-32 max-h-120'>
            <h2 class="px-4 py-4 text-xl font-semibold text-gray-900">
                {{ __('Number of logs by monts') }}
            </h2>

            <div x-data="{
        data: $wire.entangle('data_logs_by_month'),
        updateChart(){
        if (Chart.getChart('data_logs_by_month')){ Chart.getChart('data_logs_by_month').destroy(); }
         new Chart(document.getElementById('data_logs_by_month').getContext('2d'), 
{ type: 'bar', data: this.data, options: {maintainAspectRatio: false} });}
 }"
x-init="updateChart()"
x-effect="updateChart()" class='mx-2 h-120 pb-20'>

 <canvas id="data_logs_by_month" class='h-full'></canvas>
</div>
        </div>
        <div class='col-span-4 bg-white overflow-hidden  sm:rounded-lg  min-h-32'>
            <h2 class="px-4 py-4 text-xl font-semibold text-gray-900">
                {{ __('Number of routes created by week') }}
            </h2>

            <div x-data="{
        data: $wire.entangle('data_routes_week'),
        updateChart(){
        if (Chart.getChart('data_routes_week')){ Chart.getChart('data_routes_week').destroy(); }
         new Chart(document.getElementById('data_routes_week').getContext('2d'), 
{ type: 'bar', data: this.data, options: {} });}
 }"
x-init="updateChart()"
x-effect="updateChart()" class='mx-2'>

 <canvas id="data_routes_week" class='h-96'></canvas>
</div>
        </div>
    </div>
</div>