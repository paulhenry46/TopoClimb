<?php

use Livewire\Volt\Component;
use App\Models\Site;
use App\Models\Area;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use App\Models\log;
use \Carbon\CarbonPeriod;
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

    }
    public function with(){
            #Number of routes by difficulty (bar chart) with 2 bars for each type (bouldering and sport climbing)
            $routes = Route::with('line.sector.area.site')
            ->whereHas('line.sector.area.site', function ($query) {
                $query->where('id', $this->site->id);
            })
            ->when(($this->area != null), function ($query) {
                        return $query->whereHas('line.sector.area', function ($query) {
                                $query->where('id', $this->area->id);
                            });
                    })
            ->get();

            $logs = Log::whereIn('route_id', ($routes->pluck('id')))->with('route')->get();

            $logsByGrade = $logs->groupBy(function ($log) {
            return $log->route->gradeFormated();
        })->map(function (Collection $group) {
            return $group->count();
        });

        $routesByGrade = (clone $routes)->groupBy(function ($route) {
            return $route->gradeFormated();
        })->map(function (Collection $group) {
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
                                $query->where('id', $this->area->id);
                            });
                    })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw("YEARWEEK(created_at, 1) as year_week"),
                DB::raw("MIN(DATE(created_at)) as week_start"),
                DB::raw("COUNT(*) as count")
            )
            ->groupBy('year_week')
            ->orderBy('week_start')
            ->get();

        $data_routes_week =  [
        'labels' => range(1, 52),
        'datasets' => [
            [
                'label' =>  __('Number of routes created by weeks'),
                'data' => $routes_by_week->pluck('count'),
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
            });
            if ($this->area != null) {
                $query->whereHas('line.sector.area', function ($query) {
                    $query->where('id', $this->area->id);
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
                                    $query->where('id', $this->area->id);
                                });
                        })
                ->whereBetween('created_at', [$startMonth, $endMonth])
                ->select(
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                    DB::raw("COUNT(*) as count")
                )
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->keyBy('month');

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

        return ['data_logs_by_month' => json_encode($data_logs_by_month), 
            'data_routes_by_tag'=> json_encode($data_routes_by_tag),
            'data_routes_week'=> json_encode($data_routes_week),
            'data_routes_grade' => json_encode($data_routes_grade)]
    }


}; ?>

<div>
     @assets
 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 @endassets

    <div class='grid grid-cols-6 gap-2'>
        <div class="bg-white overflow-hidden  sm:rounded-lg col-span-6 flex">
            <h2 class="px-4 py-4 text-xl font-semibold text-gray-900">
                {{ __('Stats of Site') }}
            </h2>
            <select wire:model="area">
                <option value="null">{{ __('All') }}</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class='col-span-3 bg-white overflow-hidden  sm:rounded-lg md:col-span-3 min-h-32'>
        <h2 class="px-4 py-4 text-xl font-semibold text-gray-900">
                {{ __('Number of routes by grade') }}
            </h2>

        <div x-data="{ chart: null }"
x-init=" chart = new Chart(document.getElementById('data_routes_grade').getContext('2d'), 
{ type: 'bar', data: {{ $data_routes_grade }},
 options: {} }); ">

 <canvas id="data_routes_grade" class='h-96'></canvas>
</div>

        </div>
        <div class='col-span-3bg-white overflow-hidden  sm:rounded-lg md:col-span-3 min-h-32 '>
            <h2 class="px-4 py-4 text-xl font-semibold text-gray-900">
                {{ __('Number of routes by tags') }}
            </h2>

            <div x-data="{ chart: null }"
x-init=" chart = new Chart(document.getElementById('data_routes_by_tag').getContext('2d'), 
{ type: 'bar', data: {{$data_routes_by_tag}},
 options: {} }); ">

 <canvas id="data_routes_by_tag" class='h-96'></canvas>
</div>

        </div>
        <div class='col-span-2 bg-white overflow-hidden  sm:rounded-lg md:col-span-3 min-h-32'>
            <h2 class="px-4 py-4 text-xl font-semibold text-gray-900">
                {{ __('Number of logs by monts') }}
            </h2>

            <div x-data="{ chart: null }"
x-init=" chart = new Chart(document.getElementById('data_logs_by_month').getContext('2d'), 
{ type: 'bar', data: {{$data_logs_by_month}},
 options: {} }); ">

 <canvas id="data_logs_by_month" class='h-96'></canvas>
</div>
        </div>
        <div class='col-span-4 bg-white overflow-hidden  sm:rounded-lg md:col-span-3 min-h-32'>
            <h2 class="px-4 py-4 text-xl font-semibold text-gray-900">
                {{ __('Number of routes created by week') }}
            </h2>

            <div x-data="{ chart: null }"
x-init=" chart = new Chart(document.getElementById('data_routes_week').getContext('2d'), 
{ type: 'bar', data: {{$data_routes_week}},
 options: {} }); ">

 <canvas id="data_routes_week" class='h-96'></canvas>
</div>
        </div>
    </div>
</div>