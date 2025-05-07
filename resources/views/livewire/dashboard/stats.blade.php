<?php

use Livewire\Volt\Component;
use App\Models\User;
new class extends Component {

    public User $user;
    public array $data;

    public function mount(){
      
      $this->user  = auth()->user();
      $this->data =  [
    'labels' => ['5a', '5b-5c', '6a-6b', '6c', '7a', '7b', '7c'],
    'datasets' => [
        [
            'label' => 'Routes climbed',
            'data' => [65, 59, 80, 81, 56, 55, 40],
            'backgroundColor' => 'rgba(0, 0, 0, 0.2)',
            'borderColor' => 'rgba(0, 0, 0, 1)',
            'borderWidth' => 1,
        ],

        [
            'label' => 'Bloc climbed',
            'data' => [5, 7, 8, 8, 56, 55, 40],
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