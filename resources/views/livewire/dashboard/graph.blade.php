<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Log;
new class extends Component {

    public User $user;
    public array $data;

    public function mount(){
        $this->user  = auth()->user();

        $logs = Log::where('user_id', $this->user->id)
        ->with('route')
        ->get();

    // Group logs by route grade and count them
    $routesByGrade = $logs->groupBy(function ($log) {
        return $log->route->grade; // Assuming `grade` is a column in the `routes` table
    })->map(function (Collection $group) {
        return $group->count();
    });

    // Group logs by route grade and count them
    $routesByGrade = $logs->groupBy(function ($log) {
        return $log->route->grade; // Assuming `grade` is a column in the `routes` table
    })->map(function (Collection $group) {
        return $group->count();
    });

      
      $this->data =  [
    'labels' => $routesByGrade->keys()->toArray(),
    'datasets' => [
        [
            'label' => 'Routes climbed',
            'data' => $routesByGrade->values()->toArray(),
            'backgroundColor' => 'rgba(0, 0, 0, 0.2)',
            'borderColor' => 'rgba(0, 0, 0, 1)',
            'borderWidth' => 1,
        ],
    ],
];
    }

}; ?>


<div x-data="{ chart: null }"
x-init=" chart = new Chart(document.getElementById('myChart').getContext('2d'), 
{ type: 'bar', data: $wire.data,
 options: {} }); ">

 <canvas id="myChart" class='h-96'></canvas>
 @assets
 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 @endassets
</div>