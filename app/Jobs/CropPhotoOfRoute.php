<?php

namespace App\Jobs;

use App\Models\Area;
use App\Models\Route;
use App\Models\Site;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class CropPhotoOfRoute implements ShouldQueue
{
    use Queueable;
    /**
     * Create a new job instance.
     */
    public function __construct(public Site $site, public Area $area, public Route $route, public array $data, public array $transform)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $input_path = Storage::path('photos/site-'.$this->site->id.'/area-'.$this->area->id.'/route-'.$this->route->id.'');
        $output_path = Storage::path('photos/site-'.$this->site->id.'/area-'.$this->area->id.'/route-'.$this->route->id.'-thumbnail');
        $result = Process::run('identify -ping -format \'%wx%h\' '.$input_path.'');
 
        if ($result->successful()) {
        // Parse the output to get width and height
        [$p_w, $p_h] = explode('x', $result->output());

    } else {
        // Handle the error
        logger()->error('Failed to identify image dimensions: '.$result->errorOutput());
    }

    // Calculate the final width of the cropped photo by scaling the original width of photo ($p_w) 
    // proportionally based on the ratio of the crop width of svg ($this->data['w']) to the total width of svg ($this->data['t_w']).
    $final_width =  round($p_w * $this->data['w'] / $this->data['t_w']) ;
    $final_height = round($p_h * $this->data['h'] / $this->data['t_h']) ;
    // Calculate the final horizontal position (left offset) of the cropped photo by scaling the x-coordinate 
    // (including a transformation offset $this->transform['x']) proportionally based on the total width of svg ($this->data['t_w']).
    $final_left = round($p_w * ($this->data['x'] + $this->transform['x']) / $this->data['t_w']) ;
    $final_top = round($p_h * ($this->data['y'] + $this->transform['y']) / $this->data['t_h']) ;

    $result = Process::run('convert '.$input_path.' -crop '.$final_width.'x'.$final_height.'+'.$final_left.'+'.$final_top.' '.$output_path.'');
    }
}
