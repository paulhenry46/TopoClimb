<?php

namespace App\Jobs;
use App\Models\Route;
use DOMDocument;
use DOMXPath;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class DeleteRoute implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Route $route)
    {
        //
    }

    /**
     * Execute the job. This is used to remove routes after one years
     */
    public function handle(): void
    {
        $line = $this->route->line;
        $sector = $line->sector;
        $area = $sector->area;
        $site = $area->site;

        Storage::delete('photos/site-'.$site->id.'/area-'.$area->id.'/route-'.$this->route->id.'');
        
        $grade =  $this->route->cotation;
        $logs = $this->route->logs();
        $line = $this->route->line;
        $sector = $line->sector;
        $area = $sector->area;

        if($area->type == 'trad'){
            $logs->update(['route_id' => 1, "comment" => $grade]);
        }else{
            $logs->update(['route_id' => 2, "comment" => $grade]);
        }
        
        $this->route->delete();

    }
}
