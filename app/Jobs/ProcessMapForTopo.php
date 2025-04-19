<?php

namespace App\Jobs;

use App\Models\Area;
use App\Models\Site;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use DOMDocument;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class ProcessMapForTopo implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Site $site, public Area $area, public string $svg)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $final_path = 'plans/site-'.$this->site->id.'/area-'.$this->area->id.'/topo_export.svg';
        $xml = simplexml_load_string($this->svg);
        $dom = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        $items = $dom->getElementsByTagName('defs');
        foreach ($items as $item) {
            $item->remove();
        }
        Storage::put($final_path, $dom->saveXML());
        $input_file_path = Storage::path($final_path, $dom->saveXML());

        $output_file_path= storage_path('app/public/'.$final_path.'');
        $result = Process::run('inkscape --export-type=svg -o '.$output_file_path.' --export-area-drawing --export-plain-svg '.$input_file_path.'');
        
    }
}
